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

 Date: 03/08/2018 16:09:09
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ck_sys_task_queue
-- ----------------------------
DROP TABLE IF EXISTS `ck_sys_task_queue`;
CREATE TABLE `ck_sys_task_queue`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '任务id',
  `servername` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '任务服务名称',
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '任务服务参数json',
  `create_time` int(11) UNSIGNED NOT NULL COMMENT '任务创建时间',
  `update_time` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '任务更新时间',
  `status` tinyint(1) UNSIGNED NOT NULL COMMENT '任务状态\r\n0创建待执行\r\n1正在执行\r\n2执行完成\r\n3执行失败\r\n9其他',
  `result_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '返回信息json',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of ck_sys_task_queue
-- ----------------------------
INSERT INTO `ck_sys_task_queue` VALUES (1, 'UnitImport', '{\"filename\":\"C:\\Users\\yanlo\\PhpstormProjects\\system_task\\demo\\test.json\"}', 0, 1533283293, 0, '{\"errmsg\":\"\\u6267\\u884c\\u6210\\u529f\"}');

SET FOREIGN_KEY_CHECKS = 1;
