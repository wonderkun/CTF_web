/*
 Navicat Premium Data Transfer

 Source Server         : mysql localhost
 Source Server Type    : MySQL
 Source Server Version : 50622
 Source Host           : localhost
 Source Database       : xdctf

 Target Server Type    : MySQL
 Target Server Version : 50622
 File Encoding         : utf-8

 Date: 10/15/2015 22:39:48 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `file`
-- ----------------------------
DROP TABLE IF EXISTS `file`;
CREATE TABLE `file` (
  `fid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(256) NOT NULL,
  `oldname` varchar(256) DEFAULT NULL,
  `view` int(11) DEFAULT NULL,
  `extension` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`fid`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;
