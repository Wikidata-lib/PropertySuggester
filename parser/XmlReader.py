
"""
read_xml returns a generator that yields the tuple (title, [(p1, dt1, v1), (p2, dt1, v2),..])
where
p_n is a property
d_n is a datatype
v_n is a value
"""
import multiprocessing
import time, gzip, json, argparse
try:
    import xml.etree.cElementTree as ElementTree
except ImportError:
    import xml.etree.ElementTree as ElementTree


NS = "http://www.mediawiki.org/xml/export-0.8/"
title_tag = "{"+NS+"}"+"title"
text_tag = "{"+NS+"}"+"text"


def read_xml(input_file, thread_count=8):
    if thread_count > 0:
        pool = multiprocessing.Pool(thread_count)
        for title, claims in pool.imap(_process_json, _get_xml(input_file)):
            yield title, claims
    else:
        for title, claim_json in _get_xml(input_file):
            yield _process_json((title, claim_json))


def _get_xml(input_file):
    count = 0
    title = None
    for event, element in ElementTree.iterparse(input_file):
        if element.tag == title_tag:
            title = element.text
        elif element.tag == text_tag:
            claim_json = element.text
            count += 1
            if count % 10000 == 0:
                print "processed %.2fMB" % (input_file.tell() / 1024.0**2)
            yield title, claim_json
        element.clear()


def _process_json((title, json_string)):
    data = json.loads(json_string)
    claims = []
    for claim in data["claims"]:
        claim = claim["m"]
        prop = str(claim[1])
        if claim[0] == "value":
            datatype = claim[2]
            if datatype == "string":
                value = claim[3]
            elif datatype == "wikibase-entityid":
                value = "Q"+str(claim[3]["numeric-id"])
            elif datatype == "time":
                value = claim[3]["time"]
            elif datatype == "globecoordinate":
                value = "N%f, E%f" % (claim[3]["latitude"], claim[3]["longitude"])
            else:
                raise RuntimeError("unknown datatype: %s" % datatype)
        else:
            datatype = value = claim[0]

        claims.append((prop, datatype, value))
    return title, claims

if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("input", help="The XML input file (a wikidata dump), gzip is supported",
                        default="test/Wikidata-20131129215005.xml.gz", nargs="?")
    parser.add_argument("-v", "--verbose", help="Show output", action="store_true")
    parser.add_argument("-p", "--processes", help="Number of processors to use (default 4)", type=int, default=4)
    args = parser.parse_args()

    if args.input[-3:] == ".gz":
        in_file = gzip.open(args.input, "r")
    else:
        in_file = open(args.input, "r")

    start = time.time()
    if not args.verbose:
        for element in read_xml(in_file, args.processes):
            pass
    else:
        for element in read_xml(in_file, args.processes):
            print element
    
    print "total time: %.2fs"%(time.time() - start)