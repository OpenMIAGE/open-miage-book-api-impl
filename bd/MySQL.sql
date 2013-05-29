-- phpMyAdmin SQL Dump
-- version 3.5.5
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Mer 29 Mai 2013 à 19:56
-- Version du serveur: 5.1.68-cll
-- Version de PHP: 5.3.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `openmiag_nrouzeaud`
--

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_ADMIN`
--

DROP TABLE IF EXISTS `OpenM_BOOK_ADMIN`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_ADMIN` (
  `uid` varchar(100) NOT NULL,
  `add_time` int(12) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_COMMUNITY_BANNED_USERS`
--

DROP TABLE IF EXISTS `OpenM_BOOK_COMMUNITY_BANNED_USERS`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_COMMUNITY_BANNED_USERS` (
  `community_id` bigint(16) NOT NULL,
  `banned_group_id` bigint(16) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_COMMUNITY_CONTENT_USER`
--

DROP TABLE IF EXISTS `OpenM_BOOK_COMMUNITY_CONTENT_USER`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_COMMUNITY_CONTENT_USER` (
  `group_id` bigint(16) NOT NULL,
  `user_id` int(11) NOT NULL,
  `isValidated` tinyint(1) NOT NULL DEFAULT '0',
  `creation_time` int(12) NOT NULL,
  `validation_time` int(12) DEFAULT NULL,
  `validated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`group_id`,`user_id`),
  KEY `group_id` (`user_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_COMMUNITY_CONTENT_USER_VALIDATION`
--

DROP TABLE IF EXISTS `OpenM_BOOK_COMMUNITY_CONTENT_USER_VALIDATION`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_COMMUNITY_CONTENT_USER_VALIDATION` (
  `group_id` bigint(16) NOT NULL,
  `validated_by` int(11) NOT NULL,
  `time` int(12) NOT NULL,
  `message` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_COMMUNITY_MODERATOR`
--

DROP TABLE IF EXISTS `OpenM_BOOK_COMMUNITY_MODERATOR`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_COMMUNITY_MODERATOR` (
  `group_id` bigint(16) NOT NULL,
  `group_id_moderator` bigint(16) NOT NULL,
  PRIMARY KEY (`group_id`,`group_id_moderator`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_COMMUNITY_PERIOD`
--

DROP TABLE IF EXISTS `OpenM_BOOK_COMMUNITY_PERIOD`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_COMMUNITY_PERIOD` (
  `period_id` bigint(16) NOT NULL,
  `user_id` int(11) NOT NULL,
  `group_id` bigint(16) NOT NULL,
  `start` int(12) NOT NULL,
  `end` int(12) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_COMMUNITY_TO_SECTION`
--

DROP TABLE IF EXISTS `OpenM_BOOK_COMMUNITY_TO_SECTION`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_COMMUNITY_TO_SECTION` (
  `community_id` bigint(16) NOT NULL,
  `section_id` mediumint(9) NOT NULL,
  PRIMARY KEY (`community_id`,`section_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_COMMUNITY_VISIBILITY`
--

DROP TABLE IF EXISTS `OpenM_BOOK_COMMUNITY_VISIBILITY`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_COMMUNITY_VISIBILITY` (
  `user_id` int(11) NOT NULL,
  `community_id` bigint(16) NOT NULL,
  `visibility_id` bigint(16) NOT NULL,
  PRIMARY KEY (`user_id`,`community_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_GROUP`
--

DROP TABLE IF EXISTS `OpenM_BOOK_GROUP`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_GROUP` (
  `group_id` bigint(16) NOT NULL,
  `name` varchar(40) DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_GROUP_CONTENT_GROUP`
--

DROP TABLE IF EXISTS `OpenM_BOOK_GROUP_CONTENT_GROUP`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_GROUP_CONTENT_GROUP` (
  `group_id_parent` bigint(16) NOT NULL,
  `group_id` bigint(16) NOT NULL,
  PRIMARY KEY (`group_id_parent`,`group_id`),
  KEY `group_id_parent` (`group_id`,`group_id_parent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_GROUP_CONTENT_GROUP_INDEX`
--

DROP TABLE IF EXISTS `OpenM_BOOK_GROUP_CONTENT_GROUP_INDEX`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_GROUP_CONTENT_GROUP_INDEX` (
  `group_id_parent` bigint(16) NOT NULL,
  `group_id` bigint(16) NOT NULL,
  PRIMARY KEY (`group_id_parent`,`group_id`),
  KEY `group_id_parent` (`group_id`,`group_id_parent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_GROUP_CONTENT_USER`
--

DROP TABLE IF EXISTS `OpenM_BOOK_GROUP_CONTENT_USER`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_GROUP_CONTENT_USER` (
  `group_id` bigint(16) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_GROUP_SEARCH`
--

DROP TABLE IF EXISTS `OpenM_BOOK_GROUP_SEARCH`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_GROUP_SEARCH` (
  `id` bigint(16) NOT NULL,
  `string` varchar(10) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `owner_id` int(12) DEFAULT NULL,
  PRIMARY KEY (`string`,`type`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_INVITATION`
--

DROP TABLE IF EXISTS `OpenM_BOOK_INVITATION`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_INVITATION` (
  `mail` varchar(255) NOT NULL,
  `from` int(11) NOT NULL,
  `time` int(12) NOT NULL,
  `recall` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_KEEPINFORM`
--

DROP TABLE IF EXISTS `OpenM_BOOK_KEEPINFORM`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_KEEPINFORM` (
  `email` varchar(20) NOT NULL,
  `date` bigint(12) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_SECTION`
--

DROP TABLE IF EXISTS `OpenM_BOOK_SECTION`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_SECTION` (
  `section_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `user_can_register` tinyint(1) NOT NULL,
  `only_one_community` tinyint(1) NOT NULL,
  `reg_exp` varchar(30) NOT NULL DEFAULT '.*',
  `section_id_parent` smallint(6) DEFAULT NULL,
  `validation_required` tinyint(1) NOT NULL DEFAULT '1',
  `manage_period` tinyint(1) NOT NULL DEFAULT '0',
  `user_can_add_community` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`section_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=309 ;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_SIGNAL`
--

DROP TABLE IF EXISTS `OpenM_BOOK_SIGNAL`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_SIGNAL` (
  `type` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `time` int(12) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_SIGNAL_GROUP`
--

DROP TABLE IF EXISTS `OpenM_BOOK_SIGNAL_GROUP`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_SIGNAL_GROUP` (
  `group_id` bigint(16) NOT NULL,
  `signaled_by` int(11) NOT NULL,
  `time` int(12) NOT NULL,
  `message` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_SIGNAL_USER`
--

DROP TABLE IF EXISTS `OpenM_BOOK_SIGNAL_USER`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_SIGNAL_USER` (
  `user_id` int(11) NOT NULL,
  `signaled_by` int(11) NOT NULL,
  `time` int(12) NOT NULL,
  `message` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_SIGNAL_USER_IN_GROUP`
--

DROP TABLE IF EXISTS `OpenM_BOOK_SIGNAL_USER_IN_GROUP`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_SIGNAL_USER_IN_GROUP` (
  `user_id` int(11) NOT NULL,
  `signaled_by` int(11) NOT NULL,
  `group_id` bigint(16) NOT NULL,
  `time` int(12) NOT NULL,
  `message` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_USER`
--

DROP TABLE IF EXISTS `OpenM_BOOK_USER`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_USER` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(100) NOT NULL COMMENT 'id généré par OpenM_ID',
  `creation_time` int(12) NOT NULL,
  `update_time` int(12) NOT NULL,
  `personal_groups` int(16) NOT NULL,
  `first_name` varchar(20) NOT NULL,
  `last_name` varchar(20) NOT NULL,
  `photo` bigint(16) DEFAULT NULL,
  `birthday` int(11) NOT NULL,
  `birthday_display_year` tinyint(1) NOT NULL DEFAULT '0',
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_id_Unique` (`uid`),
  KEY `first_name_last_name` (`first_name`,`last_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=70 ;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_USER_PROPERTY`
--

DROP TABLE IF EXISTS `OpenM_BOOK_USER_PROPERTY`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_USER_PROPERTY` (
  `property_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`property_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_USER_PROPERTY_VALUE`
--

DROP TABLE IF EXISTS `OpenM_BOOK_USER_PROPERTY_VALUE`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_USER_PROPERTY_VALUE` (
  `value_id` bigint(16) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `value` text,
  PRIMARY KEY (`value_id`),
  KEY `property_id` (`property_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_BOOK_USER_PROPERTY_VALUE_VISIBILITY`
--

DROP TABLE IF EXISTS `OpenM_BOOK_USER_PROPERTY_VALUE_VISIBILITY`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_USER_PROPERTY_VALUE_VISIBILITY` (
  `value_id` bigint(16) NOT NULL,
  `group_id` bigint(16) NOT NULL,
  PRIMARY KEY (`value_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_SSO_ADMIN`
--

DROP TABLE IF EXISTS `OpenM_SSO_ADMIN`;
CREATE TABLE IF NOT EXISTS `OpenM_SSO_ADMIN` (
  `user_id` varchar(200) NOT NULL,
  `user_level` smallint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_SSO_API_SESSION`
--

DROP TABLE IF EXISTS `OpenM_SSO_API_SESSION`;
CREATE TABLE IF NOT EXISTS `OpenM_SSO_API_SESSION` (
  `SSID` varchar(200) NOT NULL,
  `api_url` varchar(200) NOT NULL,
  `api_SSID` varchar(200) NOT NULL,
  `end_time` int(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_SSO_CLIENT`
--

DROP TABLE IF EXISTS `OpenM_SSO_CLIENT`;
CREATE TABLE IF NOT EXISTS `OpenM_SSO_CLIENT` (
  `client_id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_hash` varchar(200) NOT NULL,
  `is_valid` smallint(1) NOT NULL,
  `install_user_id` varchar(200) NOT NULL,
  `time` int(20) NOT NULL,
  PRIMARY KEY (`client_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_SSO_CLIENT_RIGHTS`
--

DROP TABLE IF EXISTS `OpenM_SSO_CLIENT_RIGHTS`;
CREATE TABLE IF NOT EXISTS `OpenM_SSO_CLIENT_RIGHTS` (
  `rights_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `rights_pattern` varchar(40) NOT NULL,
  PRIMARY KEY (`rights_id`),
  KEY `client_id` (`client_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Structure de la table `OpenM_SSO_SESSION`
--

DROP TABLE IF EXISTS `OpenM_SSO_SESSION`;
CREATE TABLE IF NOT EXISTS `OpenM_SSO_SESSION` (
  `SSID` varchar(100) NOT NULL,
  `oid` varchar(256) NOT NULL,
  `ip_hash` varchar(100) NOT NULL,
  `begin_time` int(20) NOT NULL,
  `api_sso_token` varchar(100) NOT NULL,
  PRIMARY KEY (`SSID`),
  KEY `begin_time` (`begin_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
