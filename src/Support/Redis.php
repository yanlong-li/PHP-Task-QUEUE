<?php
/**
 * Created by PhpStorm.
 * User: yanlo
 * Date: 2018/8/2
 * Time: 15:11
 */

namespace non0\task_queue\support;


use non0\task_queue\TaskQueue;

class Redis
{
    /**
     * @var Redis
     */
    public $main = \Redis::class;

    public function __construct()
    {
        $this->main = new \Redis();
        $this->main->connect(TaskQueue::getConfig('redis.host'));
        $this->main->select(5);
    }
}