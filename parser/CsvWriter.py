import sys, time, argparse

from CompressedFileType import CompressedFileType
import XmlReader



def write_csv(entities, output_file, sep=","):
    for entity, claims in entities:
        for prop, datatype, value in claims:
            output_file.write((entity + sep + prop + sep + datatype + sep + value + "\n").encode("utf-8"))



if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="this program converts wikidata XML dumps to CSV data.")
    parser.add_argument("input", help="The XML input file (a wikidata dump), gzip is supported",
                        type=CompressedFileType('r'))
    parser.add_argument("output", help="The CSV output file (default=sys.stdout)", default=sys.stdout, nargs='?',
                        type=CompressedFileType('w'))
    parser.add_argument("-p", "--processes", help="Number of processors to use (default 4)", type=int, default=4)
    args = parser.parse_args()

    start = time.time()
    write_csv(XmlReader.read_xml(args.input, args.processes), args.output)
    print "total time: %.2fs"%(time.time() - start)