-- MySQL dump 10.13  Distrib 8.0.25, for Win64 (x86_64)
--
-- Host: 192.168.1.30    Database: homeserverportal
-- ------------------------------------------------------
-- Server version	8.0.25-0ubuntu0.20.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `section` varchar(45) NOT NULL,
  `category` varchar(45) DEFAULT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'title','general','application','Homeserver Portal'),
(2,'background','general','application','autobg'),
(3,'background_latitude','general','application',''),
(4,'background_longitude','general','application',''),
(5,'alert_timeout','general','application','5'),
(6,'items_per_page','general','application','10'),
(7,'base_uri','general','application',''),
(8,'phalcon_crypt_key','general','application',''),
(9,'demo_mode','general','application','0'),
(10,'version','general','application','1.4.0'),
(11,'enabled','general','redis','0'),
(12,'host','general','redis','localhost'),
(13,'port','general','redis','6379'),
(14,'auth','general','redis',''),
(15,'enabled','general','imageproxy','0'),
(16,'url','general','imageproxy',''),
(17,'what_is_my_browser_api_key','dashboard','speedtest',''),
(18,'what_is_my_browser_api_url','dashboard','speedtest','https://api.whatismybrowser.com/api/v2/'),
(19,'tmdb_api_url','dashboard','couchpotato','https://api.themoviedb.org/3/'),
(20,'tmdb_api_key','dashboard','couchpotato',''),
(21,'broadcast','dashboard','network',''),
(22,'check_device_states_interval','dashboard','dashboard','10'),
(23,'check_now_playing_interval','dashboard','dashboard','10'),
(24,'enabled','dashboard','phpsysinfo','0'),
(25,'url','dashboard','phpsysinfo',''),
(26,'username','dashboard','phpsysinfo',''),
(27,'password','dashboard','phpsysinfo',''),
(28,'enabled','dashboard','rcpu','0'),
(29,'url','dashboard','rcpu',''),
(30,'enabled','dashboard','transmission','0'),
(31,'username','dashboard','transmission',''),
(32,'password','dashboard','transmission',''),
(33,'url','dashboard','transmission',''),
(34,'update_interval','dashboard','transmission','10'),
(35,'enabled','dashboard','subsonic','0'),
(36,'url','dashboard','subsonic','h'),
(37,'username','dashboard','subsonic',''),
(38,'password','dashboard','subsonic',''),
(39,'enabled','dashboard','kodi','0'),
(40,'url','dashboard','kodi',''),
(41,'username','dashboard','kodi',''),
(42,'password','dashboard','kodi',''),
(43,'rotate_movies_interval','dashboard','kodi','30'),
(44,'rotate_episodes_interval','dashboard','kodi','30'),
(45,'rotate_albums_interval','dashboard','kodi','30'),
(46,'enabled','dashboard','sickrage','0'),
(47,'url','dashboard','sickrage',''),
(48,'api_key','dashboard','sickrage',''),
(49,'enabled','dashboard','couchpotato','0'),
(50,'url','dashboard','couchpotato',''),
(51,'api_key','dashboard','couchpotato',''),
(52,'rotate_interval','dashboard','couchpotato','30'),
(53,'enabled','dashboard','duo','0'),
(54,'ikey','dashboard','duo',''),
(55,'skey','dashboard','duo',''),
(56,'akey','dashboard','duo',''),
(57,'api_hostname','dashboard','duo',''),
(58,'enabled','dashboard','motion','0'),
(59,'url','dashboard','motion',''),
(60,'picture_path','dashboard','motion',''),
(61,'update_interval','dashboard','motion','30'),
(62,'enabled','dashboard','speedtest','0'),
(63,'test_order','dashboard','speedtest','IPDU'),
(64,'time_upload','dashboard','speedtest','10'),
(65,'time_download','dashboard','speedtest','10'),
(66,'get_isp_info','dashboard','speedtest','0'),
(67,'get_isp_distance','dashboard','speedtest','km'),
(68,'telemetry','dashboard','speedtest','full'),
(69,'ip_info_url','dashboard','speedtest','https://ipinfo.io/'),
(70,'ip_info_token','dashboard','speedtest',''),
(71,'enabled','dashboard','opcache','0'),
(72,'enabled','dashboard','pihole','0'),
(73,'url','dashboard','pihole',''),
(74,'enabled','dashboard','youless','0'),
(75,'url','dashboard','youless',''),
(76,'charts_url','dashboard','youless',''),
(77,'password','dashboard','youless',''),
(78,'update_interval','dashboard','youless','5'),
(79,'threshold_primary','dashboard','youless','250'),
(80,'threshold_warning','dashboard','youless','500'),
(81,'threshold_danger','dashboard','youless','1000'),
(82,'enabled','dashboard','snmp','0'),
(83,'update_interval','dashboard','snmp','5'),
(84,'enabled','dashboard','verisure','0'),
(85,'username','dashboard','verisure',''),
(86,'password','dashboard','verisure',''),
(87,'update_interval','dashboard','verisure','180'),
(88,'url','dashboard','verisure','https://mypages.verisure.com/login'),
(89,'securitycode','dashboard','verisure',''),
(90,'enabled','dashboard','roborock','0'),
(91,'ip','dashboard','roborock',''),
(92,'token','dashboard','roborock',''),
(93,'update_interval','dashboard','roborock','30'),
(94,'enabled','dashboard','jellyfin','0'),
(95,'url','dashboard','jellyfin',''),
(96,'token','dashboard','jellyfin',''),
(97,'userid','dashboard','jellyfin',''),
(98,'views','dashboard','jellyfin',''),
(99,'rotate_interval','dashboard','jellyfin','30'),
(100,'enabled','dashboard','pulseway','0'),
(101,'url','dashboard','pulseway','https://api.pulseway.com/v2/'),
(102,'username','dashboard','pulseway',''),
(103,'password','dashboard','pulseway',''),
(104,'systems','dashboard','pulseway',''),
(105,'update_interval','dashboard','pulseway','30');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-06-09  8:36:59
