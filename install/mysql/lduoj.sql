SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

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
    `revise` int(11) NOT NULL DEFAULT 1000,
    `locked` tinyint(4) NULL DEFAULT 0,
    `solved` int(11) NOT NULL DEFAULT 0,
    `accepted` int(11) NOT NULL DEFAULT 0,
    `submitted` int(11) NOT NULL DEFAULT 0,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `remember_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `IDX_username`(`username`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1000 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for blacklist
-- ----------------------------
DROP TABLE IF EXISTS `blacklist`;
CREATE TABLE `blacklist`  (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NULL DEFAULT NULL,
    `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
    `creator` int(11) NULL DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = FIXED;

-- ----------------------------
-- Table structure for groups
-- ----------------------------
DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups`  (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'group名称 e.g. 新生训练组',
    `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
    `teacher` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '教师姓名',
    `grade` int(11) NULL DEFAULT NULL COMMENT '年级/入学年份 e.g. 2016',
    `major` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '专业名称 e.g 软件工程',
    `class` int(11) NULL DEFAULT NULL COMMENT '班级编号 e.g 01',
    `private` tinyint(4) NULL DEFAULT 1 COMMENT '是否私有',
    `hidden` tinyint(4) NULL DEFAULT 1 COMMENT '是否隐藏',
    `creator` int(11) NOT NULL COMMENT '创建人user ID',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `IDX_groups_creator`(`creator`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for group_users
-- ----------------------------
DROP TABLE IF EXISTS `group_users`;
CREATE TABLE `group_users`  (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `group_id` int(11) NOT NULL COMMENT '班级编号',
    `user_id` int(11) NOT NULL COMMENT '成员编号',
    `identity` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0:申请加入; 1:普通成员; 2:班长; 3:教师/管理员; 4: 已退出/拒绝加入',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `gid`(`group_id`) USING BTREE,
    INDEX `uid`(`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = FIXED;

-- ----------------------------
-- Table structure for group_contests
-- ----------------------------
DROP TABLE IF EXISTS `group_contests`;
CREATE TABLE `group_contests`  (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `contest_id` int(11) NOT NULL COMMENT '比赛ID',
    `group_id` int(11) NOT NULL COMMENT '班级ID',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `cid`(`contest_id`) USING BTREE,
    INDEX `gid`(`group_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = FIXED;

-- ----------------------------
-- Table structure for contests
-- ----------------------------
DROP TABLE IF EXISTS `contests`;
CREATE TABLE `contests`  (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `judge_instantly` tinyint NOT NULL DEFAULT 1 COMMENT '是否即时判题，否则赛后只判最后一次提交',
    `judge_type` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'acm' COMMENT 'acm,oi',
    `open_discussion` tinyint NULL DEFAULT 1,
    `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
    `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
    `allow_lang` int NULL DEFAULT 0 COMMENT '按位标记允许的提交语言',
    `start_time` datetime NULL DEFAULT NULL,
    `end_time` datetime NULL DEFAULT NULL,
    `lock_rate` float NULL DEFAULT 0 COMMENT '封榜比例，0.00~1.00',
    `public_rank` tinyint NOT NULL DEFAULT 0 COMMENT '榜单公开访问',
    `access` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'public' COMMENT 'public,password,private',
    `password` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
    `user_id` int NULL DEFAULT NULL,
    `hidden` tinyint NULL DEFAULT 0,
    `order` int NULL DEFAULT 0,
    `cate_id` int NULL DEFAULT 0,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `stime`(`start_time`) USING BTREE,
    INDEX `etime`(`end_time`) USING BTREE,
    INDEX `index_user_id`(`user_id`) USING BTREE,
    INDEX `index_cate_id`(`cate_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1000 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for contest_cate
-- ----------------------------
DROP TABLE IF EXISTS `contest_cate`;
CREATE TABLE `contest_cate`  (
    `id` int NOT NULL AUTO_INCREMENT,
    `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
    `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '分类描述',
    `hidden` tinyint NULL DEFAULT 0,
    `order` int NULL DEFAULT 0 COMMENT '顺序',
    `parent_id` int NULL DEFAULT 0 COMMENT '子分类的父id，0代表一级分类',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `index_parent_id`(`parent_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for contest_balloons
-- ----------------------------
DROP TABLE IF EXISTS `contest_balloons`;
CREATE TABLE `contest_balloons`  (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `solution_id` int(11) NULL DEFAULT NULL,
    `sent` tinyint(4) NULL DEFAULT 0,
    `send_time` datetime NULL DEFAULT NULL,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = FIXED;

-- ----------------------------
-- Table structure for contest_notices
-- ----------------------------
DROP TABLE IF EXISTS `contest_notices`;
CREATE TABLE `contest_notices`  (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `contest_id` int(11) NULL DEFAULT NULL,
    `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
    `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
    `creator` int(11) NULL DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `cid`(`contest_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for contest_problems
-- ----------------------------
DROP TABLE IF EXISTS `contest_problems`;
CREATE TABLE `contest_problems`  (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `contest_id` int(11) NULL DEFAULT NULL,
    `index` int(11) NULL DEFAULT 1001,
    `problem_id` int(11) NULL DEFAULT NULL,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `cid`(`contest_id`) USING BTREE,
    INDEX `pid`(`problem_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = FIXED;

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
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = FIXED;

-- ----------------------------
-- Table structure for problems
-- ----------------------------
DROP TABLE IF EXISTS `problems`;
CREATE TABLE `problems`  (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0:编程,1:代码填空',
    `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
    `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
    `input` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
    `output` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
    `fill_in_blank` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '代码填空的完整代码',
    `hint` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
    `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
    `spj` tinyint(4) NULL DEFAULT 0,
    `time_limit` int(11) NULL DEFAULT 0 COMMENT 'MS',
    `memory_limit` int(11) NULL DEFAULT 0 COMMENT 'MB',
    `hidden` tinyint(4) NULL DEFAULT 1,
    `creator` int(11) NULL DEFAULT NULL,
    `solved` int(11) NOT NULL DEFAULT 0,
    `accepted` int(11) NOT NULL DEFAULT 0,
    `submitted` int(11) NOT NULL DEFAULT 0,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1000 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for discussions
-- ----------------------------
DROP TABLE IF EXISTS `discussions`;
CREATE TABLE `discussions`  (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
    `problem_id` int(11) NULL DEFAULT -1,
    `discussion_id` int(11) NULL DEFAULT -1,
    `reply_username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
    `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
    `top` int(11) NULL DEFAULT 0,
    `hidden` tinyint(4) NULL DEFAULT 0,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for tag_marks
-- ----------------------------
DROP TABLE IF EXISTS `tag_marks`;
CREATE TABLE `tag_marks`  (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `problem_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `tag_id` int(11) NOT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = FIXED;

-- ----------------------------
-- Table structure for tag_pool
-- ----------------------------
DROP TABLE IF EXISTS `tag_pool`;
CREATE TABLE `tag_pool`  (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `parent_id` int(11) NULL DEFAULT -1,
    `hidden` tinyint(4) NULL DEFAULT 0,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

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
    `submit_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `judge_time` datetime NULL DEFAULT NULL,
    `pass_rate` decimal(3, 2) UNSIGNED NULL DEFAULT 0.00,
    `error_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
    `wrong_data` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
    `ip` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
    `judger` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `code_length` int(11) NULL DEFAULT 0,
    `code` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
    `sim_rate` int(11) NULL DEFAULT 0 COMMENT '0~100',
    `sim_sid` int(11) NULL DEFAULT -1,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `uid`(`user_id`) USING BTREE,
    INDEX `pid`(`problem_id`) USING BTREE,
    INDEX `res`(`result`) USING BTREE,
    INDEX `cid`(`contest_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1000 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for notices
-- ----------------------------
DROP TABLE IF EXISTS `notices`;
CREATE TABLE `notices`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
    `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
    `state` tinyint(4) NULL DEFAULT 1 COMMENT '0:hidden,1:normal,2:置顶',
    `user_id` int(11) NULL DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for settings
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings`  (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for privileges
-- ----------------------------
DROP TABLE IF EXISTS `privileges`;
CREATE TABLE `privileges`  (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `authority` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
    `creator` int(11) NULL DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `uid`(`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = DYNAMIC;

SET FOREIGN_KEY_CHECKS = 1;
