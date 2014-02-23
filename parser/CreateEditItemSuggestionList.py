import CsvReader
import random
import argparse
from WikidataApi import WikidataApi
from CompressedFileType import CompressedFileType

import json
import urllib2

pathToWiki = 'http://suggester.wmflabs.org/wiki'
threshold = 0.3 # threshold that suggestions in the results have to pass

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="this program generates a Edit-Item-Suggestions")
    parser.add_argument("input", help="The CSV input file (wikidata triple)", type=CompressedFileType('r'))
    parser.add_argument("offset", nargs='?', default="0", help="offset", type=int)
    parser.add_argument("limit", nargs='?', default="1000", help="limit", type=int)
    parser.add_argument("properties", nargs='?', default="", help="comma seperated list of properties to be considered")
    args = parser.parse_args()

    itemCount=0
    pidList = args.properties.split(",")
    
    for entity in CsvReader.read_csv(args.input):
        itemCount +=1
        if itemCount%100 == 0:
            print str(itemCount)
        if itemCount < args.offset:
            continue
        if itemCount > args.limit:
            break
        if entity.claims:
            propertyIds = [claim.property_id for claim in entity.claims] #get ids from claims
            api = WikidataApi(pathToWiki)
            result = api.wbs_getsuggestions(properties=propertyIds, limit=50, cont=0)
            suggestions = result["search"]
            suggestIds = ""
            for suggestion in suggestions:
                if float(suggestion["correlation"]) > threshold:
                    if args.properties == "" or (str(suggestion["id"][1:]) in pidList):
                        suggestIds = suggestIds + " " + str(suggestion["id"]) 
                else:
                    if suggestIds != "":
                        print 'http://www.wikidata.org/wiki'+'/Item:' + entity.title + suggestIds
                    break;