<?php
/**
 * Created by PhpStorm.
 * User: Yanlongli
 * Date: 2018/8/2
 * Time: 16:12
 * 任务接口类
 */

namespace non0\task_queue\server;


interface BaseServer
{
    /**
     * 服务入口(参数)
     * @param $argc
     * @return
     */
    public function main($argc);
}