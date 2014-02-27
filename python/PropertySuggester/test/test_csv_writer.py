from StringIO import StringIO
import unittest
from testtools import TestCase
from testtools.matchers import *

from propertysuggester.utils.datatypes import Entity, Claim
from propertysuggester import CsvWriter

test_data = [Entity('Q15', [Claim(31, 'wikibase-entityid', 'Q5107'),
                            Claim(373, 'string', 'Europe')])]


class CsvWriterTest(TestCase):
    def setUp(self):
        TestCase.setUp(self)
        out = StringIO()
        CsvWriter.write_csv(test_data, out)
        out.seek(0)

        line = out.readline()
        self.assertThat(line, Equals("Q51,31,wikibase-entityid,Q5107\n"))

        line = out.readline()
        self.assertThat(line, Equals("Q51,373,string,Europe\n"))

        self.assertThat(out.read(), Equals(""))


if __name__ == '__main__':
    unittest.main()

