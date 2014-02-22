-- MySQL dump 10.13  Distrib 5.5.30, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: bible
-- ------------------------------------------------------
-- Server version	5.5.30-1-log

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
-- Table structure for table `kar_dropbox_oauth_tokens`
--

DROP TABLE IF EXISTS `kar_dropbox_oauth_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kar_dropbox_oauth_tokens` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(10) unsigned NOT NULL,
  `token` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `userID` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kar_igenaptar`
--

DROP TABLE IF EXISTS `kar_igenaptar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kar_igenaptar` (
  `datum` date NOT NULL,
  `unnep` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `alunnep` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `mondat` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `olv` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `olvtext` mediumtext CHARACTER SET utf8,
  `zsolt` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `zsolttext` mediumtext CHARACTER SET utf8,
  `szent` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `szenttext` mediumtext CHARACTER SET utf8,
  `evang` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `evangtext` text CHARACTER SET utf8,
  PRIMARY KEY (`datum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kar_news`
--

DROP TABLE IF EXISTS `kar_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kar_news` (
  `id` int(10) NOT NULL,
  `date` date NOT NULL,
  `title` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `text` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `frontpage` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kar_stats_search`
--

DROP TABLE IF EXISTS `kar_stats_search`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kar_stats_search` (
  `texttosearch` varchar(200) CHARACTER SET utf8 NOT NULL,
  `reftrans` int(1) NOT NULL,
  `page` int(100) NOT NULL,
  `rows` int(100) NOT NULL,
  `resultcount` int(100) NOT NULL,
  `searchcount` int(100) NOT NULL,
  `searchtype` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `resultarray` longtext COLLATE utf8_unicode_ci NOT NULL,
  `resultupdated` datetime NOT NULL,
  KEY `Index 1` (`searchtype`,`reftrans`,`page`,`texttosearch`,`rows`),
  KEY `Index 2` (`texttosearch`,`reftrans`,`searchtype`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kar_stats_texttosearch`
--

DROP TABLE IF EXISTS `kar_stats_texttosearch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kar_stats_texttosearch` (
  `reftrans` int(1) NOT NULL,
  `texttosearch` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `notes` mediumtext COLLATE utf8_unicode_ci,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `result` int(10) NOT NULL,
  `session` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `tipp` mediumtext CHARACTER SET utf8,
  `original` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `referrer` varchar(300) CHARACTER SET utf8 DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kar_szinonimak`
--

DROP TABLE IF EXISTS `kar_szinonimak`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kar_szinonimak` (
  `szinonimak` mediumtext CHARACTER SET utf8 NOT NULL,
  `tipus` varchar(50) CHARACTER SET utf8 NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kar_tdbook`
--

DROP TABLE IF EXISTS `kar_tdbook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kar_tdbook` (
  `id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `trans` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `abbrev` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `url` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `countch` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `oldtest` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`trans`),
  UNIQUE KEY `Index 2` (`abbrev`,`trans`),
  KEY `Index 3` (`url`,`trans`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kar_tdtrans`
--

DROP TABLE IF EXISTS `kar_tdtrans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kar_tdtrans` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `abbrev` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `denom` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `lang` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `copyright` text CHARACTER SET utf8,
  `publisher` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `publisherurl` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kar_tdverse`
--

DROP TABLE IF EXISTS `kar_tdverse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kar_tdverse` (
  `did` int(10) unsigned NOT NULL COMMENT 'sorszam',
  `trans` tinyint(3) unsigned NOT NULL COMMENT 'forditas azonosito',
  `gepi` bigint(11) NOT NULL COMMENT 'gepi hivatkozas',
  `book` int(3) NOT NULL COMMENT 'gepi hivatkozas',
  `chapter` int(2) NOT NULL COMMENT 'gepi hivatkozas',
  `numv` varchar(4) COLLATE utf8_unicode_ci NOT NULL COMMENT 'gepi hivatkozas',
  `hiv` varchar(15) COLLATE utf8_unicode_ci NOT NULL COMMENT 'szep hivatkozas',
  `old` int(4) NOT NULL COMMENT 'oldalszam',
  `tip` int(4) NOT NULL COMMENT 'jelenseg tipus',
  `jelenseg` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'jelenseg leirasa',
  `verse` text COLLATE utf8_unicode_ci COMMENT 'verse',
  `versesimple` text COLLATE utf8_unicode_ci COMMENT 'egyszerusitett',
  `verseroot` text COLLATE utf8_unicode_ci COMMENT 'hunspell',
  `ido` varchar(50) CHARACTER SET ucs2 COLLATE ucs2_unicode_ci DEFAULT NULL COMMENT 'idopecset',
  UNIQUE KEY `Index 1` (`did`,`trans`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kar_vars`
--

DROP TABLE IF EXISTS `kar_vars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kar_vars` (
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_hungarian_ci NOT NULL,
  `value` text CHARACTER SET utf8 NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
