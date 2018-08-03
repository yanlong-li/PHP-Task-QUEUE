<?php
/**
 * Created by PhpStorm.
 * User: yanlo
 * Date: 2018/8/2
 * Time: 15:28
 */

use non0\task_queue\TaskQueue;
//引入composer
include "../vendor/autoload.php";
//加载配置文件
$config = require 'config.php';
//初始化 ***必做
\non0\task_queue\TaskQueue::init($config);

//添加一个任务  添加任务功能可用任意方式按照特定格式写入redis指定名称的队列中
TaskQueue::$Redis->main->rPush(TaskQueue::getConfig('queue.key'), json_encode(['name' => 'RefreshConfigServer', 'value' => '']));

//开始执行队列任务
TaskQueue::start();