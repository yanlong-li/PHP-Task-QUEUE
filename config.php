<?php
/**
 * Created by PhpStorm.
 * User: Yanlongli
 * Date: 2018/8/2
 * Time: 15:31
 */

return [
    //队列
    'queue' => [
        'key' => 'non0tasklist',//任务队列key
        'timeout' => 60,//获取队列阻塞时常秒
        'useelp' => 10000,//休眠微秒
    ],
    'resultqueue' => [
        'key' => 'non0resultlist',
    ],
    'redis' => [
        'host' => '127.0.0.1',
        'port' => '6379',
        'db' => 5
    ],
    'mysqli' => [
        'dsn' => 'mysql:host=127.0.0.1;dbname=dsx_check',
        'username' => 'root',
        'password' => 'root',
        'charset' => 'utf8',
        'tablePrefix' => 'ck_',
    ],
    //日志
    'log' => array(
        'name' => 'non0.taskqueue',
        'level' => \Monolog\Logger::DEBUG,
        'file' => './runtime/log/log.log',
    ),
];