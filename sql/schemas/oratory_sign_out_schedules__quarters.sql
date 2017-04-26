# Dump of table schedules__quarters
# ------------------------------------------------------------

CREATE TABLE `schedules__quarters` (
  `quarter_num` int(1) unsigned NOT NULL,
  `start_date` int(4) unsigned zerofill NOT NULL,
  `end_date` int(4) unsigned zerofill NOT NULL,
  PRIMARY KEY (`quarter_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;