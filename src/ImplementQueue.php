<?php
/**
 * Created by PhpStorm.
 * User: yanlo
 * Date: 2018/8/2
 * Time: 16:33
 */

namespace non0\task_queue;


class ImplementQueue
{
    protected static $taskInfo = null;

    public static function main()
    {
        $task = self::getTaskQueue();
        if (isset($task) && is_array($task) && !empty($task) && self::checkTask($task[1])) {
            $serverName = "non0\\task_queue\\Server\\" . self::$taskInfo['name'];
            $main = new $serverName();
            $main->main(isset(self::$taskInfo['value']) ? self::$taskInfo['value'] : []);
            self::setReturnQueue($task[1], true, '任务执行完毕');
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
        TaskQueue::$Redis->main->rPush(TaskQueue::getConfig('resultqueue.key'), json_encode([$task, 'status' => $status, 'err' => $msg]));
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
            echo '未发现队列任务，继续等待' . PHP_EOL;
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
            echo '未定义错误' . PHP_EOL;
            self::setReturnQueue($task, false, '未定义错误');
            return false;
        }
        if (!isset($taskInfo['name'])) {
            echo '未定义服务名称' . PHP_EOL;
            self::setReturnQueue($task, false, '未定义服务名称');
            return false;
        }
        if (strtolower(substr($taskInfo['name'], -6)) != 'server') {
            $taskInfo['name'] .= 'Server';
        }
        if (!file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'Server' . DIRECTORY_SEPARATOR . $taskInfo['name'] . '.php')) {
            self::setReturnQueue($task, false, '未发现服务执行程序');
            return false;
        }

        self::$taskInfo = $taskInfo;
        return true;
    }
}