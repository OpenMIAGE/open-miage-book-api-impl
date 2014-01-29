DROP TABLE IF EXISTS `OpenM_BOOK_ADMIN`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_ADMIN` (
  `uid` varchar(100) NOT NULL,
  `add_time` int(12) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_BOOK_COMMUNITY_BANNED_USERS`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_COMMUNITY_BANNED_USERS` (
  `community_id` bigint(16) NOT NULL,
  `banned_group_id` bigint(16) NOT NULL,
  PRIMARY KEY (`community_id`,`banned_group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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

DROP TABLE IF EXISTS `OpenM_BOOK_COMMUNITY_CONTENT_USER_VALIDATION`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_COMMUNITY_CONTENT_USER_VALIDATION` (
  `group_id` bigint(16) NOT NULL,
  `user_id` int(11) NOT NULL,
  `validated_by` int(11) NOT NULL,
  `time` int(12) NOT NULL,
  `message` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_BOOK_COMMUNITY_MODERATOR`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_COMMUNITY_MODERATOR` (
  `group_id` bigint(16) NOT NULL,
  `group_id_moderator` bigint(16) NOT NULL,
  PRIMARY KEY (`group_id`,`group_id_moderator`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_BOOK_COMMUNITY_PERIOD`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_COMMUNITY_PERIOD` (
  `period_id` bigint(16) NOT NULL,
  `user_id` int(11) NOT NULL,
  `group_id` bigint(16) NOT NULL,
  `start` int(12) NOT NULL,
  `end` int(12) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_BOOK_COMMUNITY_TO_SECTION`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_COMMUNITY_TO_SECTION` (
  `community_id` bigint(16) NOT NULL,
  `section_id` mediumint(9) NOT NULL,
  PRIMARY KEY (`community_id`,`section_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_BOOK_COMMUNITY_VISIBILITY`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_COMMUNITY_VISIBILITY` (
  `user_id` int(11) NOT NULL,
  `community_id` bigint(16) NOT NULL,
  `visibility_id` bigint(16) NOT NULL,
  PRIMARY KEY (`user_id`,`community_id`,`visibility_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_BOOK_GROUP`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_GROUP` (
  `group_id` bigint(16) NOT NULL,
  `name` varchar(40) DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_BOOK_GROUP_CONTENT_GROUP`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_GROUP_CONTENT_GROUP` (
  `group_id_parent` bigint(16) NOT NULL,
  `group_id` bigint(16) NOT NULL,
  PRIMARY KEY (`group_id_parent`,`group_id`),
  KEY `group_id_parent` (`group_id`,`group_id_parent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_BOOK_GROUP_CONTENT_GROUP_INDEX`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_GROUP_CONTENT_GROUP_INDEX` (
  `group_id_parent` bigint(16) NOT NULL,
  `group_id` bigint(16) NOT NULL,
  PRIMARY KEY (`group_id_parent`,`group_id`),
  KEY `group_id_parent` (`group_id`,`group_id_parent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_BOOK_GROUP_CONTENT_USER`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_GROUP_CONTENT_USER` (
  `group_id` bigint(16) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`group_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_BOOK_GROUP_SEARCH`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_GROUP_SEARCH` (
  `id` bigint(16) NOT NULL,
  `string` varchar(20) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `owner_id` int(12) DEFAULT NULL,
  PRIMARY KEY (`string`,`type`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_BOOK_INVITATION`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_INVITATION` (
  `mail` varchar(255) NOT NULL,
  `from` int(11) NOT NULL,
  `time` int(12) NOT NULL,
  `recall` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_BOOK_KEEPINFORM`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_KEEPINFORM` (
  `email` varchar(20) NOT NULL,
  `date` bigint(12) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
  `moderator_can_add_community` tinyint(1) NOT NULL DEFAULT '1',
  `admin_can_add_community` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`section_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=322 ;

DROP TABLE IF EXISTS `OpenM_BOOK_SIGNAL`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_SIGNAL` (
  `type` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `message` varchar(255) NOT NULL,
  `time` int(12) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_BOOK_SIGNAL_GROUP`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_SIGNAL_GROUP` (
  `group_id` bigint(16) NOT NULL,
  `signaled_by` int(11) NOT NULL,
  `time` int(12) NOT NULL,
  `message` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_BOOK_SIGNAL_USER`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_SIGNAL_USER` (
  `user_id` int(11) NOT NULL,
  `signaled_by` int(11) NOT NULL,
  `time` int(12) NOT NULL,
  `message` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_BOOK_SIGNAL_USER_IN_GROUP`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_SIGNAL_USER_IN_GROUP` (
  `user_id` int(11) NOT NULL,
  `signaled_by` int(11) NOT NULL,
  `group_id` bigint(16) NOT NULL,
  `time` int(12) NOT NULL,
  `message` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_BOOK_USER`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_USER` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(100) NOT NULL COMMENT 'id généré par OpenM_ID',
  `creation_time` int(12) NOT NULL,
  `update_time` int(12) NOT NULL,
  `personal_groups` int(16) NOT NULL,
  `first_name` varchar(30) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `photo` bigint(16) DEFAULT NULL,
  `birthday` int(11) NOT NULL,
  `birthday_displayed` tinyint(1) NOT NULL DEFAULT '0',
  `birthday_year_displayed` tinyint(1) NOT NULL DEFAULT '0',
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `mail` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_id_Unique` (`uid`),
  KEY `first_name_last_name` (`first_name`,`last_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=73 ;

DROP TABLE IF EXISTS `OpenM_BOOK_USER_PROPERTY`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_USER_PROPERTY` (
  `property_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`property_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

DROP TABLE IF EXISTS `OpenM_BOOK_USER_PROPERTY_VALUE`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_USER_PROPERTY_VALUE` (
  `value_id` bigint(16) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `value` text,
  PRIMARY KEY (`value_id`),
  KEY `property_id` (`property_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_BOOK_USER_PROPERTY_VALUE_VISIBILITY`;
CREATE TABLE IF NOT EXISTS `OpenM_BOOK_USER_PROPERTY_VALUE_VISIBILITY` (
  `value_id` bigint(16) NOT NULL,
  `group_id` bigint(16) NOT NULL,
  PRIMARY KEY (`value_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;