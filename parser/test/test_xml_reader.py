from StringIO import StringIO
import unittest
from testtools import TestCase
from testtools.matchers import *
import gzip
import CsvReader, CsvWriter, XmlReader


class AbstractUniverseTest(TestCase):
    def setUp(self):
        TestCase.setUp(self)
        self.result = [("Q1", [("373", "string", "Universe"),
                               ("31", "wikibase-entityid", "Q223557"),
                               ("31", "wikibase-entityid", "Q1088088")])]

    def test_universe(self):
        self.assertThat(self.result, HasLength(1))

        q1 = self.result[0]
        self.assertThat("Q1", Equals(q1[0]))
        self.assertThat(q1[1], Contains(("373", "string", "Universe")))
        self.assertThat(q1[1], Contains(("31", "wikibase-entityid", "Q223557")))
        self.assertThat(q1[1], Contains(("31", "wikibase-entityid", "Q1088088")))


class XmlReaderTest(AbstractUniverseTest):
    def setUp(self):
        TestCase.setUp(self)
        with gzip.open("test/Wikidata-Q1.xml.gz", "r") as f:
            self.result = list(XmlReader.read_xml(f))


class CsvReaderTest(AbstractUniverseTest):
    def setUp(self):
        TestCase.setUp(self)
        with gzip.open("test/Wikidata-Q1.xml.gz", "r") as f:
            out = StringIO()
            # for testing generate csv from xml just to parse it again
            CsvWriter.write_csv(XmlReader.read_xml(f), out)
            out.seek(0)
        self.result = list(CsvReader.read_csv(out))


class MultiprocessingBigTest(TestCase):
    def test_simple_multiprocessing(self):
        r1 = list(XmlReader.read_xml(gzip.open("test/Wikidata-Q1.xml.gz"), 1))
        r4 = list(XmlReader.read_xml(gzip.open("test/Wikidata-Q1.xml.gz"), 4))

        self.assertThat(r1, HasLength(1))
        self.assertThat(r4, Equals(r1))

    def test_multiprocessing(self):
        r1 = list(XmlReader.read_xml(gzip.open("test/Wikidata-20131129161111.xml.gz"), 1))
        r4 = list(XmlReader.read_xml(gzip.open("test/Wikidata-20131129161111.xml.gz"), 4))

        self.assertThat(r1, HasLength(87))
        self.assertThat(r4, Equals(r1))

if __name__ == '__main__':
    unittest.main()

