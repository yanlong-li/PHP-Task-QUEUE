<?php
/**
 * Created by PhpStorm.
 * User: Yanlongli
 * Date: 2018/8/2
 * Time: 16:33
 */

namespace non0\task_queue;


use non0\task_queue\server\BaseServer;
use non0\task_queue\support\dbToJson;
use non0\task_queue\support\Log;

class ImplementQueue
{
    protected static $taskInfo = null;

    public static function main()
    {
        $task = self::getTaskQueue();
        if (isset($task) && is_array($task) && !empty($task) && self::checkTask($task[1])) {
            Log::info('执行服务:' . self::$taskInfo['name']);// . PHP_EOL;
            $serverName = "non0\\task_queue\\server\\" . self::$taskInfo['name'];
            /**
             * @var $main BaseServer
             */
            $main = new $serverName();
            $result = $main->main(isset(self::$taskInfo['value']) ? self::$taskInfo['value'] : []);
            self::setReturnQueue($task[1], isset($result['status']) ? $result['status'] : true, isset($result['errmsg']) ? $result['errmsg'] : '任务执行完成');
            Log::info('服务结束:' . self::$taskInfo['name']);//. PHP_EOL;
        }
    }

    /**
     * 任务回写
     * @param $task
     * @param bool $status
     * @param string $msg
     */
    protected static function setReturnQueue($task, $status = true, $msg = '')
    {
        TaskQueue::$Redis->main->rPush(TaskQueue::getConfig('resultqueue.key'), json_encode([$task, 'status' => $status, 'errmsg' => $msg]));
    }

    /**
     * 队列发现
     * @return array|bool
     */
    protected static function getTaskQueue()
    {
        try {
            return TaskQueue::$Redis->main->blPop(TaskQueue::getConfig('queue.key'), TaskQueue::getConfig('queue.timeout'));
        } catch (\Exception $exception) {
            Log::Trace('未发现队列任务，继续等待');//. PHP_EOL;
            return false;
        }
    }

    /**
     * 检查任务准备
     * @param $task
     * @return bool
     */
    protected static function checkTask($task)
    {
        if (!is_string($task)) {
            return false;
        }

        $taskInfo = json_decode($task, true);
        if (!$taskInfo) {
            Log::warning('未定义错误');// . PHP_EOL;
            self::setReturnQueue($task, false, '未定义错误');
            return false;
        }
        if (!isset($taskInfo['name'])) {
            Log::notice('未定义服务名称');
            self::setReturnQueue($task, false, '未定义服务名称');
            return false;
        }
        if (strtolower(substr($taskInfo['name'], -6)) != 'server') {
            $taskInfo['name'] .= 'Server';
        }
        if (!file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'server' . DIRECTORY_SEPARATOR . $taskInfo['name'] . '.php')) {
            self::setReturnQueue($task, false, '未发现服务执行程序');
            Log::notice('未发现服务执行程序:' . __DIR__ . DIRECTORY_SEPARATOR . 'server' . DIRECTORY_SEPARATOR . $taskInfo['name'] . '.php');
            return false;
        }

        self::$taskInfo = $taskInfo;
        return true;
    }
}