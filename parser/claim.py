
class Claim:
    def __init__(self, property_id, datatype, value):
        """
        @type property_id: int
        @type datatype: str
        @type value: str
        """
        self.property_id = property_id
        self.datatype = datatype
        self.value = value

    def __eq__(self, other):
        return self.__dict__ == other.__dict__

    def __str__(self):
        return str(self.__dict__)