<?php
/**
 * Created by PhpStorm.
 * User: Yanlongli
 * Date: 2018/8/6
 * Time: 14:28
 */

namespace non0\task_queue\support;


use PhpOffice\PhpSpreadsheet\Exception;

class ExcelToArray
{
    /**
     * @param $filename
     * @param array $map
     * @param int $titleRow
     * @param int $beginColumn
     * @return array|bool
     */
    function ExcelToArray($filename, $map = [], $titleRow = 1, $beginColumn = 1)
    {
        $inputFileName = $filename;
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(\PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName));
            $spreadsheet = $reader->load($inputFileName);
            $shell = $spreadsheet->getSheet(0);
            $cols = $shell->getHighestColumn();
            $cols = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($cols);
            $rows = $shell->getHighestRow();
            $data = [];
            $highestColumnIndex = $cols;
            $title = array();
            if ($titleRow > 0) {
                for ($cols = $beginColumn; $cols <= $highestColumnIndex; $cols++) {
                    $val = $shell->getCellByColumnAndRow($cols, $titleRow)->getValue();

                    $field = array_search($val, $map);
                    if ($field === false) {
                        $field = trim($val);
                    }

                    $title[] = $field;

                }
            }
            $data = array();
            $row = 1;
            for ($currentRow = $titleRow + 1; $currentRow <= $rows; $currentRow++) {
                $i = 0;
                for ($cols = $beginColumn; $cols <= $highestColumnIndex; $cols++) {

                    $val = $shell->getCellByColumnAndRow($cols, $currentRow)->getValue();//原始值

                    $field = isset($title[$i]) ? $title[$i] : $i;
                    $data[$row][$field] = trim($val);
                    $i++;
                }
                $row++;
            }
            return $data;
        } catch
        (Exception $exception) {
            return false;
        }
    }
}