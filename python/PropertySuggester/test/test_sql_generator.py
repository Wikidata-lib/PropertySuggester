import unittest
from testtools import TestCase
from mockito import mock, contains, verify, verifyNoMoreInteractions

from propertysuggester import SqlGenerator


class SqlGeneratorTest(TestCase):
    def setUp(self):
        TestCase.setUp(self)
        self.db = mock()

    def testCreateTable(self):
        table = {1: {'appearances': 8, 'type': 'string', 1: 0, 2: 5, 3: 0}}
        SqlGenerator.load_table_into_db(table, self.db)

        verify(self.db).execute(contains("CREATE TABLE IF NOT EXISTS wbs_properties"))
        verify(self.db).execute(contains("CREATE TABLE IF NOT EXISTS wbs_propertyPairs"))
        verify(self.db).execute(contains("DELETE FROM wbs_properties"))
        verify(self.db).execute(contains("DELETE FROM wbs_propertyPairs"))
        verify(self.db).execute(contains("INSERT INTO wbs_properties"), (1, 8.0, 'string'))
        verify(self.db).execute(contains("INSERT INTO wbs_propertyPairs"), (1, 2, 5, 5.0 / 8.0))
        verifyNoMoreInteractions(self.db)


if __name__ == '__main__':
    unittest.main()