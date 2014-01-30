import argparse
import MySQLdb
import time

import CsvReader
import TableGenerator
from CompressedFileType import CompressedFileType


def pushDictContentIntoDB(table, db):
    db.execute("CREATE TABLE IF NOT EXISTS wbs_properties(pid INT, count INT, type varchar(20), primary key(pid))")
    db.execute("CREATE TABLE IF NOT EXISTS wbs_propertyPairs(pid1 INT, pid2 INT, count INT, correlation FLOAT, primary key(pid1, pid2))")
    db.execute("DELETE FROM wbs_propertyPairs")
    db.execute("DELETE FROM wbs_properties")

    print "properties: {0}".format(len(table))
    rowcount = 0
    for pid1, row in table.iteritems():
        db.execute("INSERT INTO wbs_properties(pid, count, type) VALUES (%s, %s, %s)", (pid1, row["appearances"], row["type"]))
        for pid2, value in row.iteritems():
            if pid1 != pid2 and pid2.isdigit() and value > 0:  # "appearances" and "type" is in the same table, ignore them
                correlation = value/float(row["appearances"])
                db.execute("INSERT INTO wbs_propertyPairs(pid1, pid2, count, correlation) VALUES (%s, %s, %s, %s)", (pid1, pid2, value, correlation))
                rowcount += 1
                if not rowcount % 1000:
                    print "rows {0}".format(rowcount)


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="this program generates a correlation-table from a CSV-file")
    parser.add_argument("input", help="The CSV input file (wikidata triple)", type=CompressedFileType('r'))
    parser.add_argument("db", help="target database")
    parser.add_argument("--host", help="DB host", default="127.0.0.1")
    parser.add_argument("--user", help="username for DB", default="root")
    parser.add_argument("--pw", help="pw for DB", default="")
    args = parser.parse_args()
    connection = MySQLdb.connect(host=args.host, user=args.user, passwd=args.pw, db=args.db)
    db = connection.cursor()
    start = time.time()
    print "computing table"
    table = TableGenerator.computeTable(CsvReader.read_csv(args.input))
    print "done - {0:.2f}s".format(time.time()-start)
    print "writing to database"
    pushDictContentIntoDB(table, db)
    db.close()
    connection.commit()


