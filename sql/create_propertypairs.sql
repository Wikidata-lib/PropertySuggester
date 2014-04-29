
CREATE TABLE IF NOT EXISTS /*_*/wbs_propertypairs (
  row_id            INT unsigned    NOT NULL PRIMARY KEY AUTO_INCREMENT,
  pid1              INT unsigned    NOT NULL,
  qid1              INT unsigned    NULL,
  pid2              INT unsigned    NOT NULL,
  count             INT unsigned    NOT NULL,
  probability       FLOAT           NOT NULL,
  context           VARBINARY(32)   NOT NULL
) /*$wgDBTableOptions*/;


CREATE INDEX /*i*/propertypairs_pid1_pid2_qid1 ON /*_*/wbs_propertypairs (pid1, qid1, pid2, context);

CREATE INDEX /*i*/propertypairs_pid1_pid2 ON /*_*/wbs_propertypairs (pid1, pid2, context);

CREATE INDEX /*i*/propertypairs_pid1_qid1 ON /*_*/wbs_propertypairs (pid1, qid1, context);
