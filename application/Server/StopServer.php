<?php
/**
 * Created by PhpStorm.
 * User: Yanlongli
 * Date: 2018/8/2
 * Time: 16:20
 */

namespace non0\task_queue\server;


use non0\task_queue\support\Log;
use non0\task_queue\TaskQueue;

class StopServer implements BaseServer
{
    public function main($argc)
    {
        Log::info('收到停止任务，正在处理任务停止');
        TaskQueue::$Redis->main->set('server',false);
        return ['status' => true];
    }
}