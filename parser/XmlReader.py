
"""
read_xml returns a generator that yields the tuple (title, [(p1, dt1, v1), (p2, dt1, v2),..])
where
p_n is a property
d_n is a datatype
v_n is a value

usage:
with open("file.xml", "r") as f:
    for title, claim in read_xml(f):
        do_things()
"""
import multiprocessing
import time, gzip, json, argparse, traceback, signal
try:
    import xml.etree.cElementTree as ElementTree
except ImportError:
    import xml.etree.ElementTree as ElementTree

from CompressedFileType import CompressedFileType

NS = "http://www.mediawiki.org/xml/export-0.8/"
title_tag = "{"+NS+"}"+"title"
text_tag = "{"+NS+"}"+"text"
model_tag = "{"+NS+"}"+"model"
page_tag = "{"+NS+"}"+"page"

# http://noswap.com/blog/python-multiprocessing-keyboardinterrupt
def init_worker():
    signal.signal(signal.SIGINT, signal.SIG_IGN)

def read_xml(input_file, thread_count=4):
    if thread_count > 1:
        pool = multiprocessing.Pool(thread_count-1, init_worker) # one thread is for xml parsing
        try:
	    for title, claims in pool.imap(_process_json, _get_xml(input_file)):
                yield title, claims
        except KeyboardInterrupt:
	    print "KeyboardInterrupt"
	    pool.terminate()
            pool.join()
    else:
        for title, claim_json in _get_xml(input_file):
            try:
		data = _process_json((title, claim_json))
		yield data
	    except Exception, e:
                # show some debug info
		print "WARNING: could not parse at %s"%(input_file.tell())
		print e
		print type(claim_json)
                print claim_json
                print traceback.format_exc()
                with open(title + ".dump", "w") as f:
                     f.write(claim_json)
		exit()


def _get_xml(input_file):
    count = 0
    title = None
    model = None
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
                print "processed %.2fMB" % (input_file.tell() / 1024.0**2)
            if model == "wikibase-item":
                yield title, claim_json
        element.clear()


def _process_json((title, json_string)):
    data = json.loads(json_string)
    if not "claims" in data:
        return title, []

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
            elif datatype == "bad":
                # for example in Q2241
		continue
	    else:
                raise RuntimeError("unknown wikidata datatype: %s" % datatype)
        else:
            datatype = value = claim[0]

        claims.append((prop, datatype, value))
    return title, claims

if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("input", help="The XML input file (a wikidata dump), gzip is supported",
                        default="test/Wikidata-20131129215005.xml.gz", nargs="?", type=CompressedFileType('r'))
    parser.add_argument("-v", "--verbose", help="Show output", action="store_true")
    parser.add_argument("-p", "--processes", help="Number of processors to use (default 4)", type=int, default=4)
    args = parser.parse_args()

    start = time.time()
    if not args.verbose:
        for element in read_xml(args.input, args.processes):
            pass
    else:
        for element in read_xml(args.input, args.processes):
            print element
    
    print "total time: %.2fs"%(time.time() - start)
