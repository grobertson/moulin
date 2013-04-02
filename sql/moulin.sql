-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 01, 2013 at 11:53 PM
-- Server version: 5.5.29
-- PHP Version: 5.3.10-1ubuntu3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `moulin`
--

-- --------------------------------------------------------

--
-- Table structure for table `gearman_jobs`
--

CREATE TABLE IF NOT EXISTS `gearman_jobs` (
  `moulin_id` int(16) NOT NULL AUTO_INCREMENT,
  `job_handle` char(64) DEFAULT NULL,
  `job_class` char(32) NOT NULL,
  `job_function` char(32) NOT NULL,
  `job_uuid` char(48) NOT NULL,
  `job_status` char(16) NOT NULL,
  `job_last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `job_dispatched` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `job_completed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `job_work` longtext NOT NULL,
  `job_product` longtext NOT NULL,
  PRIMARY KEY (`moulin_id`),
  UNIQUE KEY `moulin_id` (`moulin_id`),
  KEY `status_lookup` (`job_status`),
  KEY `class_function_status` (`job_status`,`job_class`,`job_function`),
  KEY `uuid_lookup` (`job_uuid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13842 ;

-- --------------------------------------------------------

--
-- Table structure for table `job_registry`
--

CREATE TABLE IF NOT EXISTS `job_registry` (
  `job_name` char(64) NOT NULL,
  `last_run` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `job_name_pri` (`job_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `notify_twilio`
--

CREATE TABLE IF NOT EXISTS `notify_twilio` (
  `sid` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_created` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_updated` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_sent` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `account_sid` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `to_number` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `from_number` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `body` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `direction` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `api_version` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `price` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uri` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Repository of people';

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
