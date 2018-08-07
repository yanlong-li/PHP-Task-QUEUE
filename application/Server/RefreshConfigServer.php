<?php
/**
 * Created by PhpStorm.
 * User: yanlo
 * Date: 2018/8/2
 * Time: 16:20
 */

namespace non0\task_queue\Server;


use non0\task_queue\TaskQueue;

class RefreshConfigServer implements BaseServer
{
    public function main($argc)
    {
        echo '执行刷新配置文件任务' . PHP_EOL;
        TaskQueue::init(require 'config.php');
        return ['status' => true];
    }
}