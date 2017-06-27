<?php
/**
 * noise data capture
 * User: Deby
 * Date: 16/10/29
 * Time: 下午2:54
 */

namespace Models\Device;

use \InterfaceWorker\DataMapper\Db\DbData;

class NoiseData extends DbData {
    // 使用哪个Mapper类
    static protected $mapper = '\Models\Device\NoiseMapper';

    // Mapper类配置
    static protected $mapper_options = [
        'service' => 'adminDb',
        'collection' => 'com_admin',
    ];

    // 数据定义
    static protected $attributes = [
        'timestamp'  => ['type' => 'datetime', 'primary_key' => true, 'format' => 'Y-m-d H:i:s'],
        'task_id'    => ['type' => 'string'],
        'device_id'      => ['type' => 'string'],
        'lng'   => ['type' => 'number', 'allow_null' => true],
        'lat'   => ['type' => 'number', 'allow_null' => true],
        'data' => ['type' => 'jason', 'allow_null' => true],
    ];

}
