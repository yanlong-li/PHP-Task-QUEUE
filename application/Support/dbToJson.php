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
//    function arrayToJson($data)
//    {
//        if (is_array($data))
//            return json_encode($data);
//        return $data;
//    }

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
//            foreach ($arr as $key => $val) {
//                $arr[$key] = $this->arrayToJson($val);
//            }
            $data[] = $arr;
        }
        fclose($fh);
        return $data;
    }

}
//$c = new dbToJson();
//$param_arr = getopt('i:o:');
//if(isset($param_arr['i']) && isset($param_arr['o']))
//$c->start($param_arr['i'],$param_arr['o']);
//else
//    echo "请设定-i 输入文件路径，-o 输出文件路径";