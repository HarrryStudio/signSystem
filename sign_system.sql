/*
SQLyog Ultimate v11.11 (64 bit)
MySQL - 5.5.43-0ubuntu0.14.04.1 : Database - sign_system_new
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`sign_system` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `sign_system`;

/*Table structure for table `course` */

DROP TABLE IF EXISTS `course`;

CREATE TABLE `course` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `data` varchar(10000) NOT NULL DEFAULT '[]',
  `update_time` int(10) NOT NULL,
  `create_time` int(10) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;

/*Table structure for table `history_log` */

DROP TABLE IF EXISTS `history_log`;

CREATE TABLE `history_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'log_id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `in_time` int(10) NOT NULL COMMENT '签到时间',
  `out_time` int(10) NOT NULL COMMENT '签退时间',
  `offset` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否补签(0不是 1是)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2879 DEFAULT CHARSET=utf8;

/*Table structure for table `sign_log` */

DROP TABLE IF EXISTS `sign_log`;

CREATE TABLE `sign_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'log_id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `time` int(10) NOT NULL COMMENT '签(到\\退)时间',
  `type` tinyint(1) NOT NULL COMMENT '类型(0:到 1:退)',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态(0:未退,1以退)',
  `offset` tinyint(1) NOT NULL DEFAULT '0' COMMENT '补签(0:不是,1:是)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5765 DEFAULT CHARSET=utf8;

/*Table structure for table `team_info` */

DROP TABLE IF EXISTS `team_info`;

CREATE TABLE `team_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '组id',
  `name` varchar(10) NOT NULL COMMENT '组名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `user_info` */

DROP TABLE IF EXISTS `user_info`;

CREATE TABLE `user_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `team` int(11) NOT NULL COMMENT '分组id',
  `account` char(16) DEFAULT NULL COMMENT '账号',
  `name` char(10) NOT NULL COMMENT '名字',
  `class` char(8) DEFAULT NULL COMMENT '班级',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0: 正常 1:删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
