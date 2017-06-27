<?php
/**
 * Created by PhpStorm.
 * User: Deby
 * Date: 16/10/29
 * Time: 下午2:54
 */

namespace DeviceCloudService\Models\Sample;

class SampleSourceData extends \DBWorker\DataMapper\Db\Data {
    // 使用哪个Mapper类
    static protected $mapper = 'DeviceCloudService\Models\Sample\SampleSourceMapper';

    // Mapper类配置
    static protected $mapper_options = [
        'service' => 'adminDb',
        'collection' => 'com_admin',
    ];

    // 数据定义
    static protected $attributes = [
        'sample_id'   => ['type' => 'char', 'primary_key' => true],
        'device_id'   => ['type' => 'char', 'primary_key' => true],
        'timestamp'   => ['type' => 'datetime',  'format' =>'Y-m-d H:i:s', 'primary_key' => true],
        'collect_lat'   => ['type' => 'float'],
        'collect_lng'   => ['type' => 'float'],
        'collect_value'   => ['type' => 'text']
    ];

    // 自定义方法
    public function foobar() {

    }
}