import sys
import XmlReader
import gzip

def write_csv(entities, output_file, seperator=","):
    for entity, claims in entities:
        for prop, datatype, value in claims:
            output_file.write((entity + seperator + prop + seperator + datatype + seperator + value + "\n").encode("utf-8"))

def write_compressed_csv(entities, output_file, seperator=","):
    for entity, claims in entities:
        output_file.write("=%s\n" % entity)
        for prop, datatype, value in claims:
            output_file.write((prop + seperator + datatype + seperator + value + "\n").encode("utf-8"))


if __name__ == "__main__":
    with gzip.open("test/Wikidata-20131129161111.xml.gz", "r") as f:
        #write_compressed_csv(XmlReader.read_xml(f), open("test/Wikidata-20131129161111.ccsv", "w"))
        write_compressed_csv(XmlReader.read_xml(f), sys.stdout)