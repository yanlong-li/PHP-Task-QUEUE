<?php
/**
 * Created by PhpStorm.
 * User: yanlo
 * Date: 2018/8/2
 * Time: 14:49
 */

do {
    $config = require 'config.php';
    echo $config['status'].PHP_EOL;
    var_dump($a);
    sleep(3);
} while (true);

//$redis = new Redis();
//$redis->pconnect('127.0.0.1','6379','100');
//$redis->select(5);
//$redis->set('hello','你好');
//var_dump($redis->keys('*'));