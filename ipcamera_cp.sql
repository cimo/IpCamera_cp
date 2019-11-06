-- MySQL dump 10.13  Distrib 5.7.17, for macos10.12 (x86_64)
--
-- Host: 127.0.0.1    Database: ipcamera_cp
-- ------------------------------------------------------
-- Server version	5.7.27-0ubuntu0.16.04.1

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
-- Table structure for table `ipCamera_device`
--

DROP TABLE IF EXISTS `ipCamera_device`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ipCamera_device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `user_id` varchar(255) NOT NULL DEFAULT '1,',
  `host_video` varchar(255) NOT NULL DEFAULT '',
  `host_image` varchar(255) NOT NULL DEFAULT '',
  `username` varchar(20) NOT NULL DEFAULT '',
  `password` blob,
  `detection_sensitivity` decimal(4,3) NOT NULL DEFAULT '0.010',
  `detection_active` tinyint(1) NOT NULL DEFAULT '0',
  `detection_pid` varchar(4) DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ipCamera_device`
--

LOCK TABLES `ipCamera_device` WRITE;
/*!40000 ALTER TABLE `ipCamera_device` DISABLE KEYS */;
INSERT INTO `ipCamera_device` VALUES (1,'Cosenza - Salone','1,','http://home-cs.ddns.net:1000/media/?action=stream','http://home-cs.ddns.net:1000/media/?action=snapshot','admin','l!\�y\�F�c���N)�	',0.010,0,'0',1);
/*!40000 ALTER TABLE `ipCamera_device` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `language`
--

DROP TABLE IF EXISTS `language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `language` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `date` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y-m-d',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `language`
--

LOCK TABLES `language` WRITE;
/*!40000 ALTER TABLE `language` DISABLE KEYS */;
INSERT INTO `language` VALUES (1,'en','Y-m-d',1),(2,'jp','Y-m-d',1),(3,'it','d-m-Y',1);
/*!40000 ALTER TABLE `language` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `microservice_api`
--

DROP TABLE IF EXISTS `microservice_api`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `microservice_api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `controller_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `microservice_api`
--

LOCK TABLES `microservice_api` WRITE;
/*!40000 ALTER TABLE `microservice_api` DISABLE KEYS */;
INSERT INTO `microservice_api` VALUES (1,'Basic','ApiBasic','Test api functionality',NULL,0);
/*!40000 ALTER TABLE `microservice_api` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `microservice_apiBasic`
--

DROP TABLE IF EXISTS `microservice_apiBasic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `microservice_apiBasic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `token_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ip` longtext COLLATE utf8_unicode_ci,
  `url_callback` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `database_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `database_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `database_username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `database_password` blob,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `slack_active` tinyint(1) NOT NULL DEFAULT '0',
  `line_active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `microservice_apiBasic`
--

LOCK TABLES `microservice_apiBasic` WRITE;
/*!40000 ALTER TABLE `microservice_apiBasic` DISABLE KEYS */;
/*!40000 ALTER TABLE `microservice_apiBasic` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `microservice_apiBasic_request`
--

DROP TABLE IF EXISTS `microservice_apiBasic_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `microservice_apiBasic_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `date` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `count` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `microservice_apiBasic_request`
--

LOCK TABLES `microservice_apiBasic_request` WRITE;
/*!40000 ALTER TABLE `microservice_apiBasic_request` DISABLE KEYS */;
/*!40000 ALTER TABLE `microservice_apiBasic_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `microservice_apiBasic_request_detail`
--

DROP TABLE IF EXISTS `microservice_apiBasic_request_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `microservice_apiBasic_request_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `date` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data` json NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `microservice_apiBasic_request_detail`
--

LOCK TABLES `microservice_apiBasic_request_detail` WRITE;
/*!40000 ALTER TABLE `microservice_apiBasic_request_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `microservice_apiBasic_request_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `microservice_cron`
--

DROP TABLE IF EXISTS `microservice_cron`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `microservice_cron` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `time` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `last_execution` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `microservice_cron`
--

LOCK TABLES `microservice_cron` WRITE;
/*!40000 ALTER TABLE `microservice_cron` DISABLE KEYS */;
INSERT INTO `microservice_cron` VALUES (2,'test_1','Ki8xICogKiAqICo=','bHMgLWwgL2hvbWU=',0,'2019-10-30 10:35:01');
/*!40000 ALTER TABLE `microservice_cron` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `microservice_deploy`
--

DROP TABLE IF EXISTS `microservice_deploy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `microservice_deploy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `system_user` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ssh_username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ssh_password` blob,
  `key_public` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `key_private` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `key_private_password` blob,
  `ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `git_user_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `git_user_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `git_clone_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `git_clone_url_username` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `git_clone_url_password` blob,
  `git_clone_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_git_script` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_web_script` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `root_web_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `command` longtext COLLATE utf8_unicode_ci,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `microservice_deploy`
--

LOCK TABLES `microservice_deploy` WRITE;
/*!40000 ALTER TABLE `microservice_deploy` DISABLE KEYS */;
/*!40000 ALTER TABLE `microservice_deploy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `microservice_unit_test`
--

DROP TABLE IF EXISTS `microservice_unit_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `microservice_unit_test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `origin` longtext COLLATE utf8_unicode_ci NOT NULL,
  `code` longtext COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `microservice_unit_test`
--

LOCK TABLES `microservice_unit_test` WRITE;
/*!40000 ALTER TABLE `microservice_unit_test` DISABLE KEYS */;
INSERT INTO `microservice_unit_test` VALUES (1,'test_design','https://lsv2.machine.local','dmFyIGV4cGVjdGVkVmFsdWUgPSAwOw0KDQp2YXIgdGFncyA9IG5ldyBBcnJheSgpOw0KdGFncy5wdXNoKCIuY29sdW1uX2xlZnQiKTsNCg0KJC5lYWNoKHRhZ3MsIGZ1bmN0aW9uKGtleSwgdmFsdWUpIHsNCiAgICBhc3NlcnQub2soJCh2YWx1ZSkubGVuZ3RoID4gZXhwZWN0ZWRWYWx1ZSwgdmFsdWUgKyAiIGdyZWF0ZXIgdGhhbiAiICsgZXhwZWN0ZWRWYWx1ZSk7DQp9KTs=',0);
/*!40000 ALTER TABLE `microservice_unit_test` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module`
--

DROP TABLE IF EXISTS `module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'center',
  `position_tmp` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rank_in_column` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `controller_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module`
--

LOCK TABLES `module` WRITE;
/*!40000 ALTER TABLE `module` DISABLE KEYS */;
INSERT INTO `module` VALUES (1,'center','left',1,'Authentication','module_1','AuthenticationController::moduleAction',0),(2,'center',NULL,3,'Page','module_2','PageViewController::moduleAction',1),(3,'center','right',2,'Empty','module_3','EmptyController::moduleAction',0);
/*!40000 ALTER TABLE `module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page`
--

DROP TABLE IF EXISTS `page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `parent` int(11) DEFAULT NULL,
  `controller_action` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `role_user_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1,2,',
  `protected` tinyint(1) NOT NULL DEFAULT '0',
  `show_in_menu` tinyint(1) NOT NULL DEFAULT '1',
  `rank_in_menu` int(11) DEFAULT NULL,
  `comment` tinyint(1) NOT NULL DEFAULT '1',
  `only_parent` tinyint(1) NOT NULL DEFAULT '0',
  `only_link` tinyint(1) NOT NULL DEFAULT '0',
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  `user_create` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  `date_create` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_modify` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  `date_modify` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `meta_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_robots` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page`
--

LOCK TABLES `page` WRITE;
/*!40000 ALTER TABLE `page` DISABLE KEYS */;
INSERT INTO `page` VALUES (1,'myPage',NULL,'App\\Controller\\MyPage\\MyPageProfileController::renderAction','1,',1,0,1,0,0,0,'-','-','0000-00-00 00:00:00','cimo','2019-01-07 17:47:45','Personal page.','personal, page','noindex, nofollow'),(2,'home',NULL,'App\\Controller\\ControlPanel\\IpCameraController::renderAction','1,',1,1,2,1,0,0,'-','-','0000-00-00 00:00:00','cimo','2019-04-25 14:47:22','Website home page.','website, home, page','index, follow'),(3,'registration',NULL,'App\\Controller\\RegistrationController::renderAction','1,',0,0,3,1,0,0,'-','-','0000-00-00 00:00:00','cimo','2019-01-07 19:05:15','User registration page.','user, registration, page','index, follow'),(4,'recover_password',NULL,'App\\Controller\\RecoverPasswordController::renderAction','1,',0,0,4,1,0,0,'-','-','0000-00-00 00:00:00','cimo','2019-01-07 19:05:40','User recover password.','user, recover, password','index, follow'),(5,'search',NULL,'App\\Controller\\SearchController::renderAction','1,',0,0,5,1,0,0,'-','-','0000-00-00 00:00:00','cimo','2019-01-07 19:07:47','Website content search','website, content, search','noindex, nofollow');
/*!40000 ALTER TABLE `page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_argument`
--

DROP TABLE IF EXISTS `page_argument`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_argument` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `en` longtext COLLATE utf8_unicode_ci,
  `it` longtext COLLATE utf8_unicode_ci,
  `jp` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_argument`
--

LOCK TABLES `page_argument` WRITE;
/*!40000 ALTER TABLE `page_argument` DISABLE KEYS */;
INSERT INTO `page_argument` VALUES (1,'User personal page.','Argomento my page.','My pageのアーギュメント。'),(2,'This is a cms created with symfony framework.','Argomento home.','ホームのアーギュメント。'),(3,'Registration argument.','Argomento registrazione.','登録のアーギュメント。'),(4,'Recover password argument.','Argomento recupero password.','パスワードを回復のアーギュメント。'),(5,'Search argument.','Argomento cerca.','サーチのアーギュメント。');
/*!40000 ALTER TABLE `page_argument` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_comment`
--

DROP TABLE IF EXISTS `page_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL DEFAULT '0',
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `id_reply` int(11) DEFAULT NULL,
  `argument` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `date_create` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modify` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_comment`
--

LOCK TABLES `page_comment` WRITE;
/*!40000 ALTER TABLE `page_comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_menu_name`
--

DROP TABLE IF EXISTS `page_menu_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_menu_name` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `en` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  `it` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  `jp` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_menu_name`
--

LOCK TABLES `page_menu_name` WRITE;
/*!40000 ALTER TABLE `page_menu_name` DISABLE KEYS */;
INSERT INTO `page_menu_name` VALUES (1,'-','-','-'),(2,'Home','Home','ホーム'),(3,'-','-','-'),(4,'-','-','-'),(5,'-','-','-');
/*!40000 ALTER TABLE `page_menu_name` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_title`
--

DROP TABLE IF EXISTS `page_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_title` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `en` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `it` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `jp` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_title`
--

LOCK TABLES `page_title` WRITE;
/*!40000 ALTER TABLE `page_title` DISABLE KEYS */;
INSERT INTO `page_title` VALUES (1,'My page title','Titolo my page','My pageのタイトル'),(2,'Home title','Titolo home','ホームのタイトル'),(3,'Registration title','Titolo registrazione','登録のタイトル'),(4,'Recover password title','Titolo recupero password','パスワードを回復のタイトル'),(5,'Search title','Titolo cerca','サーチのタイトル');
/*!40000 ALTER TABLE `page_title` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `transaction` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `date` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `payer` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `receiver` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `currency_code` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `item_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `amount` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `quantity` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `status_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment`
--

LOCK TABLES `payment` WRITE;
/*!40000 ALTER TABLE `payment` DISABLE KEYS */;
INSERT INTO `payment` VALUES (1,2,'419530066K1260733','09:19:50 Jul 04, 2016 PDT','Completed','WGRYMAE6MYEP4','5CLM69V9C3PVW','USD','credits','0.01','1',0),(2,2,'1KY910617X114632S','05:16:37 Jul 07, 2016 PDT','Completed','WGRYMAE6MYEP4','5CLM69V9C3PVW','USD','credits','0.01','1',0),(3,2,'19U375700V802405E','05:31:11 Jul 07, 2016 PDT','Completed','WGRYMAE6MYEP4','5CLM69V9C3PVW','USD','credits','0.01','1',0),(4,2,'7JV630061G061150L','06:10:56 Jul 07, 2016 PDT','Completed','WGRYMAE6MYEP4','5CLM69V9C3PVW','USD','credits','0.01','1',0),(5,2,'87V61799HN941194T','06:15:57 Jul 07, 2016 PDT','Completed','WGRYMAE6MYEP4','5CLM69V9C3PVW','USD','credits','0.05','5',0),(6,2,'6FU073223W956013S','06:26:31 Jul 07, 2016 PDT','Completed','WGRYMAE6MYEP4','5CLM69V9C3PVW','USD','credits','0.02','2',0);
/*!40000 ALTER TABLE `payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_user`
--

DROP TABLE IF EXISTS `role_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ROLE_USER',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_user`
--

LOCK TABLES `role_user` WRITE;
/*!40000 ALTER TABLE `role_user` DISABLE KEYS */;
INSERT INTO `role_user` VALUES (1,'ROLE_USER'),(2,'ROLE_ADMIN'),(3,'ROLE_MODERATOR'),(4,'ROLE_MICROSERVICE'),(5,'ROLE_IPCAMERA');
/*!40000 ALTER TABLE `role_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setting`
--

DROP TABLE IF EXISTS `setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'basic',
  `template_column` int(1) NOT NULL DEFAULT '1',
  `language` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  `email_admin` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `website_active` tinyint(1) NOT NULL DEFAULT '1',
  `role_user_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '2,3',
  `https` tinyint(1) NOT NULL DEFAULT '1',
  `registration_user_confirm_admin` tinyint(1) NOT NULL DEFAULT '0',
  `login_attempt_time` int(11) NOT NULL DEFAULT '15',
  `login_attempt_count` int(11) NOT NULL DEFAULT '3',
  `registration` tinyint(1) NOT NULL DEFAULT '1',
  `recover_password` tinyint(1) NOT NULL DEFAULT '1',
  `captcha` tinyint(1) NOT NULL DEFAULT '0',
  `page_date` tinyint(1) NOT NULL DEFAULT '1',
  `pageComment` tinyint(1) NOT NULL DEFAULT '1',
  `pageComment_active` tinyint(1) NOT NULL DEFAULT '1',
  `use_type` int(11) NOT NULL DEFAULT '1',
  `secret_passphrase` longtext COLLATE utf8_unicode_ci NOT NULL,
  `payment` tinyint(1) NOT NULL DEFAULT '1',
  `credit` tinyint(1) NOT NULL DEFAULT '1',
  `payPal_sandbox` tinyint(1) NOT NULL DEFAULT '1',
  `payPal_business` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `payPal_currency_code` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'EUR',
  `payPal_credit_amount` varchar(12) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0.01',
  `javascript_minify` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting`
--

LOCK TABLES `setting` WRITE;
/*!40000 ALTER TABLE `setting` DISABLE KEYS */;
INSERT INTO `setting` VALUES (1,'basic',4,'en','cimo@reinventsoftware.org',1,'2,3,',1,1,15,3,0,1,0,0,0,0,1,'1234',0,0,1,'paypal.business@gmail.com','EUR','0.01',0);
/*!40000 ALTER TABLE `setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setting_line_push`
--

DROP TABLE IF EXISTS `setting_line_push`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setting_line_push` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_id_primary` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `access_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting_line_push`
--

LOCK TABLES `setting_line_push` WRITE;
/*!40000 ALTER TABLE `setting_line_push` DISABLE KEYS */;
INSERT INTO `setting_line_push` VALUES (1,'api_basic','az09','+/=',1);
/*!40000 ALTER TABLE `setting_line_push` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setting_line_push_user`
--

DROP TABLE IF EXISTS `setting_line_push_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setting_line_push_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `push_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `active` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting_line_push_user`
--

LOCK TABLES `setting_line_push_user` WRITE;
/*!40000 ALTER TABLE `setting_line_push_user` DISABLE KEYS */;
INSERT INTO `setting_line_push_user` VALUES (1,'api_basic','Abcd09','cimo@reinventsoftware.org',1);
/*!40000 ALTER TABLE `setting_line_push_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setting_slack_iw`
--

DROP TABLE IF EXISTS `setting_slack_iw`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setting_slack_iw` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `hook` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `channel` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting_slack_iw`
--

LOCK TABLES `setting_slack_iw` WRITE;
/*!40000 ALTER TABLE `setting_slack_iw` DISABLE KEYS */;
INSERT INTO `setting_slack_iw` VALUES (1,'api_basic','https://hooks.slack.com/services','#api_basic',1);
/*!40000 ALTER TABLE `setting_slack_iw` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_user_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1,',
  `roles` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ROLE_USER,',
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `telephone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `born` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fiscal_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vat` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `credit` int(11) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `date_registration` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_current_login` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_last_login` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `help_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attempt_login` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'1,2,','ROLE_USER,ROLE_ADMIN','5cbfc82693953.jpg','cimo','Simone','D\'Agostino','cimo@reinventsoftware.org','080123456789','1984-4-11','m',NULL,NULL,NULL,'https://www.reinventsoftware.org','Japan','Tokyo','100-0001','Street','$2y$13$hOJvU2.m8vRl5YxsuY/J0OiQGFSS7DAa8mTA5uNGZGxmYoc8zFgde',0,1,'2016-08-04 10:25:12','2019-10-30 10:46:45','2019-10-29 11:47:51',NULL,'192.168.56.1',0),(2,'1,4,','ROLE_USER,ROLE_MICROSERVICE',NULL,'test_1',NULL,NULL,'test_1@reinventsoftware.org',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$13$Hi5SnSpKl9oKC79.G09MjeKOGUAzPEFjM3QPyp9z69m/gVXdnivJ2',0,1,'2016-09-10 17:39:31','2019-01-22 15:09:43','2019-01-22 14:55:58','','192.168.56.1',0),(3,'1,4,5,','ROLE_USER,ROLE_MICROSERVICE,ROLE_IPCAMERA',NULL,'test_2',NULL,NULL,'test_2@reinventsoftware.org',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$13$fo/L0jc1j4uWXAFjjOKE3eP0cgwv8DtBkjvUnMC9Eaa2B537B7uXq',0,0,'0000-00-00 00:00:00','2018-09-05 16:48:01','2018-09-05 16:47:23',NULL,'183.77.252.62',0);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'ipcamera_cp'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-10-30 10:48:53
