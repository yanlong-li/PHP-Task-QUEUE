<?php
/**
 * Created by PhpStorm.
 * User: Yanlongli
 * Date: 2018/8/2
 * Time: 15:07
 */

namespace non0\task_queue;

use non0\task_queue\support\Control;
use non0\task_queue\support\Log;
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

    /**
     * @param $config
     */
    public static function init($config)
    {
        //任务不过期
        ini_set('max_execution_time', '0');
        if (empty(self::$config))
            $log = ('配置初始化完成');
        else
            $log = ('配置刷新成功');
        self::$config = $config;
        Log::info($log);
        self::$Redis = new support\Redis();
    }

    public static function start($argv)
    {
        //动态调度控制
        new Control($argv);

        log::info('启动自检');
        if (self::checkStart()) {
            log::info('自检完成,开起队列服务');
            //将服务状态设置为开启
            self::$Redis->main->set('server', true);
            do {
                ImplementQueue::main();
                Log::Trace('线程休眠中，等待下次启动');

                //检测Redis是否连接正常
                if (!self::$Redis->main->ping()) {
                    Log::debug('Redis超时关闭');
                    Log::debug('尝试恢复Redis连接');
                    self::$Redis = new support\Redis();
                    if (!self::$Redis->main->ping()) {
                        Log::warning("Redis无法恢复，请检查Redis服务是否正常");
                    } else {
                        log::info("Redis已恢复,任务继续,推荐将睡眠时间减少");
                    }
                } else {
                    //正常则读取服务状态是否开启
                    if (self::$Redis->main->get('server')) {
                        usleep((int)self::getConfig('queue.useelp'));
                    }
                    //服务被设置为关闭，不进入休眠状态并自动退出循环
                }
            } while (self::$Redis->main->get('server'));
            log::info("服务成功关闭");
        } else {
            log::error("自检不通过,请主动检查配置文件");
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
    public static function getConfig($name = '', $default = null, $config = null)
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

    /**
     * @param string $ServerName
     * @param array $param
     */
    public static function addTask($ServerName, $param = [])
    {
        self::$Redis->main->rPush(TaskQueue::getConfig('queue.key'), json_encode(['name' => $ServerName, 'value' => $param]));
    }

}