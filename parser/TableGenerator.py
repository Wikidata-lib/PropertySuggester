import argparse
import CsvReader

def computeTable(f):
    table = {}
    with open(args.input, "r") as f:
        for entity, claims in CsvReader.read_csv(f):
            for claim1 in claims:
                pid1 = claim1[0]
                if(not(table.has_key(pid1))):
                    row = {}
                    table[pid1] = {}
                    table[pid1]["appearances"] = 0
                    table[pid1]["type"] = claim1[1]
                table[pid1]["appearances"] += 1
                for claim2 in claims:
                    pid2 = claim2[0]
                    if(not(table[pid1].has_key(pid2))):
                        table[pid1][pid2] = 0
                    if(not(pid1 == pid2)):
                       table[pid1][pid2] += 1
    return table
    
if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="this program generates a correlation-table from a CSV-file")
    parser.add_argument("input", help="The CSV input file (wikidata triple)")
    args = parser.parse_args()
    table = computeTable(args.input)
    print table
