-- MySQL dump 10.16  Distrib 10.2.2-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: pw2
-- ------------------------------------------------------
-- Server version	10.2.2-MariaDB-1~xenial

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
-- Table structure for table `piwik_log_media`
--

DROP TABLE IF EXISTS `piwik_log_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `piwik_log_media` (
  `idvisitor` binary(8) NOT NULL,
  `idvisit` bigint(20) unsigned NOT NULL,
  `idsite` int(11) unsigned NOT NULL,
  `idview` varchar(16) NOT NULL,
  `player_name` varchar(20) NOT NULL,
  `media_type` tinyint(1) NOT NULL,
  `resolution` varchar(20) DEFAULT '',
  `fullscreen` tinyint(1) unsigned NOT NULL,
  `media_title` varchar(150) DEFAULT '',
  `resource` varchar(300) NOT NULL,
  `server_time` datetime NOT NULL,
  `time_to_initial_play` int(11) unsigned DEFAULT NULL,
  `watched_time` bigint(20) unsigned DEFAULT 0,
  `media_progress` int(11) unsigned DEFAULT 0,
  `media_length` int(11) unsigned DEFAULT 0,
  PRIMARY KEY (`idvisit`,`idview`),
  KEY `idsite` (`idsite`,`media_type`,`server_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-02-14 14:09:13
