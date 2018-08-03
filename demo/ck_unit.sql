/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50617
 Source Host           : localhost:3306
 Source Schema         : dsx_check

 Target Server Type    : MySQL
 Target Server Version : 50617
 File Encoding         : 65001

 Date: 03/08/2018 16:09:21
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ck_unit
-- ----------------------------
DROP TABLE IF EXISTS `ck_unit`;
CREATE TABLE `ck_unit`  (
  `id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'uuid',
  `industry_name_data` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '行业信息json序列化2维数组\r\n[\r\n   {\r\n      \"code\": \"0112\",\r\n      \"title\": \"小麦种植\"\r\n    }， {\r\n      \"code\": \"0112\",\r\n      \"title\": \"小麦种植\"\r\n    }\r\n]',
  `village_id` char(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '普查小区代码',
  `village_id_extend` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `credit_id` char(18) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '统一社会信用代码',
  `credit_id_extend` char(2) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `organization_id` char(9) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '组织机构代码',
  `organization_id_extend` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `identification_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '普查对象识别码',
  `licence_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '排污许可证编号',
  `unit_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '单位名称',
  `used_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '曾用名',
  `runing_status` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '运行状态',
  `runing_statu1` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '其他状态json1维数组\r\n[\r\n    \"其他\",\r\n    \"新增\"\r\n  ]',
  `address_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '生产地址-使用region地址拼接',
  `door_number` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '门牌号手动填写',
  `contacts` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '联系人',
  `fixed_tel` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '座机',
  `mobile_phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '移动电话',
  `industry_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '行业名称',
  `industry_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '行业代码',
  `industry_resources` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否涉及下列矿产资源开采、选矿、冶炼（分离）、加工',
  `minerals` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '涉及内容json1维',
  `is_not_factory` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '有无厂址',
  `factory_num` tinyint(2) UNSIGNED NULL DEFAULT 0 COMMENT '厂址数量',
  `other_address` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '其他厂址地址json2维数组\r\n\r\n[\r\n    {\r\n      \"AddressName\": \"江苏省-南京市-六合区-金牛湖街道-樊集社区村委会\",\r\n      \"DoorNumber\": \"666\",\r\n      \"status\": 1\r\n    },\r\n    {\r\n      \"AddressName\": \"江苏省-南京市-六合区-金牛湖街道-长山社区居委会\",\r\n      \"DoorNumber\": \"777\",\r\n      \"status\": 1\r\n    },\r\n    {\r\n      \"AddressName\": \"江苏省-南京市-六合区-金牛湖街道-樊集社区村委会\",\r\n      \"DoorNumber\": \"888\",\r\n      \"status\": 1\r\n    }\r\n  ]',
  `remarks` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '备注',
  `enumerator_name` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '普查员',
  `enumerator_id` varchar(222) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '普查员编号',
  `enumerator_id_whether` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `create_time` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '填表时间',
  `update_time` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '更新时间',
  `examine_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '审核人',
  `examine_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '审核人id',
  `examine_time` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '审核时间',
  `longitude` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '经度度',
  `longitude1` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '经度分',
  `longitude2` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '经度秒',
  `latitude` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '维度度',
  `latitude1` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '维度分',
  `latitude2` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '维度秒',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '提交者用户id',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态\r\n0新增未提交\r\n1提交审核\r\n2审核通过\r\n3审核未通过\r\n4锁定\r\n5已分配\r\n6已完成\r\n9删除',
  `delete_time` int(11) UNSIGNED NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '工业企业和产业活动单位清查表' ROW_FORMAT = Compact;

SET FOREIGN_KEY_CHECKS = 1;
