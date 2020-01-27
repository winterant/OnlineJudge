/*
 Navicat Premium Data Transfer

 Source Server         : mysql5.7
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : localhost:3306
 Source Schema         : lduoj

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : 65001

 Date: 25/01/2020 15:30:02
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for privilege
-- ----------------------------
DROP TABLE IF EXISTS `privileges`;
CREATE TABLE `privilege`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `authority` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for problems
-- ----------------------------
DROP TABLE IF EXISTS `problems`;
CREATE TABLE `problems`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
  `input` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
  `output` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
  `hint` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
  `source` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `spj` tinyint(4) NULL DEFAULT 0,
  `time_limit` int(11) NULL DEFAULT 0 COMMENT 'ms',
  `memory_limit` int(11) NULL DEFAULT 0 COMMENT 'MB',
  `submit` int(11) NULL DEFAULT 0,
  `solved` int(11) NULL DEFAULT 0,
  `in_date` datetime(0) NULL DEFAULT NULL,
  `state` tinyint(4) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1497 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for solutions
-- ----------------------------
DROP TABLE IF EXISTS `solutions`;
CREATE TABLE `solutions`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `problem_id` int(11) NOT NULL DEFAULT 0,
  `contest_id` int(11) NULL DEFAULT -1,
  `user_id` int(11) NULL DEFAULT NULL,
  `result` tinyint(4) NULL DEFAULT 0,
  `time` int(11) NULL DEFAULT 0,
  `memory` float NULL DEFAULT 0,
  `language` tinyint(4) NULL DEFAULT 0,
  `submit_time` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP(0),
  `judge_time` datetime(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  `pass_rate` decimal(3, 2) UNSIGNED NULL DEFAULT 0.00,
  `error_info` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `ip` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `code_length` int(11) NULL DEFAULT 0,
  `code` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`user_id`) USING BTREE,
  INDEX `pid`(`problem_id`) USING BTREE,
  INDEX `res`(`result`) USING BTREE,
  INDEX `cid`(`contest_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2050 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `password` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `nick` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `school` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `class` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `submit` int(11) NULL DEFAULT 0,
  `solved` int(11) NULL DEFAULT 0,
  `created_at` datetime(0) NULL DEFAULT NULL,
  `updated_at` datetime(0) NULL DEFAULT NULL,
  `remember_token` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1018 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
