<?php
/**
 * Created by PhpStorm.
 * User: yanlo
 * Date: 2018/8/2
 * Time: 16:17
 */

namespace non0\task_queue\Server;


use non0\task_queue\support\PDO;
use non0\task_queue\TaskQueue;
use PFinal\Database\Builder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UnitImportServer implements BaseServer
{
    /**
     * @var Builder
     */
    protected $db;

    /**
     * 数据库中任务id
     */
    protected $id;

    /**
     * 需要读取的文件路径
     * @var string
     */
    protected $filename;

    /**
     * @param $argc
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function main($argc)
    {
        /**
         * 获取$argc参数 应该是数组待判断
         * 然后根据 数组中的 filename 读取 ,读取后应该是json文件
         * 将json转数组并替换key,
         * filename 必须是完整路径
         * c:/wamp64/www/qc/public/upload/temp/20180707/1018.json
         *   /www/wwwroot/upload/1018.json
         */
        $this->filename = $argc['filename'];
        $this->db = new Builder(TaskQueue::getConfig('mysqli.'));
        $this->id = $taskId = $argc['id'];
        $this->db->table('sys_task_queue')->wherePk($taskId)->update(['status' => 1, 'update_time' => time(), 'result_value' => json_encode(['errmsg' => '正在执行'])]);
        if(file_exists($this->filename)){
            $data = $this->fileToArray($this->filename);
            if (is_array($data)) {
                return $this->dataInsertUnitAll($data);
            }
        }
        $this->db->table('sys_task_queue')->wherePk($taskId)->update(['status' => 3, 'update_time' => time(), 'result_value' => json_encode(['errmsg' => '读取文件转可操作数组失败'])]);
        return ['status' => false, 'errmsg' => '数据转数组失败'];
    }


    /**
     * 将数据插入数据库
     * @param $data
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function dataInsertUnitAll($data)
    {
        $result = [];
        foreach ($data as $key => $val) {
            $newval = $this->arrayChangeKey($val);
            $newval['status'] = 2;
            try {
                $this->db->table('unit')->insert($newval);
            } catch (\Exception $exception) {
                $newval['errmsg'] = $exception->getMessage();
                $result[] = $newval;
            }
        }

        if (empty($result)) {
            $this->db->table('sys_task_queue')->wherePk($this->id)->update(['status' => 2, 'update_time' => time(), 'result_value' => json_encode(['errmsg' => '执行成功'])]);
        } else {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getActiveSheet()->fromArray($result);
            $writer = new Xlsx($spreadsheet);
            $filename = dirname($this->filename).DIRECTORY_SEPARATOR.md5(microtime()).'.xlsx';
            $writer->save($filename);
            $this->db->table('sys_task_queue')->wherePk($this->id)->update(['status' => 2, 'update_time' => time(), 'result_value' => json_encode(['errmsg' => '执行完成,但部分数据失败.请检查.','filename'=>$filename])]);
        }
        return ['status' => true, 'errmsg' => empty($result) ? '执行成功' : '执行完成,但部分数据失败.请检查.'];
    }

    /**
     * 将几种格式文件转换成数组
     * @param $filename
     * @return bool|mixed
     */
    public function fileToArray($filename)
    {
        $extname = pathinfo($filename, PATHINFO_EXTENSION);
        switch ($extname) {
            case 'json':
                return json_decode(file_get_contents($filename), true);
                break;
            case 'db':

                break;
            case 'xlsx':

                break;
            default:
                return false;
                break;
        }
        return false;
    }

    /**
     * @param $data
     * @param string $type
     * @return mixed
     */
    function arrayChangeKey($data, $type = 'unit')
    {
        if (isset($data['IndustryNameData']))
            $newdata['industry_name_data'] = json_encode($data['IndustryNameData']);
        if (isset($data['VillageID']))
            $newdata['village_id'] = $data['VillageID'];
        if (isset($data['VillageIDExtend']))
            $newdata['village_id_extend'] = $data['VillageIDExtend'];
        if (isset($data['CreditID']))
            $newdata['credit_id'] = $data['CreditID'];
        if (isset($data['CreditIDExtend']))
            $newdata['credit_id_extend'] = $data['CreditIDExtend'];
        if (isset($data['OrganizationID']))
            $newdata['organization_id'] = $data['OrganizationID'];
        if (isset($data['OrganizationIDExtend']))
            $newdata['organization_id_extend'] = $data['OrganizationIDExtend'];
        if (isset($data['IdentificationID']))
            $newdata['identification_id'] = $data['IdentificationID'];
        if (isset($data['LicenceID']))
            $newdata['licence_id'] = $data['LicenceID'];
        if (isset($data['UnitName']))
            $newdata['unit_name'] = $data['UnitName'];
        if (isset($data['UsedName']))
            $newdata['used_name'] = $data['UsedName'];
        if (isset($data['RuningStatus']))
            $newdata['runing_status'] = $data['RuningStatus'];
        if (isset($data['RuningStatu1']))
            $newdata['runing_statu1'] = isset($data['RuningStatu1']);
        if (isset($data['AddressName']))
            $newdata['address_name'] = $data['AddressName'];
        if (isset($data['DoorNumber']))
            $newdata['door_number'] = $data['DoorNumber'];
        if (isset($data['Contacts']))
            $newdata['contacts'] = $data ['Contacts'];
        if (isset($data['FixedTel']))
            $newdata['fixed_tel'] = $data['FixedTel'];
        if (isset($data['MobilePhone']))
            $newdata['mobile_phone'] = $data ['MobilePhone'];
        if (isset($data['IndustryName']))
            $newdata['industry_name'] = $data['IndustryName'];
        if (isset($data['IndustryCode']))
            $newdata['industry_code'] = $data ['IndustryCode'];
        if (isset($data['IndustryResources']))
            $newdata['industry_resources'] = $data['IndustryResources'];
        if (isset($data['Minerals']))
            $newdata['minerals'] = json_encode($data['Minerals']);
        if (isset($data['IsNotFactory']))
            $newdata['is_not_factory'] = $data ['IsNotFactory'];
        if (isset($data['FactoryNum']))
            $newdata['factory_num'] = $data['FactoryNum'];
        if (isset($data['otherAddress']))
            $newdata['other_address'] = json_encode($data['otherAddress']);
        if (isset($data['Remarks']))
            $newdata['remarks'] = $data['Remarks'];
        if (isset($data['EnumeratorName']))
            $newdata['enumerator_name'] = $data['EnumeratorName'];
        if (isset($data['EnumeratorID']))
            $newdata['enumerator_id'] = $data['EnumeratorID'];
        if (isset($data['CreateTime']))
            $newdata['create_time'] = $data['CreateTime'];
        if (isset($data['ExamineName']))
            $newdata['examine_name'] = $data['ExamineName'];
        if (isset($data['ExamineID']))
            $newdata['examine_id'] = $data['ExamineID'];
        if (isset($data['ExamineTime']))
            $newdata['examine_time'] = $data['ExamineTime'];
        if (isset($data['Longitude']))
            $newdata['longitude'] = $data['Longitude'];
        if (isset($data['Longitude1']))
            $newdata['longitude1'] = $data['Longitude1'];
        if (isset($data['Longitude2']))
            $newdata['longitude2'] = $data['Longitude2'];
        if (isset($data['Latitude']))
            $newdata['latitude'] = $data ['Latitude'];
        if (isset($data['Latitude1']))
            $newdata['latitude1'] = $data['Latitude1'];
        if (isset($data['Latitude2']))
            $newdata['latitude2'] = $data['Latitude2'];
        if (isset($data['EnumeratorID_Whether']))
            $newdata['enumerator_id_whether'] = $data['EnumeratorID_Whether'];
        if (isset($data['_id']))
            $newdata['_id'] = $data['_id'];

        return $newdata;


    }
}
