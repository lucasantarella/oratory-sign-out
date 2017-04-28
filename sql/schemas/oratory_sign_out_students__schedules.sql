# Dump of table students__schedules
# ------------------------------------------------------------

CREATE TABLE `students__schedules` (
  `student_id` bigint(20) unsigned NOT NULL,
  `quarter` int(1) unsigned NOT NULL,
  `cycle_day` int(1) unsigned NOT NULL,
  `period` int(1) unsigned NOT NULL,
  `room` varchar(20) NOT NULL DEFAULT '',
  KEY `students__schedules_student_id_period_index` (`student_id`,`period`),
  KEY `students__schedules_schedules__quarters_quarter_num_fk` (`quarter`),
  KEY `students__schedules_rooms___name_fk` (`room`),
  CONSTRAINT `students__schedules_rooms___name_fk` FOREIGN KEY (`room`) REFERENCES `rooms__` (`name`),
  CONSTRAINT `students__schedules_schedules__quarters_quarter_num_fk` FOREIGN KEY (`quarter`) REFERENCES `schedules__quarters` (`quarter_num`),
  CONSTRAINT `students__schedules_students___id_fk` FOREIGN KEY (`student_id`) REFERENCES `students__` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;