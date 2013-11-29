"""
read_csv returns a generator that yields the tuple (title, [(p1, v1), (p2, v2),..])
"""

from StringIO import StringIO
import gzip
import CsvWriter, XmlReader

def read_csv(input_file, seperator=","):
    current_title = None
    claims = []
    for line in input_file:
        title, prop, value = line.strip().split(seperator, 2)
        if current_title != title:
            if not current_title is None:
                yield current_title, claims
            current_title = title
            claims = []
        claims.append((prop, value))

    if not current_title is None:
        yield current_title, claims


def read_compressed_csv(input_file, seperator=","):
    title = None
    claims = []
    for line in input_file:
        if line[0] == "=":
            if not title is None:
                yield title, claims
            title = line[1:].strip()
        else:
            prop, value = line.strip().split(seperator, 1)
            claims.append((prop, value))
    if not title is None:
        yield title, claims
        claims = []



if __name__ == "__main__":
    with gzip.open("../test/Wikidata-20131129161111.xml.gz", "r") as f:
        out = StringIO()
        # for testing generate csv from xml just to parse it again
        CsvWriter.write_compressed_csv(XmlReader.read_xml(f), out)
        out.seek(0)
    x = read_compressed_csv(out)
    print "\n".join(map(str, x))