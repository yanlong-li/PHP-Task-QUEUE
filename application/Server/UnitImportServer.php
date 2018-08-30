<?php
/**
 * Created by PhpStorm.
 * User: Yanlongli
 * Date: 2018/8/2
 * Time: 16:17
 */

namespace non0\task_queue\server;


use non0\task_queue\support\dbToJson;
use non0\task_queue\Support\ExcelToArray;
use non0\task_queue\support\PDO;
use non0\task_queue\TaskQueue;
use PFinal\Database\Builder;
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
     * 服务类型
     */
    protected $type;

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
        $this->type = $argc['type'];
        $this->db->table('sys_task_queue')->wherePk($taskId)->update(['status' => 1, 'update_time' => time(), 'result_value' => json_encode(['errmsg' => '正在执行'],JSON_UNESCAPED_UNICODE)]);
        if (file_exists($this->filename)) {
            $data = $this->fileToArray($this->filename);
            if (is_array($data)) {
                return $this->dataInsertUnitAll($data);
            }
        }
        $this->db->table('sys_task_queue')->wherePk($taskId)->update(['status' => 3, 'update_time' => time(), 'result_value' => json_encode(['errmsg' => '读取文件转可操作数组失败'],JSON_UNESCAPED_UNICODE)]);
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
        if (!$this->type) {
            $this->db->table('sys_task_queue')->wherePk($this->id)->update(['status' => 3, 'update_time' => time(), 'result_value' => json_encode(['errmsg' => '未定义的数据类型，无法使用'],JSON_UNESCAPED_UNICODE)]);
            return ['status' => false, 'message' => '未知的类型'];
        }
        $result = [];
        //开启事务
        $this->db->getConnection()->beginTransaction();
        foreach ($data as $key => $val) {
            $newval = $this->arrayChangeKey($val);
            $newval['status'] = 2;
            try {
                $this->db->table($this->type)->insert($newval);
            } catch (\Exception $exception) {
                $newval['errmsg'] = $exception->getMessage();
                $result[] = $newval;
            }
        }

        if (empty($result)) {
            $this->db->table('sys_task_queue')->wherePk($this->id)->update(['status' => 2, 'update_time' => time(), 'result_value' => json_encode(['errmsg' => '执行成功'],JSON_UNESCAPED_UNICODE)]);
        } else {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getActiveSheet()->fromArray($result);
            $writer = new Xlsx($spreadsheet);
            $filename = dirname($this->filename) . DIRECTORY_SEPARATOR . md5(microtime()) . '.xlsx';
            $writer->save($filename);
            $this->db->table('sys_task_queue')->wherePk($this->id)->update(['status' => 2, 'update_time' => time(), 'result_value' => json_encode(['errmsg' => '执行完成,但部分数据失败.请检查.', 'filename' => $filename],JSON_UNESCAPED_UNICODE)]);
        }
        //提交事务
        $this->db->getConnection()->commit();
        return ['status' => true, 'errmsg' => empty($result) ? '执行成功' : '执行完成,但部分数据失败.请检查.'];
    }

    /**
     * 将几种格式文件转换成数组
     * @param $filename string 文件名
     * @param null $fileType
     * @return bool|mixed
     */
    public function fileToArray($filename, $fileType = null)
    {
        $extname = $fileType ?: pathinfo($filename, PATHINFO_EXTENSION);

        switch (strtolower($extname)) {
            case 'json':
                return json_decode(file_get_contents($filename), true);
                break;
            case 'db':
                $dbToJson = new dbToJson();
                $tempFilename = tempnam(sys_get_temp_dir(), 'json');
                $result = $dbToJson->start($filename, $tempFilename);
                if ($result) {
                    return $this->fileToArray($tempFilename, 'json');
                }
                return false;
                break;
            case 'xlsx':
                $excelToArray = new ExcelToArray();
                return $excelToArray->ExcelToArray($filename, $this->excelTitleRow());
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
        $newdata = [];
        if (isset($data['IndustryNameData']))
            $newdata['industry_name_data'] = json_encode($data['IndustryNameData'], JSON_UNESCAPED_UNICODE);
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
        if (isset($data['UnitName']))//单位名称
            $newdata['unit_name'] = $data['UnitName'];

        if (isset($data['UsedName']))
            $newdata['used_name'] = $data['UsedName'];
        if (isset($data['RuningStatus']))
            $newdata['runing_status'] = $data['RuningStatus'];
        if (isset($data['RuningStatu1']))
            $newdata['runing_statu1'] = json_encode($data['RuningStatu1'], JSON_UNESCAPED_UNICODE);
        if (isset($data['AddressName']))
            $newdata['address_name'] = $data['AddressName'];

        if (isset($data['省 (自治区、直辖市)'])) {
            $newdata['address_name'] = (!isset($data['省 (自治区、直辖市)']) ? '' : $data['省 (自治区、直辖市)']) . (!isset($data['地区 (市、州、盟)']) ? '' : $data['地区 (市、州、盟)']) . (!isset($data['县 (区、市、旗)']) ? '' : $data['县 (区、市、旗)']) . (!isset($data['乡 (镇)']) ? '' : $data['乡 (镇)']) . (!isset($data['详细地址 (街、村)']) ? '' : $data['详细地址 (街、村)']);
        }

        if (isset($data['DoorNumber'])) {
            $newdata['door_number'] = $data['DoorNumber'];
            if (strtolower(substr($newdata['door_number'], -6)) == '号号') {
                $newdata['door_number'] = substr($newdata['door_number'], 0, -3);
            }
            if (substr($newdata['door_number'], 0, 1) == '-') {
                $newdata['door_number'] = substr($newdata['door_number'], 1);
            }
        }

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
            $newdata['minerals'] = json_encode($data['Minerals'], JSON_UNESCAPED_UNICODE);
        if (isset($data['IsNotFactory']))
            $newdata['is_not_factory'] = $data ['IsNotFactory'];
        if (isset($data['FactoryNum']))
            $newdata['factory_num'] = $data['FactoryNum'];
        if (isset($data['otherAddress']))
            $newdata['other_address'] = json_encode($data['otherAddress'], JSON_UNESCAPED_UNICODE);
        if (isset($data['Remarks']))
            $newdata['remarks'] = $data['Remarks'];
        if (isset($data['EnumeratorName']))
            $newdata['enumerator_name'] = $data['EnumeratorName'];
        if (isset($data['EnumeratorID'])) {
            //拆分编号中包含的姓名
            if (!preg_match("/^\d*$/", $data['EnumeratorID'])) {
                $newdata['enumerator_id'] = explode('-', $data['enumerator_id'])[0];
                $newdata['enumerator_name'] = explode('-', $data['enumerator_id'])[1];
            } else {
                $newdata['enumerator_id'] = $data['EnumeratorID'];
            }
        }
        if (isset($data['CreateTime'])) {
            if (is_array($data['CreateTime'])) {
                $newdata['create_time'] = substr($data['CreateTime']['$$date'], 0, -3);
            } else {
                if (preg_match("/^\d*$/", $data['CreateTime'])) {
                    $newdata['create_time'] = substr($data['CreateTime'], -3);//毫秒转秒
                } else {
                    $newdata['create_time'] = strtotime($data['CreateTime']);
                }
            }
        }
        if (isset($data['ExamineName']))
            $newdata['examine_name'] = $data['ExamineName'];
        //审核员
        if (isset($data['ExamineID'])) {
            //拆分编号中包含的姓名
            if (!preg_match("/^\d*$/", $data['ExamineID'])) {
                $newdata['examine_id'] = explode('-', $data['ExamineID'])[0];
                $newdata['examine_name'] = explode('-', $data['ExamineID'])[1];
            } else {
                $newdata['examine_id'] = $data['ExamineID'];
            }
        }
        if (isset($data['ExamineTime'])) {
            if (is_array($data['ExamineTime'])) {
                $newdata['examine_time'] = substr($data['ExamineTime']['$$date'], 0, -3);
            } else {
                if (preg_match("/^\d*$/", $data['ExamineTime'])) {
                    $newdata['examine_time'] = substr($data['ExamineTime'], 0, -3);
                } else {
                    $newdata['examine_time'] = strtotime($data['ExamineTime']);
                }
            }
        }
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

        //////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// farm
        if (isset($data['BeefCattle'])) {
            $newdata['beef_cattle'] = $data['BeefCattle'];
        }
        if (isset($data['Broiler'])) {
            $newdata['broiler'] = $data['Broiler'];
        }
        if (isset($data['Cow'])) {
            $newdata['cow'] = $data['Cow'];
        }
        if (isset($data['Layer'])) {
            $newdata['layer'] = $data['Layer'];
        }
        if (isset($data['pigs'])) {
            $newdata['pigs'] = $data['pigs'];
        }
        //////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// facilities

        if (isset($data['Operation']))//运营单位名称  集中式  独有
            $newdata['operation'] = $data['Operation'];
        if (isset($data['category']))//设施分类  集中式  独有
            $newdata['category'] = $data['category'];
        if (isset($data['focus']))//是否农村  集中式  独有
            $newdata['focus'] = $data['focus'];
        if (isset($data['ability']))//是否农村  集中式  独有
            $newdata['ability'] = $data['ability'];
        if (isset($data['population']))//是否农村  集中式  独有
            $newdata['population'] = $data['population'];
        if (isset($data['family']))//是否农村  集中式  独有
            $newdata['family'] = $data['family'];
        //////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// boiler
        if (isset($data['PropertyUnitOfBoiler']))//运营单位名称  集中式  独有
            $newdata['property_unit_of_boiler'] = $data['PropertyUnitOfBoiler'];

        if (isset($data['coordinate0']))//经纬度 锅炉 独有
            $newdata['longitude'] = $data['coordinate0'];
        if (isset($data['coordinate1']))//经纬度 锅炉 独有
            $newdata['longitude1'] = $data['coordinate1'];
        if (isset($data['coordinate2']))//经纬度 锅炉 独有
            $newdata['longitude2'] = $data['coordinate2'];
        if (isset($data['coordinate3']))//经纬度 锅炉 独有
            $newdata['latitude'] = $data['coordinate3'];
        if (isset($data['coordinate4']))//经纬度 锅炉 独有
            $newdata['latitude1'] = $data['coordinate4'];
        if (isset($data['coordinate5']))//经纬度 锅炉 独有
            $newdata['latitude2'] = $data['coordinate5'];
        if (isset($data['coordinate0']))//经纬度 锅炉 独有
            $newdata['coordinate0'] = $data['coordinate0'];
        if (isset($data['coordinate1']))//经纬度 锅炉 独有
            $newdata['coordinate1'] = $data['coordinate1'];
        if (isset($data['coordinate2']))//经纬度 锅炉 独有
            $newdata['coordinate2'] = $data['coordinate2'];
        if (isset($data['coordinate3']))//经纬度 锅炉 独有
            $newdata['coordinate3'] = $data['coordinate3'];
        if (isset($data['coordinate4']))//经纬度 锅炉 独有
            $newdata['coordinate4'] = $data['coordinate4'];
        if (isset($data['coordinate5']))//经纬度 锅炉 独有
            $newdata['coordinate5'] = $data['coordinate5'];

        if(isset($data['dlzbls'])){
            preg_match_all('/\d+/',$data['dlzbls'],$arr);
            $arr = $arr[0];
            $newdata['coordinate0'] = $arr[0];
            $newdata['coordinate1'] = $arr[1];
            $newdata['coordinate2'] = $arr[2];
            $newdata['coordinate3'] = $arr[3];
            $newdata['coordinate4'] = $arr[4];
            $newdata['coordinate5'] = $arr[5];
        }

        if (isset($data['Province']))//省
            $newdata['province'] = $data['Province'];
        if (isset($data['City']))//市
            $newdata['city'] = $data['City'];
        if (isset($data['County']))//县区
            $newdata['county'] = $data['County'];
        if (isset($data['Country']))//镇
            $newdata['country'] = $data['Country'];
        if (isset($data['Street']))//村居委会
            $newdata['street'] = $data['Street'];


        if (isset($data['MechanismType']))//企业类型
            $newdata['mechanism_type'] = $data['MechanismType'];
        if (isset($data['BoilerQuantity']))//锅炉数量
            $newdata['boiler_quantity'] = $data['BoilerQuantity'];
        if (isset($data['GL']))//锅炉数量
            $newdata['gl'] = json_encode($data['GL'], JSON_UNESCAPED_UNICODE);
        if (isset($data['BH']))//保护
            $newdata['bh'] = json_encode($data['BH'], JSON_UNESCAPED_UNICODE);
        //////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///
        if (isset($data['BlowdownOutletID']))//排污口编号
            $newdata['blowdown_outlet_id'] = $data['BlowdownOutletID'];
        if (isset($data['BlowdownOutleName']))//排污口名称
            $newdata['blowdown_outlet_name'] = $data['BlowdownOutleName'];
        if (isset($data['BlowdownOutleCategory']))//排污口类别
            $newdata['blowdown_outlet_category'] = $data['BlowdownOutleCategory'];
        if (isset($data['SettingUnit']))//设置单位
            $newdata['setting_unit'] = $data['SettingUnit'];
        if (isset($data['TypeOfBlowdown']))//排污口类型
            $newdata['type_of_blowdown'] = $data['TypeOfBlowdown'];
        if (isset($data['ScaleOfBlowdown']))//排污口规模
            $newdata['scale_of_blowdown'] = $data['ScaleOfBlowdown'];
        if (isset($data['WaterEntryMode']))//入河（海）方式
            $newdata['water_entry_mode'] = $data['WaterEntryMode'];
        if (isset($data['WaterName']))//受纳水体名称
            $newdata['water_name'] = $data['WaterName'];
        if (isset($data['Other']))//其他  当选择入河方式未其他时填写  新增other1字段但未使用，这里也不使用
            $newdata['other'] = $data['Other'];
        return $newdata;


    }

    function excelTitleRow()
    {
        $data = [];
//        $data['IndustryNameData'] = ;
        $data['VillageID'] = '普查小区编号';
//        $data['VillageIDExtend'];
        $data['CreditID'] = '统一社会信用代码';
//        $data['CreditIDExtend'];
        $data['OrganizationID'] = '组织机构代码';
//        $data['OrganizationIDExtend'];
        $data['IdentificationID'] = '普查对象识别码';
        $data['LicenceID'] = '排污许可证编号';
        $data['UnitName'] = '单位名称';
        $data['UsedName'] = '曾用名';
        $data['RuningStatus'] = '运行状态';
//        $data['RuningStatu1'] = '省 (自治区、直辖市)';
//        $data['AddressName'] = '地区 (市、州、盟)';
        $data['DoorNumber'] = '详细地址（街、村、门牌号）';
        $data ['Contacts'] = '联系人';
        $data['FixedTel'] = '固定电话';
        $data ['MobilePhone'] = '移动电话';
        $data['IndustryName'] = '行业类别';
        $data ['IndustryCode'] = '行业类别';//待正则处理数字
        $data['IndustryResources'] = '是否涉及稀土等15类矿产资源开采、选矿、冶炼（分离）、加工';
//        $data['Minerals'];
//        $data ['IsNotFactory'];
        $data['FactoryNum'] = '其他厂址个数';
        $data['otherAddress'] = '其他厂址地址';
        $data['Remarks'] = '备注';
//        $data['EnumeratorName'];
//        $data['EnumeratorID'];
//        $data['CreateTime'];
//        $data['ExamineName'];
//        $data['ExamineID'];
//        $data['ExamineTime'];
//        $data['Longitude'];
//        $data['Longitude1'];
//        $data['Longitude2'];
//        $data ['Latitude'];
//        $data['Latitude1'];
//        $data['Latitude2'];
        $data['EnumeratorID_Whether'] = '是否纳入普查范围';
//        $data['_id'];
        //省(自治区、直辖市)	地区(市、州、盟)	县(区、市、旗)	乡(镇)	详细地址（街、村、门牌号）	联系人	固定电话	移动电话	行业类别	是否涉及稀土等15类矿产资源开采、选矿、冶炼（分离）、加工	其他厂址个数	其他厂址地址	备注	是否纳入普查范围


        ///
        ///
        ///
        if ($this->type === 'farm') {
            $data['UnitName'] = '养殖场名称';
            $data['VillageID'] = '普查小区代码';
            $data['CreditID'] = '统一信用代码';
            $data['DoorNumber'] = '门牌号';
            $data['pigs'] = '猪(头)';
            $data['Cow'] = '';
            $data['BeefCattle'] = '肉牛(头)';
            $data['Layer'] = '蛋鸡(羽)';
            $data['Broiler'] = '肉鸡(羽)';
        }
//        $this->type = 'facilities';
        if ($this->type === 'facilities') {
            $data['VillageID'] = '普查小区代码';
            $data['CreditID'] = '统一信用代码';
            $data['DoorNumber'] = '门牌号';
            $data['Operation'] = '运营单位';
            $data['RuningStatus'] = '运营状态';
            $data['category'] = '设施类别';
            $data['focus'] = '农村集中式污水处理设施';
            $data['ability'] = '设计处理能力(吨/日)';
            $data['population'] = '服务人口(人)';
            $data['family'] = '服务家庭(户)';
        }
        if ($this->type === 'blowdown_outlet') {
            $data['VillageID'] = '普查小区代码';
            $data['BlowdownOutletID'] = '排污口编码';
            $data['BlowdownOutleName'] = '排污口名称';
            $data['BlowdownOutleCategory'] = '排污口类别';
            $data['dlzbls'] = '地理坐标';//warning
            $data['SettingUnit'] = '设置单位';
            $data['ScaleOfBlowdown'] = '排污口规模';
            $data['TypeOfBlowdown'] = '排污口类型';
            $data['WaterEntryMode'] = '入河（海）方式';
            $data['Other'] = '其他';
            $data['WaterName'] = '受纳水体名称';
            $data['EnumeratorID'] = '普查员及编号';
            $data['CreateTime'] = '填表时间';
            $data['ExamineID'] = '审核人及编号';
            $data['ExamineTime'] = '审核时间';
        }
        return $data;
    }
}
