/*
Navicat MySQL Data Transfer

Target Server Type    : MYSQL
Target Server Version : 50148
File Encoding         : 65001

Date: 2019-09-18 11:49:49
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for zhubajie_task
-- ----------------------------
DROP TABLE IF EXISTS `zhubajie_task`;
CREATE TABLE `zhubajie_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `taskid` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL COMMENT '需求标题',
  `price_money` varchar(32) DEFAULT NULL COMMENT '需求价格',
  `deposit_money` varchar(32) DEFAULT NULL COMMENT '已托管赏金',
  `task_status` varchar(32) DEFAULT NULL COMMENT '需求状态',
  `spider_status` tinyint(4) DEFAULT '0' COMMENT '爬取状态 0:待爬取详情 1:爬取成功 2:爬取失败',
  `situation` varchar(255) DEFAULT NULL COMMENT '参与情况',
  `tags` varchar(255) DEFAULT NULL COMMENT '需求标签',
  `first_category` varchar(64) DEFAULT NULL COMMENT '第一分类',
  `second_category` varchar(64) DEFAULT NULL COMMENT '第二分类',
  `desc` text COMMENT '需求描述',
  `desc_supplement` text COMMENT '需求补充',
  `url` varchar(128) DEFAULT NULL COMMENT '需求地址',
  `public_at` varchar(255) DEFAULT NULL COMMENT '需求发布时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `taskid` (`taskid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
