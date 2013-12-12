import unittest
from testtools import TestCase
from testtools.matchers import *
import ClassifierTableGenerator

test_data1 = [('Q15', [('31', 'wikibase-entityid', 'Q5107'),
                       ('373', 'string', 'Africa')]),
              ('Q16', [('31', 'wikibase-entityid', 'Q5107')])]


class ClassifierTableGeneratorTest(TestCase):
    def setUp(self):
        TestCase.setUp(self)

    def testTableGenerator(self):
        table = ClassifierTableGenerator.computeTable(test_data1, ['31'])
        self.assertThat(table, Contains(('31', 'Q5107')))

        self.assertThat(table[('31', 'Q5107')]['appearances'], Equals(2))
        self.assertThat(table[('31', 'Q5107')]['31'], Equals(0))
        self.assertThat(table[('31', 'Q5107')]['373'], Equals(1))


if __name__ == '__main__':
    unittest.main()