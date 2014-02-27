"""
read_csv returns a generator that yields Entities)

usage:
with open("file.csv", "r") as f:
    for entity in read_csv(f):
        do_things()

"""

import argparse, time

from propertysuggester.utils.datatypes import Claim, Entity
from propertysuggester.utils.CompressedFileType import CompressedFileType

def read_csv(input_file, separator=","):
    """
    @rtype : collections.Iterable[Entity]
    @type input_file: file or StringIO.StringIO
    @type separator: str
    """
    current_title = None
    claims = []
    for line in input_file:
        title, prop, datatype, value = line.strip().split(separator, 3)
        if current_title != title:
            if not current_title is None:
                yield Entity(current_title, claims)
            current_title = title
            claims = []
        claims.append(Claim(int(prop), datatype, value))

    if not current_title is None:
        yield Entity(current_title, claims)


if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("input", help="The CSV input file (a wikidata dump), gzip is supported",
                        type=CompressedFileType('r'))
    parser.add_argument("-s", "--silent", help="Show output", action="store_true")
    args = parser.parse_args()

    start = time.time()
    if args.silent:
        for element in read_csv(args.input):
            pass
    else:
        for element in read_csv(args.input):
            print element

    print "total time: %.2fs"%(time.time() - start)