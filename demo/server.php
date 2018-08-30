<?php
/**
 * Created by PhpStorm.
 * User: Yanlongli
 * Date: 2018/8/6
 * Time: 11:01
 */
echo '服务开启成功:0.0.0.0:81';
popen("php -S 0.0.0.0:81",'r');