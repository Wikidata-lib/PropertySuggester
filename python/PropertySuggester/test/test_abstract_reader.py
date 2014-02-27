from testtools import TestCase
from testtools.matchers import *

from propertysuggester.utils.datatypes import Claim, Entity


class AbstractUniverseTest(TestCase):
    def setUp(self):
        TestCase.setUp(self)
        self.result = [Entity("Q1", [Claim(373, "string", "Universe"),
                                     Claim(31, "wikibase-entityid", "Q223557"),
                                     Claim(31, "wikibase-entityid", "Q1088088")])]

    def test_universe(self):
        self.assertThat(self.result, HasLength(1))

        q1 = self.result[0]
        self.assertThat(q1.title, Equals("Q1"))
        self.assertThat(q1.claims, Contains(Claim(373, "string", "Universe")))
        self.assertThat(q1.claims, Contains(Claim(31, "wikibase-entityid", "Q223557")))
        self.assertThat(q1.claims, Contains(Claim(31, "wikibase-entityid", "Q1088088")))

