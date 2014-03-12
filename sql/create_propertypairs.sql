
CREATE TABLE IF NOT EXISTS /*_*/wbs_propertypairs(
  pid1              INT unsigned    NOT NULL,
  pid2              INT unsigned    NOT NULL,
  count             INT unsigned    NOT NULL,
  probability       FLOAT           NOT NULL,
  PRIMARY KEY(pid1, pid2)
) /*$wgDBTableOptions*/;

