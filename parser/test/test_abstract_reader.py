from testtools import TestCase
from testtools.matchers import *

from claim import Claim

class AbstractUniverseTest(TestCase):
    def setUp(self):
        TestCase.setUp(self)
        self.result = [("Q1", [Claim(373, "string", "Universe"),
                               Claim(31, "wikibase-entityid", "Q223557"),
                               Claim(31, "wikibase-entityid", "Q1088088")])]

    def test_universe(self):
        self.assertThat(self.result, HasLength(1))

        q1 = self.result[0]
        self.assertThat("Q1", Equals(q1[0]))
        self.assertThat(q1[1], Contains(Claim(373, "string", "Universe")))
        self.assertThat(q1[1], Contains(Claim(31, "wikibase-entityid", "Q223557")))
        self.assertThat(q1[1], Contains(Claim(31, "wikibase-entityid", "Q1088088")))

