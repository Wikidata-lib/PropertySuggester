import argparse
import CsvReader
from collections import defaultdict

def computeTable(f):
    table = defaultdict(int)
    with open(f, "r") as f:
        for entity, claims in CsvReader.read_csv(f):
            for claim1 in claims:
                pid1 = claim1[0]
                if not pid1 in table:
                    table[pid1] = defaultdict(int)
                    table[pid1]["type"] = claim1[1]
                table[pid1]["appearances"] += 1
                for claim2 in claims:
                    pid2 = claim2[0]
                    if pid1 != pid2:
                       table[pid1][pid2] += 1
    return table
    
if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="this program generates a correlation-table from a CSV-file")
    parser.add_argument("input", help="The CSV input file (wikidata triple)")
    args = parser.parse_args()
    table = computeTable(args.input)
    print table
