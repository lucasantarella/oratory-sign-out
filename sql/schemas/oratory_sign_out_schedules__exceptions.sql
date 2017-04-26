# Dump of table schedules__exceptions
# ------------------------------------------------------------

CREATE TABLE `schedules__exceptions` (
  `date` bigint(8) unsigned NOT NULL,
  `ignored` tinyint(1) DEFAULT '0',
  `schedule_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`date`),
  KEY `schedules__exceptions_schedules___id_fk` (`schedule_id`),
  CONSTRAINT `schedules__exceptions_schedules___id_fk` FOREIGN KEY (`schedule_id`) REFERENCES `schedules__` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;