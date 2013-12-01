from StringIO import StringIO
import unittest
from testtools import TestCase
from testtools.matchers import *
import gzip
import TableGenerator
import os

fn = os.path.join(os.path.dirname(__file__), 'CsvsQ15Test.csv')

class TableGeneratorTest(TestCase):

    def setUp(self):
        TestCase.setUp(self)
    def testTableGenerator(self):
        table = TableGenerator.computeTable(fn)
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