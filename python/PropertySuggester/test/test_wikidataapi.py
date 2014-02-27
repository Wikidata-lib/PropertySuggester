from testtools import TestCase
from testtools.matchers import *

from propertysuggester.utils.WikidataApi import WikidataApi


class WikiDataApiTest(TestCase):
    def setUp(self):
        TestCase.setUp(self)

        self.api = WikidataApi("http://suggester.wmflabs.org/wiki/")

    def test_wbsgetsuggestions(self):
        result = self.api.wbs_getsuggestions(entity="Q4", limit=3, cont=2)

        self.assertThat(result["success"], Equals(1))
        self.assertThat(result["search-continue"], Equals(5))
        self.assertThat(result["search"], HasLength(3))

    def test_wbsgetsuggestions_by_properties(self):
        result = self.api.wbs_getsuggestions(properties=[31, 373], limit=2, cont=5)

        self.assertThat(result["success"], Equals(1))
        self.assertThat(result["search-continue"], Equals(7))
        self.assertThat(result["search"], HasLength(2))