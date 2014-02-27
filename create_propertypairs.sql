
CREATE TABLE wbs_propertyPairs(
  pid1 INT,
  pid2 INT,
  count INT,
  probability FLOAT,
  PRIMARY KEY(pid1, pid2)
);