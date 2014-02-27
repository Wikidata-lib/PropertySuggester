import argparse

from collections import defaultdict
from propertysuggester.parser import CsvReader
from propertysuggester.utils.CompressedFileType import CompressedFileType


def compute_table(generator):
    """
    @type generator: collections.Iterable[Entity]
    @return: dict[int, dict]
    """
    table = {}
    for i, entity in enumerate(generator):
        if i % 100000 == 0 and i > 0:
            print "entities {0}".format(i)
        for claim in entity.claims:
            if not claim.property_id in table or table[claim.property_id]["type"] == "unknown":
                table[claim.property_id] = defaultdict(int)
                table[claim.property_id]["type"] = claim.datatype

        distinct_ids = set(claim.property_id for claim in entity.claims)
        for pid1 in distinct_ids:
            table[pid1]["appearances"] += 1
            for pid2 in distinct_ids:
                if pid1 != pid2:
                    table[pid1][pid2] += 1
    return table

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="this program generates a probability-table from a CSV-file")
    parser.add_argument("input", help="The CSV input file (wikidata triple)", type=CompressedFileType('r'))
    args = parser.parse_args()
    t = compute_table(CsvReader.read_csv(args.input))
    print t