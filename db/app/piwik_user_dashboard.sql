-- MySQL dump 10.15  Distrib 10.0.29-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: localhost
-- ------------------------------------------------------
-- Server version	10.0.29-MariaDB-0ubuntu0.16.04.1

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
-- Table structure for table `piwik_user_dashboard`
--

DROP TABLE IF EXISTS `piwik_user_dashboard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `piwik_user_dashboard` (
  `login` varchar(100) NOT NULL,
  `iddashboard` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `layout` text NOT NULL,
  PRIMARY KEY (`login`,`iddashboard`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `piwik_user_dashboard`
--

LOCK TABLES `piwik_user_dashboard` WRITE;
/*!40000 ALTER TABLE `piwik_user_dashboard` DISABLE KEYS */;
INSERT INTO `piwik_user_dashboard` VALUES ('admin',1,'Dashboard','{\"config\":{\"layout\":\"33-33-33\"},\"columns\":[[{\"uniqueId\":\"widgetLivegetSimpleLastVisitCount\",\"parameters\":{\"module\":\"Live\",\"action\":\"getSimpleLastVisitCount\",\"widget\":1},\"isHidden\":false},{\"uniqueId\":\"widgetDevicesDetectiongetOsFamilies\",\"parameters\":{\"module\":\"DevicesDetection\",\"action\":\"getOsFamilies\",\"widget\":1,\"isFooterExpandedInDashboard\":true,\"viewDataTable\":\"graphPie\"},\"isHidden\":false},{\"uniqueId\":\"widgetDevicesDetectiongetType\",\"parameters\":{\"module\":\"DevicesDetection\",\"action\":\"getType\",\"widget\":1,\"isFooterExpandedInDashboard\":true,\"viewDataTable\":\"graphPie\"},\"isHidden\":false},{\"uniqueId\":\"widgetDevicesDetectiongetBrowsers\",\"parameters\":{\"module\":\"DevicesDetection\",\"action\":\"getBrowsers\",\"widget\":1,\"isFooterExpandedInDashboard\":true,\"viewDataTable\":\"graphPie\"},\"isHidden\":false}],[{\"uniqueId\":\"widgetQoSwidRealtimeAvgDcolumnsArray\",\"parameters\":{\"module\":\"QoS\",\"action\":\"widRealtimeAvgD\",\"columns\":[\"avg_speed\"],\"widget\":1},\"isHidden\":false},{\"uniqueId\":\"widgetUserCountryMaprealtimeMap\",\"parameters\":{\"module\":\"UserCountryMap\",\"action\":\"realtimeMap\",\"widget\":1},\"isHidden\":false},{\"uniqueId\":\"widgetVisitTimegetVisitInformationPerServerTime\",\"parameters\":{\"module\":\"VisitTime\",\"action\":\"getVisitInformationPerServerTime\",\"widget\":1},\"isHidden\":false},{\"uniqueId\":\"widgetProvidergetProvider\",\"parameters\":{\"module\":\"Provider\",\"action\":\"getProvider\",\"widget\":1,\"isFooterExpandedInDashboard\":true,\"viewDataTable\":\"graphPie\"},\"isHidden\":false},{\"uniqueId\":\"widgetActionsgetPageUrls\",\"parameters\":{\"module\":\"Actions\",\"action\":\"getPageUrls\",\"widget\":1},\"isHidden\":false}],[{\"uniqueId\":\"widgetQoSwidRealtimeThrucolumnsArray\",\"parameters\":{\"module\":\"QoS\",\"action\":\"widRealtimeThru\",\"columns\":[\"traffic_ps\"],\"widget\":1},\"isHidden\":false},{\"uniqueId\":\"widgetUserCountryMapvisitorMap\",\"parameters\":{\"module\":\"UserCountryMap\",\"action\":\"visitorMap\",\"widget\":1},\"isHidden\":false},{\"uniqueId\":\"widgetUserCountrygetCountry\",\"parameters\":{\"module\":\"UserCountry\",\"action\":\"getCountry\",\"widget\":1},\"isHidden\":false},{\"uniqueId\":\"widgetUserCountrygetRegion\",\"parameters\":{\"module\":\"UserCountry\",\"action\":\"getRegion\",\"widget\":1},\"isHidden\":false},{\"uniqueId\":\"widgetReferrersgetAll\",\"parameters\":{\"module\":\"Referrers\",\"action\":\"getAll\",\"widget\":1,\"isFooterExpandedInDashboard\":true,\"filter_limit\":5},\"isHidden\":false}]]}');
/*!40000 ALTER TABLE `piwik_user_dashboard` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-03-01  8:54:15
