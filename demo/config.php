<?php
/**
 * Created by PhpStorm.
 * User: yanlo
 * Date: 2018/8/2
 * Time: 15:31
 */

return [
    //队列
    'queue' => [
        'key' => 'non0tasklist',//任务队列key
        'timeout' => 5,//获取队列阻塞时常秒
        'useelp' => 1000,//休眠微秒
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
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => '',
        'username' => 'root',
        'password' => 'root'
    ]
];