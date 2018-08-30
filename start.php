<?php
/**
 * Created by PhpStorm.
 * User: Yanlongli
 * Date: 2018/8/2
 * Time: 15:28
 * 任务启动器
 */

use non0\task_queue\TaskQueue;

//定义根目录
define("APP_ROOT", dirname(__FILE__));

//引入composer
include "./vendor/autoload.php";
//加载配置文件
//初始化 ***必做
TaskQueue::init(require 'config.php');

///开始执行队列任务
/// argv 是从命令行启动接受的参数
TaskQueue::start($argv);