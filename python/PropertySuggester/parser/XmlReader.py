"""
read_xml returns a generator that yields Entities)

usage:
with open("file.csv", "r") as f:
    for entity in read_xml(f):
        do_things()

"""
import multiprocessing
import time, argparse, traceback, signal
from propertysuggester.utils.CompressedFileType import CompressedFileType
from propertysuggester.utils.datatypes import Claim, Entity

try:
    import ujson as json
except ImportError:
    print "ujson not found"
    import json as json

try:
    import xml.etree.cElementTree as ElementTree
except ImportError:
    print "cElementTree not found"
    import xml.etree.ElementTree as ElementTree


NS = "http://www.mediawiki.org/xml/export-0.8/"
title_tag = "{" + NS + "}" + "title"
text_tag = "{" + NS + "}" + "text"
model_tag = "{" + NS + "}" + "model"
page_tag = "{" + NS + "}" + "page"


# http://noswap.com/blog/python-multiprocessing-keyboardinterrupt
def init_worker():
    signal.signal(signal.SIGINT, signal.SIG_IGN)


def read_xml(input_file, thread_count=4):
    """
    @rtype : collections.Iterable[Entity]
    @type input_file:  file or GzipFile or StringIO.StringIO
    @type thread_count: int
    """
    if thread_count > 1:
        # thread_count -1 because one thread is for xml parsing
        pool = multiprocessing.Pool(thread_count - 1, init_worker)
        try:
            for entity in pool.imap(_process_json, _get_xml(input_file)):
                yield entity
        except KeyboardInterrupt:
            print "KeyboardInterrupt"
            pool.terminate()
        except Exception:
            pool.terminate()
            traceback.format_exc()
        else:
            pool.close()
        finally:
            pool.join()
    else:
        for title, claim_json in _get_xml(input_file):
            yield _process_json((title, claim_json))


def _get_xml(input_file):
    count = 0
    title = claim_json = model = None
    for event, element in ElementTree.iterparse(input_file):
        if element.tag == title_tag:
            title = element.text
        elif element.tag == model_tag:
            model = element.text
        elif element.tag == text_tag:
            claim_json = element.text
        elif element.tag == page_tag:
            count += 1
            if count % 3000 == 0:
                print "processed %.2fMB" % (input_file.tell() / 1024.0 ** 2)
            if model == "wikibase-item":
                yield title, claim_json
        element.clear()


def _process_json((title, json_string)):
    data = json.loads(json_string)
    if not "claims" in data:
        return Entity(title, [])

    claims = []
    for claim in data["claims"]:
        claim = claim["m"]
        prop = claim[1]
        if claim[0] == "value":
            datatype = claim[2]
            if datatype == "string":
                value = claim[3]
            elif datatype == "wikibase-entityid":
                value = "Q" + str(claim[3]["numeric-id"])
            elif datatype == "time":
                value = claim[3]["time"]
            elif datatype == "globecoordinate":
                value = "N%f, E%f" % (claim[3]["latitude"], claim[3]["longitude"])
            elif datatype == "bad":
                # for example in Q2241
                continue
            else:
                print "WARNING unknown wikidata datatype: %s" % datatype
                continue
        else: # novalue, somevalue, ...
            datatype = "unknown"
            value = claim[0]

        claims.append(Claim(prop, datatype, value))
    return Entity(title, claims)


if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("input", help="The XML input file (a wikidata dump), gzip is supported",
                        default="test/Wikidata-20131129161111.xml.gz", nargs="?", type=CompressedFileType('r'))
    parser.add_argument("-v", "--verbose", help="Show output", action="store_true")
    parser.add_argument("-p", "--processes", help="Number of processors to use (default 4)", type=int, default=4)
    args = parser.parse_args()

    start = time.time()
    if not args.verbose:
        for x in read_xml(args.input, args.processes):
            pass
    else:
        for x in read_xml(args.input, args.processes):
            print x

    print "total time: %.2fs" % (time.time() - start)
