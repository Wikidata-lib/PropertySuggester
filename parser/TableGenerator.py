import argparse
import CsvReader

from collections import defaultdict
from CompressedFileType import CompressedFileType

def compute_table(generator):
    """
    @type generator: collections.Iterable[(string, list[Claim])]
    @return: dict[int, dict]
    """
    table = {}
    for entity, claims in generator:
        for claim in claims:
            if not claim.property_id in table:
                table[claim.property_id] = defaultdict(int)
                table[claim.property_id]["type"] = claim.datatype
            table[claim.property_id]["appearances"] += 1
            for claim2 in claims:
                if claim.property_id != claim2.property_id:
                    table[claim.property_id][claim2.property_id] += 1
    return table

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="this program generates a correlation-table from a CSV-file")
    parser.add_argument("input", help="The CSV input file (wikidata triple)", type=CompressedFileType('r'))
    args = parser.parse_args()
    table = compute_table(CsvReader.read_csv(args.input))
    print table