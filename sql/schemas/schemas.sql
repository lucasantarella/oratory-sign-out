# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: localhost (MySQL 5.7.17)
# Database: oratory_sign_out
# Generation Time: 2017-04-28 17:12:34 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table rooms__
# ------------------------------------------------------------

CREATE TABLE `rooms__` (
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table schedules__
# ------------------------------------------------------------

CREATE TABLE `schedules__` (
  `id` bigint(20) unsigned NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `default` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



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



# Dump of table schedules__quarters
# ------------------------------------------------------------

CREATE TABLE `schedules__quarters` (
  `quarter_num` int(1) unsigned NOT NULL,
  `start_date` int(4) unsigned zerofill NOT NULL,
  `end_date` int(4) unsigned zerofill NOT NULL,
  PRIMARY KEY (`quarter_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table students__
# ------------------------------------------------------------

CREATE TABLE `students__` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `middle_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



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




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
