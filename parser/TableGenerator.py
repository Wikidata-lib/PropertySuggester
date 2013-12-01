import argparse
import CsvReader
from collections import defaultdict


def computeTable(f):
    table = {}
    with open(f, "r") as f:
        for entity, claims in CsvReader.read_csv(f):
            for pid1, datatype, value in claims:
                if not pid1 in table:
                    table[pid1] = defaultdict(int)
                    table[pid1]["type"] = datatype
                table[pid1]["appearances"] += 1
                for pid2, _, _ in claims:
                    if pid1 != pid2:
                        table[pid1][pid2] += 1
    return table


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="this program generates a correlation-table from a CSV-file")
    parser.add_argument("input", help="The CSV input file (wikidata triple)")
    args = parser.parse_args()
    table = computeTable(args.input)
    print table
