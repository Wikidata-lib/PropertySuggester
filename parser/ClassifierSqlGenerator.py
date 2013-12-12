import argparse
import CsvReader
import TableGenerator
import MySQLdb
import time


def pushDictContentIntoDB(table, db):
    db.execute("""CREATE TABLE IF NOT EXISTS wbs_propertyvaluepairs (
                    id INT NOT NULL,
                    pid INT UNSIGNED NOT NULL,
                    qid INT UNSIGNED NOT NULL,
                    count INT UNSIGNED NOT NULL,
                    PRIMARY KEY (id),
                    UNIQUE INDEX pid_qid (pid, qid)
                )"""
    )
    db.execute("""CREATE TABLE wbs_propertyvaluecorrelations (
                    pair_id INT UNSIGNED NOT NULL,
                    pid INT UNSIGNED NOT NULL,
                    count INT UNSIGNED NOT NULL,
                    correlation FLOAT NOT NULL,
                    PRIMARY KEY (pair_id, pid)
                )"""
    )
    db.execute("DELETE FROM wbs_propertyvaluepairs")
    db.execute("DELETE FROM wbs_propertyvaluecorrelations")

    print "properties: {0}".format(len(table))
    rowcount = 0
    pair_counter = 0
    for (pid1, qid), row in table.iteritems():
        db.execute("INSERT INTO wbs_propertyvaluepairs(pid, qid, count) VALUES (%s, %s, %s)",
                   (pid1, qid, row["appearances"]))
        for pid2, value in row.iteritems():
            if pid1 != pid2 and pid2.isdigit() and value > 0:
                correlation = value / float(row["appearances"])
                db.execute("INSERT INTO wbs_propertyvaluecorrelations(pair_id, pid, count, correlation) "
                           "VALUES (%s, %s, %s, %s)",
                           (pair_counter, pid2, value, correlation))
                rowcount += 1
                if not rowcount % 1000:
                    print "rows {0}".format(rowcount)
        pair_counter += 1

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="this program generates a correlation-table from a CSV-file")
    parser.add_argument("input", help="The CSV input file (wikidata triple)")
    parser.add_argument("classifier", help="A list of classifiers (e.g. 31)", nargs="+")
    parser.add_argument("db", help="target database")
    parser.add_argument("--host", help="DB host", default="127.0.0.1")
    parser.add_argument("--user", help="username for DB", default="root")
    parser.add_argument("--pw", help="pw for DB", default="")
    args = parser.parse_args()
    connection = MySQLdb.connect(host=args.host, user=args.user, passwd=args.pw, db=args.db)
    db = connection.cursor()
    start = time.time()
    print "computing table"
    table = TableGenerator.computeTable(CsvReader.read_csv(open(args.input, "r")))
    print "done - {0:.2f}s".format(time.time() - start)
    print "writing to database"
    pushDictContentIntoDB(table, db)
    db.close()
    connection.commit()


