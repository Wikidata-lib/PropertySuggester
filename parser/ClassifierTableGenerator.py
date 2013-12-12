import argparse
import CsvReader
from collections import defaultdict

def computeTable(generator, classifier):
    table = defaultdict(lambda: defaultdict(int))
    for entity, claims in generator:
        for pid1, _, value in claims:
            if pid1 in classifier:
                pid_qid = (pid1, value)
                table[pid_qid]["appearances"] += 1
                for pid2, _, _ in claims:
                    if pid1 != pid2:
                        table[pid_qid][pid2] += 1
    return table

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="this program generates a correlation-table for classifiers from a CSV-file")
    parser.add_argument("input", help="The CSV input file (wikidata triple)")
    parser.add_argument("classifier", help="A list of classifiers (e.g. 31)", nargs="+")
    args = parser.parse_args()
    table = computeTable(CsvReader.read_csv(open(args.input, "r")), args.classifier)
    print table