-- MySQL dump 10.11
--
-- Host: localhost    Database: fotavia
-- ------------------------------------------------------
-- Server version	5.0.51b-community-nt

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `blocked_ips`
--

DROP TABLE IF EXISTS `blocked_ips`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `blocked_ips` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `comments` (
  `comment_id` int(10) unsigned NOT NULL auto_increment,
  `photo_id` int(10) unsigned NOT NULL,
  `owner_id` int(10) unsigned NOT NULL,
  `stamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `body` text NOT NULL,
  PRIMARY KEY  (`comment_id`),
  KEY `photo_id` (`photo_id`),
  KEY `owner_id` (`owner_id`),
  KEY `stamp` (`stamp`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `email_optouts`
--

DROP TABLE IF EXISTS `email_optouts`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `email_optouts` (
  `user_id` int(10) unsigned NOT NULL,
  `email_type` varchar(30) NOT NULL,
  PRIMARY KEY  (`user_id`,`email_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `feedback_form`
--

DROP TABLE IF EXISTS `feedback_form`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `feedback_form` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `logged_in_userid` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `msg` text NOT NULL,
  `admin_comment` text,
  `is_reviewed` tinyint(1) NOT NULL default '0',
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `follow_history`
--

DROP TABLE IF EXISTS `follow_history`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `follow_history` (
  `user_id` int(10) unsigned NOT NULL,
  `leader_user_id` int(10) unsigned NOT NULL,
  `is_follow` tinyint(1) unsigned NOT NULL,
  `stamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  KEY `user_id` (`user_id`),
  KEY `stamp` (`stamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `follows`
--

DROP TABLE IF EXISTS `follows`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `follows` (
  `follower_user_id` int(10) unsigned NOT NULL,
  `leader_user_id` int(10) unsigned NOT NULL,
  KEY `follower_user_id` (`follower_user_id`),
  KEY `leader_user_id` (`leader_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `photos`
--

DROP TABLE IF EXISTS `photos`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `photos` (
  `photo_id` int(10) unsigned NOT NULL auto_increment,
  `owner_id` int(10) unsigned NOT NULL,
  `user_date` date NOT NULL,
  `title` varchar(100) NOT NULL,
  `text` text NOT NULL,
  `hash` char(32) NOT NULL,
  `ext` varchar(4) NOT NULL,
  `page_url` varchar(150) NOT NULL default '',
  `email_sent` tinyint(1) NOT NULL default '0',
  `fb_published` tinyint(1) NOT NULL default '0',
  `tw_published` tinyint(1) unsigned NOT NULL default '0',
  `num_views` int(10) unsigned NOT NULL default '0',
  `stamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `status` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`photo_id`),
  KEY `stamp` (`stamp`),
  KEY `user_date` (`user_date`),
  KEY `status` (`status`),
  KEY `owner_id` (`owner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `temp_users`
--

DROP TABLE IF EXISTS `temp_users`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `temp_users` (
  `confirm_id` varchar(32) NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `is_confirmed` tinyint(1) unsigned NOT NULL default '0',
  `signup_stamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`confirm_id`),
  KEY `is_confirmed` (`is_confirmed`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `user_prefs`
--

DROP TABLE IF EXISTS `user_prefs`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `user_prefs` (
  `user_id` int(10) unsigned NOT NULL,
  `pref_name` varchar(15) NOT NULL,
  `pref_val` varchar(255) NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(25) NOT NULL,
  `password` char(40) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `bio` varchar(255) NOT NULL default '',
  `website` varchar(150) NOT NULL default '',
  `location` varchar(100) NOT NULL default '',
  `secret` char(32) NOT NULL,
  `tz_offset` int(11) NOT NULL default '0',
  `client_width` smallint(5) unsigned NOT NULL default '0',
  `client_height` smallint(5) unsigned NOT NULL default '0',
  `headshot_ext` char(3) default NULL,
  `signup_stamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `last_login_stamp` timestamp NULL default NULL,
  `sent_first_reminder` tinyint(1) unsigned NOT NULL default '0',
  `sent_inactive_reminder` tinyint(1) unsigned NOT NULL default '0',
  `status` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-08-30 17:31:13
