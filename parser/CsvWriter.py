import sys
import gzip
import argparse

import XmlReader


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
    parser = argparse.ArgumentParser()
    parser.add_argument("input", help="The XML input file (a wikidata dump), gzip is supported")
    parser.add_argument("output", help="The CSV output file (default=sys.stdout)", default="sys.stdout", nargs='?')
    parser.add_argument("-c", "--compressed", help="Use compressed csv (every entity is shown only once)", action="store_true")
    args = parser.parse_args()

    if args.input[-3:] == ".gz":
        in_file = gzip.open(args.input, "r")
    else:
        in_file = open(args.input, "r")

    if args.output == "sys.stdout":
        out_file = sys.stdout
    else:
        out_file = open(args.output, "w")

    if args.compressed:
        write_compressed_csv(XmlReader.read_xml(in_file), out_file)
    else:
        write_csv(XmlReader.read_xml(in_file), out_file)