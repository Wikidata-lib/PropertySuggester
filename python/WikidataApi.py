import requests


class WikidataApi:
    def __init__(self, url):
        self.url = url

    def wbs_getsuggestions(self, entity="", properties=(), limit=10, cont=0, language='en', search=''):
        """
        @rtype : dict
        @type entity: str
        @type properties: tuple[int] or list[int]
        @type limit: int
        @type cont: int
        @type language: str
        @type search: str
        """
        if (entity and properties) or (not entity and not properties):
            raise AttributeError("provide either a entity or properties")

        params = {'action': 'wbsgetsuggestions', 'format': 'json',
                  'limit': limit, 'continue': cont,
                  'language': language, 'search': search}

        if entity:
            params['entity'] = entity
        elif properties:
            params['properties'] = ','.join(map(str, properties))

        result = requests.get(self.url + "/api.php", params=params)
        return result.json()
