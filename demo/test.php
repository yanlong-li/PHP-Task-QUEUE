<?php
/**
 * Created by PhpStorm.
 * User: Yanlongli
 * Date: 2018/8/6
 * Time: 10:58
 */

include_once "../vendor/autoload.php";
try {

    $new  = new \non0\task_queue\Server\UnitImportServer();
    $data = $new->fileToArray("C:\\Users\\yanlo\\Desktop\\新建文件夹\\市政入河（海）排污口清查表-总表-201887112793.xlsx");
    preg_match_all('/\d+/',$data[1]['dlzbls'],$arr);
    var_dump($arr);
    var_dump($data);
//    var_dump($data['dlzbls']);
} catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
} catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
}