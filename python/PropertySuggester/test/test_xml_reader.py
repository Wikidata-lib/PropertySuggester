import unittest
import gzip

from testtools import TestCase
from testtools.matchers import *

from propertysuggester.test.test_abstract_reader import AbstractUniverseTest
from propertysuggester.parser import XmlReader


class XmlReaderTest(AbstractUniverseTest):
    def setUp(self):
        TestCase.setUp(self)
        with gzip.open("Wikidata-Q1.xml.gz", "r") as f:
            self.result = list(XmlReader.read_xml(f))



class MultiprocessingBigTest(TestCase):
    def test_simple_multiprocessing(self):
        r1 = list(XmlReader.read_xml(gzip.open("Wikidata-Q1.xml.gz"), 1))
        r4 = list(XmlReader.read_xml(gzip.open("Wikidata-Q1.xml.gz"), 4))

        self.assertThat(r1, HasLength(1))
        self.assertThat(r4, Equals(r1))

    def test_multiprocessing(self):
        r1 = list(XmlReader.read_xml(gzip.open("Wikidata-20131129161111.xml.gz"), 1))
        r4 = list(XmlReader.read_xml(gzip.open("Wikidata-20131129161111.xml.gz"), 4))

        self.assertThat(r1, HasLength(87))
        self.assertThat(r4, Equals(r1))

if __name__ == '__main__':
    unittest.main()

