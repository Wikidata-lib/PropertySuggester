
ALTER TABLE /*_*/wbs_propertypairs
	ADD COLUMN qid1 INT NOT NULL AFTER pid1,
	DROP PRIMARY KEY,
	ADD PRIMARY KEY (pid1, pid2, qid1)
	/*$wgDBTableOptions*/;