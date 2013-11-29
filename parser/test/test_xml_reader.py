from StringIO import StringIO
import unittest
from testtools import TestCase
from testtools.matchers import *
import gzip
import CsvReader
import CsvWriter

import XmlReader


class AbstractUniverseTest(TestCase):
    def setUp(self):
        TestCase.setUp(self)
        self.result = [("Q1", [("373", "string", "Universe"),
                               ("31", "wikibase-entityid", "Q223557"),
                               ("31", "wikibase-entityid", "Q1088088")])]

    def test_universe(self):
        self.assertThat(len(self.result), Equals(1))

        q1 = self.result[0]
        self.assertThat("Q1", Equals(q1[0]))
        self.assertThat(q1[1], Contains(("373", "string", "Universe")))
        self.assertThat(q1[1], Contains(("31", "wikibase-entityid", "Q223557")))
        self.assertThat(q1[1], Contains(("31", "wikibase-entityid", "Q1088088")))


class XmlReaderTest(AbstractUniverseTest):
    def setUp(self):
        TestCase.setUp(self)
        with gzip.open("../test/Wikidata-Q1.xml.gz", "r") as f:
            self.result = list(XmlReader.read_xml(f))


class CsvReaderTest(AbstractUniverseTest):
    def setUp(self):
        TestCase.setUp(self)
        with gzip.open("../test/Wikidata-Q1.xml.gz", "r") as f:
            out = StringIO()
            # for testing generate csv from xml just to parse it again
            CsvWriter.write_csv(XmlReader.read_xml(f), out)
            out.seek(0)
        self.result = list(CsvReader.read_csv(out))


class CompressedCsvReaderTest(AbstractUniverseTest):
    def setUp(self):
        TestCase.setUp(self)
        with gzip.open("../test/Wikidata-Q1.xml.gz", "r") as f:
            out = StringIO()
            # for testing generate csv from xml just to parse it again
            CsvWriter.write_compressed_csv(XmlReader.read_xml(f), out)
            out.seek(0)
        self.result = list(CsvReader.read_compressed_csv(out))

if __name__ == '__main__':
    unittest.main()
