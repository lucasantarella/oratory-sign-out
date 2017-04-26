# Dump of table schedules__
# ------------------------------------------------------------

CREATE TABLE `schedules__` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `default` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;