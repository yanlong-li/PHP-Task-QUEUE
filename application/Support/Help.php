<?php
/**
 * Created by PhpStorm.
 * User: Yanlongli
 * Date: 2018/8/2
 * Time: 15:11
 */

namespace non0\task_queue\support;


use non0\task_queue\TaskQueue;

class Help
{

    public function __construct()
    {
        $help =  <<<html

========================================================
‖          Yanlongli TaskQueue [版本 2.0.3]          ‖
‖(c) 2018-08-30 Yanlongli Corporation。保留所有权利。‖
========================================================
-s or default Start Server;
-d Stop Server;
-c Refresh Config;
-h Help;

html;
        Log::Trace($help,'help');
    }
}