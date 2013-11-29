
"""
read_xml returns a generator that yields the tuple (title, [(p1, v1), (p2, v2),..])
"""

import gzip
import json
try:
    import xml.etree.cElementTree as ElementTree
except ImportError:
    import xml.etree.ElementTree


NS = "http://www.mediawiki.org/xml/export-0.8/"
title_tag = "{"+NS+"}"+"title"
text_tag = "{"+NS+"}"+"text"


def read_xml(input_file):
    count = 0

    for event, element in ElementTree.iterparse(input_file):
        if element.tag == title_tag:
            title = element.text
        elif element.tag == text_tag:
            claims = _process_json(element.text)

            count += 1
            if count % 10000 == 0:
                print "processed %.2fMB"%(input_file.tell() / 1024.0**2)

            yield (title, claims)
        element.clear()


def _process_json(json_string):
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
            value = claim[0]
        claims.append((prop,  value))
    return claims

if __name__ == "__main__":
    with  gzip.open("../test/Wikidata-20131129161111.xml.gz", "r") as f:
        x = read_xml(f)
        print "\n".join(map(str, x))
