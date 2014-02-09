from StringIO import StringIO
import unittest
from testtools import TestCase
from testtools.matchers import *
import gzip
import CsvReader, CsvWriter, XmlReader
from test.test_abstract_reader import AbstractUniverseTest

class CsvReaderTest(AbstractUniverseTest):
    def setUp(self):
        TestCase.setUp(self)
        with gzip.open("test/Wikidata-Q1.xml.gz", "r") as f:
            out = StringIO()
            # for testing generate csv from xml just to parse it again
            CsvWriter.write_csv(XmlReader.read_xml(f, thread_count=1), out)
            out.seek(0)
        self.result = list(CsvReader.read_csv(out))


if __name__ == '__main__':
    unittest.main()

