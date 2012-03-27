-- phpMyAdmin SQL Dump
-- version 3.4.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 27, 2012 at 10:43 AM
-- Server version: 5.1.61
-- PHP Version: 5.3.6-13ubuntu3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `global`
--

-- --------------------------------------------------------

--
-- Table structure for table `chaperone_action`
--

CREATE TABLE IF NOT EXISTS `chaperone_action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `namespace` int(11) NOT NULL,
  `action` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `namespace_action` (`namespace`,`action`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `chaperone_action_rule_set`
--

CREATE TABLE IF NOT EXISTS `chaperone_action_rule_set` (
  `action` int(11) NOT NULL,
  `rule_set` int(11) NOT NULL,
  PRIMARY KEY (`action`,`rule_set`),
  KEY `rule_set_action` (`rule_set`,`action`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chaperone_namespace`
--

CREATE TABLE IF NOT EXISTS `chaperone_namespace` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `chaperone_role`
--

CREATE TABLE IF NOT EXISTS `chaperone_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `namespace` int(11) NOT NULL,
  `role` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `rule_set` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `namespace_role` (`namespace`,`role`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `chaperone_role_action`
--

CREATE TABLE IF NOT EXISTS `chaperone_role_action` (
  `role` int(11) NOT NULL,
  `action` int(11) NOT NULL,
  UNIQUE KEY `role_action` (`role`,`action`),
  KEY `action_role` (`action`,`role`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='This table creates a many-to-many bridge from role to action';

-- --------------------------------------------------------

--
-- Table structure for table `chaperone_rule`
--

CREATE TABLE IF NOT EXISTS `chaperone_rule` (
  `rule_set` int(11) NOT NULL,
  `context_item` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `wildcard` tinyint(1) NOT NULL,
  KEY `rule_set` (`rule_set`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chaperone_rule_set`
--

CREATE TABLE IF NOT EXISTS `chaperone_rule_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `namespace` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `namespace` (`namespace`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
