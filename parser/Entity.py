

class Entity:
    def __init__(self, name, claims):
        self.title = name
        self.claims = claims

    def __eq__(self, other):
        return self.__dict__ == other.__dict__

    def __str__(self):
        return "title: {0} claims: {1}".format(self.title, map(str,self.claims))