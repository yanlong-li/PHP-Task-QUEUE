<?php
/**
 * Created by PhpStorm.
 * User: Yanlongli
 * Date: 2018/8/30
 * Time: 11:04
 */

namespace non0\task_queue\support;


use non0\task_queue\TaskQueue;

class Control
{
    public function __construct($argv)
    {
        unset($argv[0]);
        foreach ($argv as $key => $val) {
            switch ($val) {
                case '-s':
                    //开始执行队列任务
                    TaskQueue::start();
                    die();
                    break;
                case '-d':
                    TaskQueue::addTask('Stop');
                    die();
                    break;
                case '-c':
                    TaskQueue::addTask('RefreshConfigServer');
                    die();
                    break;
                case '-h':
                    new \non0\task_queue\support\Help();
                    die();
                    break;
                default:
                    Log::notice('不支持的参数:' . $val);
                    die();
                    break;
            }
        }
    }
}