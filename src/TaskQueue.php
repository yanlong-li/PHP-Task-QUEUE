<?php
/**
 * Created by PhpStorm.
 * User: yanlo
 * Date: 2018/8/2
 * Time: 15:07
 */

namespace non0\task_queue;

use non0\task_queue\support\Redis;

class TaskQueue
{
    /**
     * @var array =['redis']
     */
    public static $config = array();

    /**
     * @var Redis
     */
    public static $Redis = null;

    public static function init($config)
    {
        //任务不过期
        ini_set('max_execution_time', '0');
        self::$config = $config;
        self::$Redis = new support\Redis();
    }

    public static function start()
    {
        if (self::checkStart()) {
            //将服务状态设置为开启
            self::$Redis->main->set('server', true);
            do {
                usleep((int)self::getConfig('queue.useelp'));
                ImplementQueue::main();
                echo '线程休眠中，等待下次启动'.date(DATE_W3C,time()).PHP_EOL;
            } while (self::$Redis->main->get('server'));
            echo self::$Redis->main->get('server').PHP_EOL;
        } else {
            echo 'checkStart不通过';
            return false;
        }
    }

    /**
     * Check the starting condition
     */
    private static function checkStart()
    {
        $check = true;
        $check = !empty(self::$config) ? $check : false;
        $check = self::$Redis ? $check : false;
        return $check;
    }

    /**
     * 获取配置参数
     * 兼容 key.key
     * key. 获取key下的所有数据 value
     * @param $name
     * @param null $default
     * @param null $config
     * @return array|mixed|null
     */
    public static function getConfig($name, $default = null, $config = null)
    {
        if ($config == null) {
            $config = self::$config;
        }
        $name = explode('.', $name);
        if (count($name) == 1) {
            if (trim($name[0]) == '') return $config;
            return isset($config[$name[0]]) ? $config[$name[0]] : $default;
        } else {
            if (isset($config[$name[0]])) {
                $newname = $name[0];
                unset($name[0]);
                $name = implode('.', $name);
            }
            return self::getConfig($name, null, $config[$newname]);
        }
    }

    /**
     * @param $name
     * @param $value
     */
    public static function setConfig($name, $value)
    {
        self::$config[$name] = $value;
    }

}