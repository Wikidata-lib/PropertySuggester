import argparse
import CsvReader
import TableGenerator
from collections import defaultdict
import MySQLdb

def pushDictContentIntoDB(2dDict, db):
    db.execute("DROP TABLE IF EXISTS propertyPairs")
    db.execute("create table propertyPairs(pid1 INT, pid2 INT, count INT, primary key(pid1, pid2))")
    db.execute("DROP TABLE IF EXISTS properties")
    db.execute("create table properties(pid INT, count INT, type varchar(20), primary key(pid))")
    for pid1 in 2dDict:
        db.execute("INSERT INTO properties(pid, count, type) VALUES" + "(" + str(pid1) + ", " + str(2dDict[pid1][appearances])+", " + 2dDict[pid1][type]+")")
        for pid2 in 2dDict[pid1]:
            if pid2.isdigit() and pid1 != pid2:
                db.execute("INSERT INTO propertyPairs(pid1, pid2, count) VALUES" + "(" + str(pid1) + ", " + str(pid2)+", " + str(2dDict[pid1][pid2])+")")
                
if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="this program generates a correlation-table from a CSV-file")
    parser.add_argument("input", help="The CSV input file (wikidata triple)")
    parser.add_argument("host", help="DB host")
    parser.add_argument("user", help="username for DB")
    parser.add_argument("pw", help="pw for DB")
    parser.add_argument("db", help="Target DB")
    args = parser.parse_args()
    connection = MySQLdb.connect(host="localhost",user="root",passwd="newpassword",db="engy1")
    db = connection.cursor()
    2dDict = TableGenerator.computeTable(CsvReader.read_csv(open(args.input, "r")))
    pushDictContentIntoDB(2dDict, db)
    

