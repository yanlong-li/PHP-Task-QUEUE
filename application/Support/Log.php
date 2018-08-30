<?php

namespace non0\task_queue\support;


use non0\task_queue\TaskQueue;

/**
 * @method static debug($string)
 * @method static info($string)
 * @method static warning($string)
 * @method static error($string)
 * @method static notice($string)
 */
class Log
{
    /**
     * @var Logger
     */
    protected static $logger = null;

    private static function init()
    {
        if (static::$logger === null) {
            $config = TaskQueue::getConfig('log');
            static::$logger = new Logger($config);
        }
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        self::init();
        self::Trace($arguments, $name);
        return call_user_func_array(array(self::$logger, $name), $arguments);
    }

    /**
     * @param string|array|int|float $arguments
     * @param $level
     */
    public static function Trace($arguments, $level = '')
    {
        if (!is_array($arguments)) {
            $date = '[' . date('Y-m-d H:i:s', time()) . '] ';
            $level = !$level ? '' : '[' . $level . '] ';
            echo $date . $level . $arguments . PHP_EOL;
        } elseif (is_array($arguments)) {
            foreach ($arguments as $val) {
                self::Trace($val, $level);
            }
        } else {
            echo '无法打印的日志，请查看日志记录' . PHP_EOL;
        }


    }
}
