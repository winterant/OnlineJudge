/*
 Navicat Premium Data Transfer

 Source Server         : mysql_win10
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : localhost:3306
 Source Schema         : lduoj

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : 65001

 Date: 26/03/2020 18:36:46
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for contest_balloons
-- ----------------------------
DROP TABLE IF EXISTS `contest_balloons`;
CREATE TABLE `contest_balloons`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `solution_id` int(11) NULL DEFAULT NULL,
  `sent` tinyint(4) NULL DEFAULT 0,
  `send_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Table structure for contest_notices
-- ----------------------------
DROP TABLE IF EXISTS `contest_notices`;
CREATE TABLE `contest_notices`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contest_id` int(11) NULL DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `created_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `cid`(`contest_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for contest_problems
-- ----------------------------
DROP TABLE IF EXISTS `contest_problems`;
CREATE TABLE `contest_problems`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contest_id` int(11) NULL DEFAULT NULL,
  `index` int(11) NULL DEFAULT 1001,
  `problem_id` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `cid`(`contest_id`) USING BTREE,
  INDEX `pid`(`problem_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Table structure for contest_users
-- ----------------------------
DROP TABLE IF EXISTS `contest_users`;
CREATE TABLE `contest_users`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contest_id` int(11) NULL DEFAULT NULL,
  `user_id` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `cid`(`contest_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Table structure for contests
-- ----------------------------
DROP TABLE IF EXISTS `contests`;
CREATE TABLE `contests`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'acm' COMMENT 'acm,oi,exam',
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `allow_lang` int(11) NULL DEFAULT 0 COMMENT '按位标记允许的提交语言',
  `start_time` datetime(0) NULL DEFAULT NULL,
  `end_time` datetime(0) NULL DEFAULT NULL,
  `lock_rate` float NULL DEFAULT 0 COMMENT '封榜比例，0.00~1.00',
  `access` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'public' COMMENT 'public,password,private',
  `password` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `user_id` int(11) NULL DEFAULT NULL,
  `hidden` tinyint(4) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `stime`(`start_time`) USING BTREE,
  INDEX `etime`(`end_time`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1000 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for notices
-- ----------------------------
DROP TABLE IF EXISTS `notices`;
CREATE TABLE `notices`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `state` tinyint(4) NULL DEFAULT 1 COMMENT '0:hidden,1:normal,2:置顶',
  `created_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for privileges
-- ----------------------------
DROP TABLE IF EXISTS `privileges`;
CREATE TABLE `privileges`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `authority` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `created_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for problems
-- ----------------------------
DROP TABLE IF EXISTS `problems`;
CREATE TABLE `problems`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `input` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `output` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `hint` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `source` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `spj` tinyint(4) NULL DEFAULT 0,
  `time_limit` int(11) NULL DEFAULT 0 COMMENT 'ms',
  `memory_limit` int(11) NULL DEFAULT 0 COMMENT 'MB',
  `created_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP,
  `hidden` tinyint(4) NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1000 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for solutions
-- ----------------------------
DROP TABLE IF EXISTS `solutions`;
CREATE TABLE `solutions`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `problem_id` int(11) NOT NULL DEFAULT 0,
  `contest_id` int(11) NULL DEFAULT -1,
  `user_id` int(11) NULL DEFAULT NULL,
  `judge_type` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'acm,oi,exam',
  `result` tinyint(4) NULL DEFAULT 0,
  `time` int(11) NULL DEFAULT 0 COMMENT 'MS',
  `memory` float NULL DEFAULT 0 COMMENT 'MB',
  `language` tinyint(4) NULL DEFAULT 0,
  `submit_time` datetime(0) NULL DEFAULT CURRENT_TIMESTAMP,
  `judge_time` datetime(0) NULL DEFAULT NULL,
  `pass_rate` decimal(3, 2) UNSIGNED NULL DEFAULT 0.00,
  `error_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `ip` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `judger` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `code_length` int(11) NULL DEFAULT 0,
  `code` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`user_id`) USING BTREE,
  INDEX `pid`(`problem_id`) USING BTREE,
  INDEX `res`(`result`) USING BTREE,
  INDEX `cid`(`contest_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `password` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `nick` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `school` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `class` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `revise` int(11) NULL DEFAULT 2,
  `created_at` datetime(0) NULL DEFAULT NULL,
  `updated_at` datetime(0) NULL DEFAULT NULL,
  `remember_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uname`(`username`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1000 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
