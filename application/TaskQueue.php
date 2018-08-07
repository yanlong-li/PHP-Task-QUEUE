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
        if (empty(self::$config))
            echo '配置初始化完成' . PHP_EOL;
        else
            echo '配置刷新成功' . PHP_EOL;
        self::$config = $config;
        self::$Redis = new support\Redis();
    }

    public static function start()
    {
        echo '启动自检' . PHP_EOL;
        if (self::checkStart()) {
            echo '自检完成,开起队列服务' . PHP_EOL;
            //将服务状态设置为开启
            self::$Redis->main->set('server', true);
            do {
                ImplementQueue::main();
                echo '线程休眠中，等待下次启动' . date(DATE_W3C, time()) . PHP_EOL;
                usleep((int)self::getConfig('queue.useelp'));
            } while (self::$Redis->main->get('server'));
            echo "服务成功关闭" . PHP_EOL;
        } else {
            echo '自检不通过,请主动检查' . PHP_EOL;
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