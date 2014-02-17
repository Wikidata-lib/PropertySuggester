import argparse
import ItemRelationsCsvReader
from collections import defaultdict
from CompressedFileType import CompressedFileType
import cPickle as pickle
from collections import Counter
from collections import defaultdict

def computeTable(generator):
    i = 0
    table = {}
    for entity, claims in generator:
        i+=1
        if(i%750000==0):
            print str(i/75000)+"%"
        for pid, value, datatype in claims:
            if not entity in table:
               table[entity] = []
            if datatype == "wikibase-entityid":
                if not pid in table:
                    table[pid] = defaultdict(int)
                table[pid]["appearances"] += 1
            table[entity].append((pid, value))
    return table

def findClassifiers(table):
    #Select top 10 most frequently used properties:
    i = 0
    minFrequency = 0.5
    properties = []
    for pid in table:
        if pid[0]!="Q":
            properties.append(pid)
    mostFrequentlyUsed = sorted(properties, key=lambda pid: table[pid]["appearances"], reverse=True) 
    mostFrequentlyUsed = mostFrequentlyUsed[:10] #Top ten most used properties with datatype = "wikibase-entityid"
    #print mostFrequentlyUsed
    propertyDic = {}
    for prop in mostFrequentlyUsed:
        propertyDic[prop] = {}
        propertyDic[prop]["entities"] = []
        propertyDic[prop]["values"] = []
    for item in table:
        i+=1
        if(i%750000==0):
            print str(i/75000)+"%" #toDo: correct progress!
        if item[0] == "Q":
            for prop in table[item]:
                if prop[0] in propertyDic:
                    propertyDic[prop[0]]["values"].append(prop[1]) # create list of values that occur in connection with the current property
                    propertyDic[prop[0]]["entities"].append(item)  # create list of items that are described by current property
    for prop in propertyDic:
        print "rating P" + str(prop)
        propertyDic[prop]["values"] = Counter(propertyDic[prop]["values"]).most_common(10) #find most common values in connection with the current (potential classifying) property
        propertyDic[prop]["entities"] = list(set(propertyDic[prop]["entities"])) #eliminate dublicates
        propertyDic[prop]["predictions"] = []
        for value in propertyDic[prop]["values"]:
            propertyDic[prop][value[0]] = []
            for entity in propertyDic[prop]["entities"]:
                if (prop, value[0]) in table[entity]:
                    propertyDic[prop][value[0]].append(entity) #create List of entities that are described by the current property-value combination
            commonProperties = computeCommonProperties(table, propertyDic[prop][value[0]], minFrequency)
            #print commonProperties
            propertyDic[prop]["predictions"] = propertyDic[prop]["predictions"] + commonProperties #create List of potential predictions for the current property combined with the most common values for this property
        propertyDic[prop]["predictions"] = (set(propertyDic[prop]["predictions"]) - set(prop)) - set(computeCommonProperties(table, propertyDic[prop]["entities"], 0.3)) #accumlated potential predictions minus obvious ones (current property itself) and (general) ones that don't depend on one of the most common property-value-combination
        propertyDic[prop]["rating"] = len(propertyDic[prop]["predictions"])
        #print propertyDic[prop]["predictions"]
    result = []
    for prop in propertyDic:
        result.append((prop, propertyDic[prop]["rating"]))
    result = sorted(result, key=lambda prop: prop[1], reverse=True)
    return result

def computeCommonProperties(table, ItemList, minFrequency): #creates List of properties that occur with more than minFrequency in ItemList
    itemCount = len(ItemList)
    propertyDic = {}
    for item in ItemList:
        for prop in table[item]:
            if not prop[0] in propertyDic:
                propertyDic[prop[0]] = defaultdict(int)
            propertyDic[prop[0]]["appearances"] += 1
    commonProperties = []
    for prop in propertyDic:
        if((propertyDic[prop]["appearances"]/float(itemCount))>minFrequency):
            commonProperties.append(prop)
    return commonProperties

#problem: certain items just have more attributes or much less! (Commons Category) when a certain property value combination occurs - therefore lower confidence level for finding general common properties

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="this program finds classifying attributes in wikidata")
    parser.add_argument("input", help="The CSV input file (wikidata triple)", type=CompressedFileType("r"))
    args = parser.parse_args()
    print "computing table..."
    table = computeTable(ItemRelationsCsvReader.read_csv(args.input))
    print "finding potential classifiers"
    result = findClassifiers(table)
    print result
    print "success"

    

