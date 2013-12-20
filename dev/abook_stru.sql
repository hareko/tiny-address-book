SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS `abook` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `abook`;

CREATE TABLE IF NOT EXISTS `towns` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(25) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fname` varchar(20) collate utf8_unicode_ci NOT NULL,
  `lname` varchar(20) collate utf8_unicode_ci NOT NULL,
  `street` varchar(30) collate utf8_unicode_ci NOT NULL,
  `zip` varchar(15) collate utf8_unicode_ci NOT NULL,
  `town_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `PERSON` (`fname`,`lname`),
  KEY `TOWN` (`town_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=13 ;


ALTER TABLE `contacts`
  ADD CONSTRAINT `town_key` FOREIGN KEY (`town_id`) REFERENCES `towns` (`id`) ON UPDATE CASCADE;