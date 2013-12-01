from StringIO import StringIO
import unittest
from testtools import TestCase
from testtools.matchers import *
import gzip
import TableGenerator
import os

class TableGeneratorTest(TestCase):

    def setUp(self):
        TestCase.setUp(self)

    def buildGen(self):
        yield 'Q15', [('31', 'wikibase-entityid', 'Q5107'), ('373', 'string', 'Africa'), ('625', 'globecoordinate', 'N7.188056, E21.093611')]

    def testTableGenerator(self):
        table = TableGenerator.computeTable(self.buildGen())
        self.assertTrue('31' in table)
        self.assertTrue('373' in table)
        self.assertTrue('625' in table)
        for pid1 in table:
            self.assertThat(table[pid1]["appearances"], Equals(1))
            self.assertThat(type(table[pid1]["type"]), Equals(str))
            for pid2 in table[pid1]:
                if pid2.isdigit():
                    if pid1 == pid2:
                        self.assertThat(table[pid1][pid2], Equals(0))
                    else:
                        self.assertThat(table[pid1][pid2], Equals(1))

if __name__ == '__main__':
    unittest.main()