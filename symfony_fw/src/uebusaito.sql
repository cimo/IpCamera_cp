-- MySQL dump 10.13  Distrib 5.7.17, for macos10.12 (x86_64)
--
-- Host: 127.0.0.1    Database: uebusaito
-- ------------------------------------------------------
-- Server version	5.7.25-0ubuntu0.16.04.2

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
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `date` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y-m-d',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (1,'en','Y-m-d',1),(2,'jp','Y-m-d',1),(3,'it','d-m-Y',1);
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
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
INSERT INTO `microservice_api` VALUES (1,'Basic','ApiBasic','Test api functionality',NULL,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `microservice_apiBasic`
--

LOCK TABLES `microservice_apiBasic` WRITE;
/*!40000 ALTER TABLE `microservice_apiBasic` DISABLE KEYS */;
INSERT INTO `microservice_apiBasic` VALUES (1,'Site_1','token_basic','192.168.56.1',NULL,NULL,NULL,NULL,NULL,1,1,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `microservice_apiBasic_request`
--

LOCK TABLES `microservice_apiBasic_request` WRITE;
/*!40000 ALTER TABLE `microservice_apiBasic_request` DISABLE KEYS */;
INSERT INTO `microservice_apiBasic_request` VALUES (1,1,'requestTestAction','2018-11-05 12:05:29','192.168.56.1',1),(2,1,'requestTestAction','2018-11-06 12:05:29','192.168.56.1',2),(3,1,'requestTestAction','2018-11-07 12:05:29','192.168.56.1',1),(4,1,'requestTestAction','2018-11-08 12:05:29','192.168.56.1',4),(5,1,'requestTestAction','2018-11-09 12:05:29','192.168.56.1',1),(6,1,'requestTestAction','2018-11-10 12:05:29','192.168.56.1',1),(7,1,'requestTestAction','2018-11-11 12:05:29','192.168.56.1',6),(8,1,'requestTestAction','2018-11-12 12:05:29','192.168.56.1',1),(9,1,'requestTestAction','2018-11-13 12:05:29','192.168.56.1',8),(10,1,'requestTestAction','2018-11-14 12:05:29','192.168.56.1',1),(11,1,'requestTestAction','2018-11-16 12:05:29','192.168.56.1',4),(12,1,'requestTestAction','2018-11-17 12:05:29','192.168.56.1',1),(13,1,'requestTestAction','2018-11-18 12:05:29','192.168.56.1',6),(14,1,'requestTestAction','2018-11-19 12:05:29','192.168.56.1',1),(15,1,'requestTestAction','2018-11-20 12:05:29','192.168.56.1',2),(16,1,'requestTestAction','2018-11-22 12:05:29','192.168.56.1',1),(17,1,'requestTestAction','2018-11-23 12:05:29','192.168.56.1',9),(18,1,'requestTestAction','2018-11-24 12:05:29','192.168.56.1',1),(19,1,'requestTestAction','2018-11-25 12:05:29','192.168.56.1',13),(20,1,'requestTestAction','2018-11-26 12:05:29','192.168.56.1',1),(21,1,'requestTestAction','2018-12-11 16:44:09','192.168.56.1',1),(22,1,'requestTestAction','2018-12-26 16:00:44','192.168.56.1',2),(23,1,'requestTestAction','2018-12-27 17:07:56','192.168.56.1',26);
/*!40000 ALTER TABLE `microservice_apiBasic_request` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `microservice_deploy`
--

LOCK TABLES `microservice_deploy` WRITE;
/*!40000 ALTER TABLE `microservice_deploy` DISABLE KEYS */;
INSERT INTO `microservice_deploy` VALUES (1,'Test dev','Test','ubuntu',NULL,NULL,NULL,NULL,NULL,'123.123.123.123','user@github.com','git_user','url.git','username','uO\�\�K\�D�DG�\�7\�5','/home/user_1/www/test_dev','user_1','user_1:www-data','/home/user_1/www/test_dev/src','php -v\r\napache2 -v',1);
/*!40000 ALTER TABLE `microservice_deploy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modules` (
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
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES (1,'left','',1,'Authentication','module_1','AuthenticationController::moduleAction',1),(2,'center',NULL,1,'Page','module_2','PageViewController::moduleAction',1),(3,'right','',1,'Empty','module_3','EmptyController::moduleAction',1);
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (1,'myPage',NULL,'App\\Controller\\MyPage\\MyPageProfileController::renderAction','1,',1,0,1,0,0,0,'-','-','0000-00-00 00:00:00','cimo','2019-01-07 17:47:45','Personal page.','personal, page','noindex, nofollow'),(2,'home',NULL,NULL,'1,',0,1,2,1,0,0,'-','-','0000-00-00 00:00:00','cimo','2019-01-07 19:04:33','Website home page.','website, home, page','index, follow'),(3,'registration',NULL,'App\\Controller\\RegistrationController::renderAction','1,',0,0,3,1,0,0,'-','-','0000-00-00 00:00:00','cimo','2019-01-07 19:05:15','User registration page.','user, registration, page','index, follow'),(4,'recover_password',NULL,'App\\Controller\\RecoverPasswordController::renderAction','1,',0,0,4,1,0,0,'-','-','0000-00-00 00:00:00','cimo','2019-01-07 19:05:40','User recover password.','user, recover, password','index, follow'),(5,'search',NULL,'App\\Controller\\SearchController::renderAction','1,',0,0,5,1,0,0,'-','-','0000-00-00 00:00:00','cimo','2019-01-07 19:07:47','Website content search','website, content, search','noindex, nofollow'),(6,'test',NULL,'App\\Controller\\PageAction\\PageActionTestController::renderAction','1,5,',1,1,7,1,0,0,'-','-','0000-00-00 00:00:00','cimo','2019-02-08 12:47:38','Page test','page, test','index, follow'),(7,'test_parent',NULL,NULL,'1,',0,1,6,1,0,0,'-','-','0000-00-00 00:00:00','cimo','2019-01-07 19:08:22','Page test','page, test','index, follow'),(8,'test_children_1',7,NULL,'1,',0,1,1,1,0,0,'-','-','0000-00-00 00:00:00','cimo','2019-01-07 19:09:05','Page test','page, test','index, follow'),(9,'test_children_2',8,NULL,'1,',0,1,1,1,0,0,'-','-','0000-00-00 00:00:00','cimo','2019-01-07 19:09:24','Page test','page, test','index, follow'),(10,'test_2',8,NULL,'1,',0,1,2,1,0,0,'-','-','0000-00-00 00:00:00','cimo','2019-01-07 19:10:16','Page test','page, test','index, follow'),(11,'test_children_3',9,NULL,'1,',0,1,1,1,0,0,'-','-','0000-00-00 00:00:00','cimo','2019-01-07 19:09:42','Page test','page, test','index, follow'),(12,'test_1',7,NULL,'1,',0,1,2,1,0,1,'http://www.google.it','-','0000-00-00 00:00:00','cimo','2019-01-07 19:10:34','Page test','page, test','index, follow'),(13,'test_children_4',11,NULL,'1,',0,1,1,1,0,0,'-','-','0000-00-00 00:00:00','cimo','2019-01-07 19:10:01','Page test','page, test','index, follow');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages_arguments`
--

DROP TABLE IF EXISTS `pages_arguments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages_arguments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `en` longtext COLLATE utf8_unicode_ci,
  `it` longtext COLLATE utf8_unicode_ci,
  `jp` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages_arguments`
--

LOCK TABLES `pages_arguments` WRITE;
/*!40000 ALTER TABLE `pages_arguments` DISABLE KEYS */;
INSERT INTO `pages_arguments` VALUES (1,'User personal page.','Argomento my page.','My pageのアーギュメント。'),(2,'This is a cms created with symfony framework.','Argomento home.','ホームのアーギュメント。'),(3,'Registration argument.','Argomento registrazione.','登録のアーギュメント。'),(4,'Recover password argument.','Argomento recupero password.','パスワードを回復のアーギュメント。'),(5,'Search argument.','Argomento cerca.','サーチのアーギュメント。'),(6,'Test argument.','Argomento test.','テストのアーギュメント。'),(7,'Test parent argument.','Argomento test genitore.','テストparentのアーギュメント。'),(8,'Test children 1 argument.','Argomento test figlio 1.','テストchildren１のアーギュメント。'),(9,'Test children 2 argument.','Argomento test figlio 2.','テストchildren２のアーギュメント。'),(10,'Test 2 argument.','Argomento test 2.','テスト２のアーギュメント。'),(11,'Test children 3 argument.','Argomento test figlio 3.','テストchildren３のアーギュメント。'),(12,'Test 1 argument.','Argomento test 1.','テスト１のアーギュメント。'),(13,'Test children 4 argument.','Argomento test figlio 4.','テストchildren４のアーギュメント。');
/*!40000 ALTER TABLE `pages_arguments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages_comments`
--

DROP TABLE IF EXISTS `pages_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL DEFAULT '0',
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `id_reply` int(11) DEFAULT NULL,
  `argument` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `date_create` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modify` varchar(19) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages_comments`
--

LOCK TABLES `pages_comments` WRITE;
/*!40000 ALTER TABLE `pages_comments` DISABLE KEYS */;
INSERT INTO `pages_comments` VALUES (1,6,'cimo',NULL,'Comment test.','2017-10-31 11:45:22','2018-12-13 12:23:39'),(2,6,'test_1',NULL,'New comment test.','2017-10-31 11:55:18','2017-11-01 23:51:35'),(3,6,'cimo',2,'Test over.','2018-12-13 12:25:59','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `pages_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages_menu_names`
--

DROP TABLE IF EXISTS `pages_menu_names`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages_menu_names` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `en` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  `it` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  `jp` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages_menu_names`
--

LOCK TABLES `pages_menu_names` WRITE;
/*!40000 ALTER TABLE `pages_menu_names` DISABLE KEYS */;
INSERT INTO `pages_menu_names` VALUES (1,'-','-','-'),(2,'Home','Home','ホーム'),(3,'-','-','-'),(4,'-','-','-'),(5,'-','-','-'),(6,'Test','Test','テスト'),(7,'Test parent','Test genitore','テストparent'),(8,'Test children 1','Test figlio 1','テストchildren１'),(9,'Test children 2','Test figlio 2','テストchildren２'),(10,'Test 2','Test 2','テスト２'),(11,'Test children 3','Test figlio 3','テストchildren３'),(12,'Test 1','Test 1','テスト１'),(13,'Test children 4','Test figlio 4','テストchildren４');
/*!40000 ALTER TABLE `pages_menu_names` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages_titles`
--

DROP TABLE IF EXISTS `pages_titles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages_titles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `en` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `it` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `jp` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages_titles`
--

LOCK TABLES `pages_titles` WRITE;
/*!40000 ALTER TABLE `pages_titles` DISABLE KEYS */;
INSERT INTO `pages_titles` VALUES (1,'My page title','Titolo my page','My pageのタイトル'),(2,'Home title','Titolo home','ホームのタイトル'),(3,'Registration title','Titolo registrazione','登録のタイトル'),(4,'Recover password title','Titolo recupero password','パスワードを回復のタイトル'),(5,'Search title','Titolo cerca','サーチのタイトル'),(6,'Test title','Titolo test','テストのタイトル'),(7,'Test parent title','Titolo test genitore','テストparentのタイトル'),(8,'Test children 1 title','Titolo test figlio 1','テストchildren１のタイトル'),(9,'Test children 2 title','Titolo test figlio 2','テストchildren２のタイトル'),(10,'Test 2 title','Titolo test 2','テスト２のタイトル'),(11,'Test children 3 title','Titolo test figlio 3','テストchildren３のタイトル'),(12,'Test 1 title','Titolo test 1','テスト１のタイトル'),(13,'Test children 4 title','Titolo test figlio 4','テストchildren４のタイトル');
/*!40000 ALTER TABLE `pages_titles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
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
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,2,'419530066K1260733','09:19:50 Jul 04, 2016 PDT','Completed','WGRYMAE6MYEP4','5CLM69V9C3PVW','USD','credits','0.01','1',0),(2,2,'1KY910617X114632S','05:16:37 Jul 07, 2016 PDT','Completed','WGRYMAE6MYEP4','5CLM69V9C3PVW','USD','credits','0.01','1',0),(3,2,'19U375700V802405E','05:31:11 Jul 07, 2016 PDT','Completed','WGRYMAE6MYEP4','5CLM69V9C3PVW','USD','credits','0.01','1',0),(4,2,'7JV630061G061150L','06:10:56 Jul 07, 2016 PDT','Completed','WGRYMAE6MYEP4','5CLM69V9C3PVW','USD','credits','0.01','1',0),(5,2,'87V61799HN941194T','06:15:57 Jul 07, 2016 PDT','Completed','WGRYMAE6MYEP4','5CLM69V9C3PVW','USD','credits','0.05','5',0),(6,2,'6FU073223W956013S','06:26:31 Jul 07, 2016 PDT','Completed','WGRYMAE6MYEP4','5CLM69V9C3PVW','USD','credits','0.02','2',0);
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles_users`
--

DROP TABLE IF EXISTS `roles_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ROLE_USER',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_users`
--

LOCK TABLES `roles_users` WRITE;
/*!40000 ALTER TABLE `roles_users` DISABLE KEYS */;
INSERT INTO `roles_users` VALUES (1,'ROLE_USER'),(2,'ROLE_ADMIN'),(3,'ROLE_MODERATOR'),(4,'ROLE_MICROSERVICE'),(5,'ROLE_TEST');
/*!40000 ALTER TABLE `roles_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'basic',1,'en','cimo@reinventsoftware.org',1,'2,3,',1,0,15,3,1,1,0,1,1,1,1,'1234',1,1,1,'paypal.business@gmail.com','EUR','0.01');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings_line_push`
--

DROP TABLE IF EXISTS `settings_line_push`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings_line_push` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_id_primary` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `access_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings_line_push`
--

LOCK TABLES `settings_line_push` WRITE;
/*!40000 ALTER TABLE `settings_line_push` DISABLE KEYS */;
INSERT INTO `settings_line_push` VALUES (1,'api_basic','az09','+/=',1);
/*!40000 ALTER TABLE `settings_line_push` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings_line_push_user`
--

DROP TABLE IF EXISTS `settings_line_push_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings_line_push_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `push_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `user_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `active` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings_line_push_user`
--

LOCK TABLES `settings_line_push_user` WRITE;
/*!40000 ALTER TABLE `settings_line_push_user` DISABLE KEYS */;
INSERT INTO `settings_line_push_user` VALUES (1,'api_basic','Abcd09','cimo@reinventsoftware.org',1);
/*!40000 ALTER TABLE `settings_line_push_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings_slack_iw`
--

DROP TABLE IF EXISTS `settings_slack_iw`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings_slack_iw` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `hook` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `channel` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings_slack_iw`
--

LOCK TABLES `settings_slack_iw` WRITE;
/*!40000 ALTER TABLE `settings_slack_iw` DISABLE KEYS */;
INSERT INTO `settings_slack_iw` VALUES (1,'api_basic','https://hooks.slack.com/services','#api_basic',1);
/*!40000 ALTER TABLE `settings_slack_iw` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_user_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1,',
  `roles` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ROLE_USER,',
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
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'1,2,','ROLE_USER,ROLE_ADMIN','cimo','Simone','D\'Agostino','cimo@reinventsoftware.org','080123456789','1984-4-11','m',NULL,NULL,NULL,'https://www.reinventsoftware.org','Japan','Tokyo','100-0001','Street','$2y$13$hOJvU2.m8vRl5YxsuY/J0OiQGFSS7DAa8mTA5uNGZGxmYoc8zFgde',0,1,'2016-08-04 10:25:12','2019-02-15 18:49:38','2019-02-08 14:41:07',NULL,'192.168.56.1',0),(2,'1,4,','ROLE_USER,ROLE_MICROSERVICE','test_1',NULL,NULL,'test_1@reinventsoftware.org',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$13$Hi5SnSpKl9oKC79.G09MjeKOGUAzPEFjM3QPyp9z69m/gVXdnivJ2',0,1,'2016-09-10 17:39:31','2019-01-22 15:09:43','2019-01-22 14:55:58','','192.168.56.1',0),(3,'1,4,5,','ROLE_USER,ROLE_MICROSERVICE,ROLE_TEST','test_2',NULL,NULL,'test_2@reinventsoftware.org',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$13$fo/L0jc1j4uWXAFjjOKE3eP0cgwv8DtBkjvUnMC9Eaa2B537B7uXq',0,0,'0000-00-00 00:00:00','2018-09-05 16:48:01','2018-09-05 16:47:23',NULL,'183.77.252.62',0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'uebusaito'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-02-15 19:21:08
