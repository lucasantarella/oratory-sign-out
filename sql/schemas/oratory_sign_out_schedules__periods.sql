# Dump of table schedules__periods
# ------------------------------------------------------------

CREATE TABLE `schedules__periods` (
  `schedule_id` bigint(20) unsigned NOT NULL,
  `period` tinyint(1) unsigned NOT NULL,
  `start_time` int(4) unsigned zerofill NOT NULL,
  `end_time` int(4) unsigned zerofill NOT NULL,
  KEY `schedules__periods_schedules___id_fk` (`schedule_id`),
  CONSTRAINT `schedules__periods_schedules___id_fk` FOREIGN KEY (`schedule_id`) REFERENCES `schedules__` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;