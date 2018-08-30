<?php
/**
 * Created by PhpStorm.
 * User: Yanlongli
 * Date: 2018/7/11/0011
 * Time: 下午 1:27
 * APPLICATION:
 */

/**
 * Class jsonToArray
 * db 文件转 json 文件
 */
namespace non0\task_queue\support;

class dbToJson
{

    /**
     * @param $string
     * @param string $outFile
     * @return bool
     */
    function start($string,$outFile = 'test.txt')
    {
        if (!file_exists($string))
            return false;
        $arr = $this->readFileByLine($string);
        $myfile = fopen($outFile, "w");
        fwrite($myfile, json_encode($arr));
        fclose($myfile);
        return true;
    }

    function readFileByLine($filename)
    {
        $fh = fopen($filename, 'r');
        $data = [];
        while (!feof($fh)) {
            $line = fgets($fh);
            $arr = json_decode($line,true);
            if(!is_array($arr))
                continue;
            $data[] = $arr;
        }
        fclose($fh);
        return $data;
    }

}