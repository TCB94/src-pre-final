-- MySQL dump 10.11
--
-- Host: scratchdb.media.mit.edu    Database: tbgforums
-- ------------------------------------------------------
-- Server version	5.0.45-log

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
-- Table structure for table `heartbeat`
--

DROP TABLE IF EXISTS `heartbeat`;
CREATE TABLE `heartbeat` (
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `heartbeat`
--

LOCK TABLES `heartbeat` WRITE;
/*!40000 ALTER TABLE `heartbeat` DISABLE KEYS */;
/*!40000 ALTER TABLE `heartbeat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_bans`
--

DROP TABLE IF EXISTS `punbb_bans`;
CREATE TABLE `punbb_bans` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(200) default NULL,
  `ip` varchar(255) default NULL,
  `email` varchar(50) default NULL,
  `message` varchar(255) default NULL,
  `expire` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_bans`
--

LOCK TABLES `punbb_bans` WRITE;
/*!40000 ALTER TABLE `punbb_bans` DISABLE KEYS */;
/*!40000 ALTER TABLE `punbb_bans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_categories`
--

DROP TABLE IF EXISTS `punbb_categories`;
CREATE TABLE `punbb_categories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `cat_name` varchar(80) NOT NULL default 'New Category',
  `disp_position` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_categories`
--

LOCK TABLES `punbb_categories` WRITE;
/*!40000 ALTER TABLE `punbb_categories` DISABLE KEYS */;
INSERT INTO `punbb_categories` VALUES (7,'Text Based Games',0);
/*!40000 ALTER TABLE `punbb_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_censoring`
--

DROP TABLE IF EXISTS `punbb_censoring`;
CREATE TABLE `punbb_censoring` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `search_for` varchar(60) NOT NULL default '',
  `replace_with` varchar(60) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_censoring`
--

LOCK TABLES `punbb_censoring` WRITE;
/*!40000 ALTER TABLE `punbb_censoring` DISABLE KEYS */;
INSERT INTO `punbb_censoring` VALUES (1,'fuck','*'),(2,'sex','*'),(3,'asshole','*'),(4,'whore','*'),(5,'suck','*'),(6,'viagra','*'),(7,'cialis','*'),(8,'ambien','*'),(9,'shit','*'),(10,'bastard','*'),(11,'omfg','*'),(12,'bitch','*'),(14,'slut','*'),(15,'damn','*'),(16,'rape','*'),(17,'crap','*');
/*!40000 ALTER TABLE `punbb_censoring` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_config`
--

DROP TABLE IF EXISTS `punbb_config`;
CREATE TABLE `punbb_config` (
  `conf_name` varchar(255) NOT NULL default '',
  `conf_value` text,
  PRIMARY KEY  (`conf_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_config`
--

LOCK TABLES `punbb_config` WRITE;
/*!40000 ALTER TABLE `punbb_config` DISABLE KEYS */;
INSERT INTO `punbb_config` VALUES ('o_additional_navlinks',''),('o_admin_email','help@scratch.mit.edu'),('o_announcement','0'),('o_announcement_message','Welcome to our forums.'),('o_avatars','0'),('o_avatars_dir','img/avatars'),('o_avatars_height','90'),('o_avatars_size','60000'),('o_avatars_width','90'),('o_base_url','http://scratch.mit.edu/tbgforums'),('o_board_desc','Discuss Text Based Games!'),('o_board_title','Text Based Games Forums'),('o_censoring','1'),('o_cur_version','1.2.14'),('o_date_format','Y-m-d'),('o_default_lang','English'),('o_default_style','scratchr'),('o_default_user_group','4'),('o_disp_posts_default','25'),('o_disp_topics_default','30'),('o_gzip','0'),('o_indent_num_spaces','4'),('o_mailing_list','caution@scratch.mit.edu'),('o_maintenance','0'),('o_maintenance_message','The forums are temporarily down for maintenance. Please try again in a few minutes.<br />\n<br />\n/Administrator'),('o_make_links','1'),('o_quickjump','1'),('o_quickpost','1'),('o_ranks','0'),('o_redirect_delay','0'),('o_regs_allow','1'),('o_regs_report','0'),('o_regs_verify','0'),('o_report_method','2'),('o_rules','0'),('o_rules_message','Be nice.'),('o_search_all_forums','1'),('o_server_timezone','-5'),('o_show_dot','0'),('o_show_post_count','1'),('o_show_user_info','1'),('o_show_version','0'),('o_smilies','1'),('o_smilies_sig','1'),('o_smtp_host',NULL),('o_smtp_pass',NULL),('o_smtp_user',NULL),('o_subscriptions','0'),('o_timeout_online','300'),('o_timeout_visit','600'),('o_time_format','H:i:s'),('o_topic_review','15'),('o_users_online','1'),('o_webmaster_email','caution@scratch.mit.edu'),('p_allow_banned_email','1'),('p_allow_dupe_email','0'),('p_force_guest_email','1'),('p_message_all_caps','0'),('p_message_bbcode','1'),('p_message_img_tag','1'),('p_mod_ban_users','1'),('p_mod_change_passwords','0'),('p_mod_edit_users','1'),('p_mod_rename_users','0'),('p_sig_all_caps','0'),('p_sig_bbcode','1'),('p_sig_img_tag','1'),('p_sig_length','400'),('p_sig_lines','2'),('p_subject_all_caps','0');
/*!40000 ALTER TABLE `punbb_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_digest_subscribed_forums`
--

DROP TABLE IF EXISTS `punbb_digest_subscribed_forums`;
CREATE TABLE `punbb_digest_subscribed_forums` (
  `user_id` int(10) NOT NULL default '0',
  `forum_id` int(10) NOT NULL default '0',
  UNIQUE KEY `user_id` (`user_id`,`forum_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_digest_subscribed_forums`
--

LOCK TABLES `punbb_digest_subscribed_forums` WRITE;
/*!40000 ALTER TABLE `punbb_digest_subscribed_forums` DISABLE KEYS */;
/*!40000 ALTER TABLE `punbb_digest_subscribed_forums` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_digest_subscriptions`
--

DROP TABLE IF EXISTS `punbb_digest_subscriptions`;
CREATE TABLE `punbb_digest_subscriptions` (
  `user_id` int(10) NOT NULL default '0',
  `digest_type` enum('DAY','WEEK') NOT NULL default 'DAY',
  `show_text` enum('YES','NO') NOT NULL default 'YES',
  `show_mine` enum('YES','NO') NOT NULL default 'YES',
  `new_only` enum('TRUE','FALSE') NOT NULL default 'TRUE',
  `send_on_no_messages` enum('YES','NO') NOT NULL default 'NO',
  `text_length` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_digest_subscriptions`
--

LOCK TABLES `punbb_digest_subscriptions` WRITE;
/*!40000 ALTER TABLE `punbb_digest_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `punbb_digest_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_forum_perms`
--

DROP TABLE IF EXISTS `punbb_forum_perms`;
CREATE TABLE `punbb_forum_perms` (
  `group_id` int(10) NOT NULL default '0',
  `forum_id` int(10) NOT NULL default '0',
  `read_forum` tinyint(1) NOT NULL default '1',
  `post_replies` tinyint(1) NOT NULL default '1',
  `post_topics` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`group_id`,`forum_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_forum_perms`
--

LOCK TABLES `punbb_forum_perms` WRITE;
/*!40000 ALTER TABLE `punbb_forum_perms` DISABLE KEYS */;
INSERT INTO `punbb_forum_perms` VALUES (2,46,1,0,0),(4,46,1,0,0),(5,46,1,0,0);
/*!40000 ALTER TABLE `punbb_forum_perms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_forums`
--

DROP TABLE IF EXISTS `punbb_forums`;
CREATE TABLE `punbb_forums` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `forum_name` varchar(80) NOT NULL default 'New forum',
  `forum_desc` text,
  `redirect_url` varchar(100) default NULL,
  `moderators` text,
  `num_topics` mediumint(8) unsigned NOT NULL default '0',
  `num_posts` mediumint(8) unsigned NOT NULL default '0',
  `last_post` int(10) unsigned default NULL,
  `last_post_id` int(10) unsigned default NULL,
  `last_poster` varchar(200) default NULL,
  `sort_by` tinyint(1) NOT NULL default '0',
  `disp_position` int(10) NOT NULL default '0',
  `cat_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_forums`
--

LOCK TABLES `punbb_forums` WRITE;
/*!40000 ALTER TABLE `punbb_forums` DISABLE KEYS */;
INSERT INTO `punbb_forums` VALUES (46,'Text Based Games','Also known as Forum Games. This is an experimental category intended to contain this kind of threads to a particular section.',NULL,NULL,0,0,NULL,NULL,NULL,0,0,7);
/*!40000 ALTER TABLE `punbb_forums` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_groups`
--

DROP TABLE IF EXISTS `punbb_groups`;
CREATE TABLE `punbb_groups` (
  `g_id` int(10) unsigned NOT NULL auto_increment,
  `g_title` varchar(50) NOT NULL default '',
  `g_user_title` varchar(50) default NULL,
  `g_read_board` tinyint(1) NOT NULL default '1',
  `g_post_replies` tinyint(1) NOT NULL default '1',
  `g_post_topics` tinyint(1) NOT NULL default '1',
  `g_post_polls` tinyint(1) NOT NULL default '1',
  `g_edit_posts` tinyint(1) NOT NULL default '1',
  `g_delete_posts` tinyint(1) NOT NULL default '1',
  `g_delete_topics` tinyint(1) NOT NULL default '1',
  `g_set_title` tinyint(1) NOT NULL default '1',
  `g_search` tinyint(1) NOT NULL default '1',
  `g_search_users` tinyint(1) NOT NULL default '1',
  `g_edit_subjects_interval` smallint(6) NOT NULL default '300',
  `g_post_flood` smallint(6) NOT NULL default '30',
  `g_search_flood` smallint(6) NOT NULL default '30',
  PRIMARY KEY  (`g_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_groups`
--

LOCK TABLES `punbb_groups` WRITE;
/*!40000 ALTER TABLE `punbb_groups` DISABLE KEYS */;
INSERT INTO `punbb_groups` VALUES (1,'Administrators','Scratch Team',1,1,1,1,1,1,1,1,1,1,0,0,0),(2,'Moderators','Forum Moderator',1,1,1,1,1,1,1,1,1,1,0,0,0),(3,'Guest',NULL,1,0,0,0,0,0,0,0,1,1,0,0,0),(4,'Members',NULL,1,1,1,1,1,1,1,0,1,1,300,60,30),(5,'Scratch Users',NULL,1,1,1,1,1,1,1,0,1,1,300,60,30);
/*!40000 ALTER TABLE `punbb_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_online`
--

DROP TABLE IF EXISTS `punbb_online`;
CREATE TABLE `punbb_online` (
  `user_id` int(10) unsigned NOT NULL default '1',
  `ident` varchar(200) NOT NULL default '',
  `logged` int(10) unsigned NOT NULL default '0',
  `idle` tinyint(1) NOT NULL default '0',
  KEY `punbb_online_user_id_idx` (`user_id`)
) ENGINE=MEMORY DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_online`
--

LOCK TABLES `punbb_online` WRITE;
/*!40000 ALTER TABLE `punbb_online` DISABLE KEYS */;
/*!40000 ALTER TABLE `punbb_online` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_posts`
--

DROP TABLE IF EXISTS `punbb_posts`;
CREATE TABLE `punbb_posts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `poster` varchar(200) NOT NULL default '',
  `poster_id` int(10) unsigned NOT NULL default '1',
  `poster_ip` varchar(15) default NULL,
  `poster_email` varchar(50) default NULL,
  `message` text,
  `hide_smilies` tinyint(1) NOT NULL default '0',
  `posted` int(10) unsigned NOT NULL default '0',
  `edited` int(10) unsigned default NULL,
  `edited_by` varchar(200) default NULL,
  `topic_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `punbb_posts_topic_id_idx` (`topic_id`),
  KEY `punbb_posts_multi_idx` (`poster_id`,`topic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_posts`
--

LOCK TABLES `punbb_posts` WRITE;
/*!40000 ALTER TABLE `punbb_posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `punbb_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_ranks`
--

DROP TABLE IF EXISTS `punbb_ranks`;
CREATE TABLE `punbb_ranks` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `rank` varchar(50) NOT NULL default '',
  `min_posts` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_ranks`
--

LOCK TABLES `punbb_ranks` WRITE;
/*!40000 ALTER TABLE `punbb_ranks` DISABLE KEYS */;
INSERT INTO `punbb_ranks` VALUES (1,'Member',0);
/*!40000 ALTER TABLE `punbb_ranks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_reports`
--

DROP TABLE IF EXISTS `punbb_reports`;
CREATE TABLE `punbb_reports` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `post_id` int(10) unsigned NOT NULL default '0',
  `topic_id` int(10) unsigned NOT NULL default '0',
  `forum_id` int(10) unsigned NOT NULL default '0',
  `reported_by` int(10) unsigned NOT NULL default '0',
  `created` int(10) unsigned NOT NULL default '0',
  `message` text,
  `zapped` int(10) unsigned default NULL,
  `zapped_by` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `punbb_reports_zapped_idx` (`zapped`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_reports`
--

LOCK TABLES `punbb_reports` WRITE;
/*!40000 ALTER TABLE `punbb_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `punbb_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_search_cache`
--

DROP TABLE IF EXISTS `punbb_search_cache`;
CREATE TABLE `punbb_search_cache` (
  `id` int(10) unsigned NOT NULL default '0',
  `ident` varchar(200) NOT NULL default '',
  `search_data` text,
  PRIMARY KEY  (`id`),
  KEY `punbb_search_cache_ident_idx` (`ident`(8))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_search_cache`
--

LOCK TABLES `punbb_search_cache` WRITE;
/*!40000 ALTER TABLE `punbb_search_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `punbb_search_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_search_matches`
--

DROP TABLE IF EXISTS `punbb_search_matches`;
CREATE TABLE `punbb_search_matches` (
  `post_id` int(10) unsigned NOT NULL default '0',
  `word_id` mediumint(8) unsigned NOT NULL default '0',
  `subject_match` tinyint(1) NOT NULL default '0',
  KEY `punbb_search_matches_word_id_idx` (`word_id`),
  KEY `punbb_search_matches_post_id_idx` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_search_matches`
--

LOCK TABLES `punbb_search_matches` WRITE;
/*!40000 ALTER TABLE `punbb_search_matches` DISABLE KEYS */;
/*!40000 ALTER TABLE `punbb_search_matches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_search_words`
--

DROP TABLE IF EXISTS `punbb_search_words`;
CREATE TABLE `punbb_search_words` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `word` varchar(20) character set latin1 collate latin1_bin NOT NULL default '',
  PRIMARY KEY  (`word`),
  KEY `punbb_search_words_id_idx` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_search_words`
--

LOCK TABLES `punbb_search_words` WRITE;
/*!40000 ALTER TABLE `punbb_search_words` DISABLE KEYS */;
/*!40000 ALTER TABLE `punbb_search_words` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_subscriptions`
--

DROP TABLE IF EXISTS `punbb_subscriptions`;
CREATE TABLE `punbb_subscriptions` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `topic_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`topic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_subscriptions`
--

LOCK TABLES `punbb_subscriptions` WRITE;
/*!40000 ALTER TABLE `punbb_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `punbb_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_topics`
--

DROP TABLE IF EXISTS `punbb_topics`;
CREATE TABLE `punbb_topics` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `poster` varchar(200) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `posted` int(10) unsigned NOT NULL default '0',
  `last_post` int(10) unsigned NOT NULL default '0',
  `last_post_id` int(10) unsigned NOT NULL default '0',
  `last_poster` varchar(200) default NULL,
  `num_views` mediumint(8) unsigned NOT NULL default '0',
  `num_replies` mediumint(8) unsigned NOT NULL default '0',
  `closed` tinyint(1) NOT NULL default '0',
  `sticky` tinyint(1) NOT NULL default '0',
  `moved_to` int(10) unsigned default NULL,
  `forum_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `punbb_topics_forum_id_idx` (`forum_id`),
  KEY `punbb_topics_moved_to_idx` (`moved_to`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_topics`
--

LOCK TABLES `punbb_topics` WRITE;
/*!40000 ALTER TABLE `punbb_topics` DISABLE KEYS */;
/*!40000 ALTER TABLE `punbb_topics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `punbb_users`
--

DROP TABLE IF EXISTS `punbb_users`;
CREATE TABLE `punbb_users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `group_id` int(10) unsigned NOT NULL default '4',
  `username` varchar(200) NOT NULL default '',
  `password` varchar(40) NOT NULL default '',
  `email` varchar(50) NOT NULL default '',
  `title` varchar(50) default NULL,
  `realname` varchar(40) default NULL,
  `url` varchar(100) default NULL,
  `jabber` varchar(75) default NULL,
  `icq` varchar(12) default NULL,
  `msn` varchar(50) default NULL,
  `aim` varchar(30) default NULL,
  `yahoo` varchar(30) default NULL,
  `location` varchar(30) default NULL,
  `use_avatar` tinyint(1) NOT NULL default '0',
  `signature` text,
  `disp_topics` tinyint(3) unsigned default NULL,
  `disp_posts` tinyint(3) unsigned default NULL,
  `email_setting` tinyint(1) NOT NULL default '1',
  `save_pass` tinyint(1) NOT NULL default '1',
  `notify_with_post` tinyint(1) NOT NULL default '0',
  `show_smilies` tinyint(1) NOT NULL default '1',
  `show_img` tinyint(1) NOT NULL default '1',
  `show_img_sig` tinyint(1) NOT NULL default '1',
  `show_avatars` tinyint(1) NOT NULL default '1',
  `show_sig` tinyint(1) NOT NULL default '1',
  `timezone` float NOT NULL default '0',
  `language` varchar(25) NOT NULL default 'English',
  `style` varchar(25) NOT NULL default 'Oxygen',
  `num_posts` int(10) unsigned NOT NULL default '0',
  `last_post` int(10) unsigned default NULL,
  `registered` int(10) unsigned NOT NULL default '0',
  `registration_ip` varchar(15) NOT NULL default '0.0.0.0',
  `last_visit` int(10) unsigned NOT NULL default '0',
  `admin_note` varchar(30) default NULL,
  `activate_string` varchar(50) default NULL,
  `activate_key` varchar(8) default NULL,
  PRIMARY KEY  (`id`),
  KEY `punbb_users_registered_idx` (`registered`),
  KEY `punbb_users_username_idx` (`username`(8))
) ENGINE=InnoDB AUTO_INCREMENT=66050 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `punbb_users`
--

LOCK TABLES `punbb_users` WRITE;
/*!40000 ALTER TABLE `punbb_users` DISABLE KEYS */;
INSERT INTO `punbb_users` VALUES (1,3,'Guest','Guest','Guest',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,1,1,0,1,1,1,1,1,0,'English','Oxygen',0,NULL,0,'0.0.0.0',0,NULL,NULL,NULL),(19,1,'andresmh','43527feedf301cf3880e49e7c851d60005670e15','andresmh@media.mit.edu','ScratchR','Andres Monroy-Hernandez','http://scratch.mit.edu/users/andresmh',NULL,NULL,NULL,NULL,NULL,'MIT Media Lab',0,'Andres Monroy-Hernandez\nScratch Team at the MIT Media Lab',NULL,NULL,1,1,0,1,1,1,0,1,-5,'English','scratchr',0,NULL,1255207267,'18.85.18.15',1255210025,NULL,NULL,NULL),(66049,1,'anupom98','97102f7ea108523c1c6c6e42161d9261ca680b64','anupom.syam@gmail.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,1,1,0,1,1,1,1,1,-5,'English','scratchr',0,NULL,1255207267,'119.30.35.27',1255285114,NULL,NULL,NULL);
/*!40000 ALTER TABLE `punbb_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-10-15  9:58:06
