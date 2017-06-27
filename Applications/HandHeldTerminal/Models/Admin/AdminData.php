<?php

namespace HandHeldTerminal\Models\Admin;

class AdminData extends \DBWorker\DataMapper\Db\Data {
    // 使用哪个Mapper类
    static protected $mapper = 'HandHeldTerminal\Models\Admin\AdminMapper';

    // Mapper类配置
    static protected $mapper_options = [
        'service' => 'mysql.adminDB',
        'collection' => 'com_admin',
    ];

    // 数据定义
    static protected $attributes = [
        'admin_id'         => ['type' => 'integer', 'primary_key' => true, 'auto_generate' => true],
        'token'      => ['type' => 'string', 'allow_null' => true],
        'group_id'   => ['type' => 'integer'],
        'enterprise_id'   => ['type' => 'string'],
        'admin_name'   => ['type' => 'string'],
        'front_name'   => ['type' => 'string', 'allow_null' => true],
        'admin_pwd'   => ['type' => 'string'],
        'ensure_pwd'   => ['type' => 'string', 'allow_null' => true],
        'email'   => ['type' => 'string', 'allow_null' => true],
        'avatar'   => ['type' => 'string', 'allow_null' => true],
        'mobile'   => ['type' => 'string', 'allow_null' => true],
        'qq'   => ['type' => 'string', 'allow_null' => true],
        'region_province'   => ['type' => 'integer'],
        'region_city'   => ['type' => 'integer'],
        'lng'   => ['type' => 'string', 'allow_null' => true],
        'lat'   => ['type' => 'string', 'allow_null' => true],
        'ip'   => ['type' => 'string', 'allow_null' => true],
        'createtime'   => ['type' => 'datetime', 'format' => 'Y-m-d'],
        'last_login_time'   => ['type' => 'datetime', 'allow_null' => true, 'format' => 'Y-m-d'],
        'last_update_time'   => ['type' => 'datetime', 'allow_null' => true, 'format' => 'Y-m-d'],
        'state'   => ['type' => 'integer'],
    ];

    // 自定义方法
    static function checkAccount(string $admin_name, string $admin_pwd): array
    {
        $select = self::select();
        $select->where('admin_name = ?', $admin_name)->where('admin_pwd = ?', md5($admin_pwd.PWD_VERIFY_CODE));

        return $select->limit(1)->execute()->fetch();
    }
}
