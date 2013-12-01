"""
read_csv returns a generator that yields the tuple (title, [(p1, dt1, v1), (p2, dt1, v2),..])
where
p_n is a property
d_n is a datatype
v_n is a value

usage:
with open("file.csv", "r") as f:
    for title, claim in read_csv(f):
        do_things()

"""

import argparse, gzip, time
from CompressedFileType import CompressedFileType


def read_csv(input_file, seperator=","):
    current_title = None
    claims = []
    for line in input_file:
        title, prop, datatype, value = line.strip().split(seperator, 3)
        if current_title != title:
            if not current_title is None:
                yield current_title, claims
            current_title = title
            claims = []
        claims.append((prop, datatype, value))

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
            prop, datatype, value = line.strip().split(seperator, 2)
            claims.append((prop, datatype, value))
    if not title is None:
        yield title, claims


if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("input", help="The CSV input file (a wikidata dump), gzip is supported",
                        type=CompressedFileType('r'))
    parser.add_argument("-c", "--compressed", help="Use compressed csv (every entity is shown only once)",
                        action="store_true")
    parser.add_argument("-s", "--silent", help="Show output", action="store_true")
    args = parser.parse_args()

    if args.compressed:
        read_method = read_compressed_csv
    else:
        read_method = read_csv

    start = time.time()
    if args.silent:
        for element in read_method(args.input):
            pass
    else:
        for element in read_method(args.input):
            print element

    print "total time: %.2fs"%(time.time() - start)