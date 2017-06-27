<?php

namespace HandHeldTerminal\Models\Workflow;

class ProcessData extends \DBWorker\DataMapper\Db\Data {
    // 使用哪个Mapper类
    static protected $mapper = 'HandHeldTerminal\Models\Workflow\ProcessMapper';

    // Mapper类配置
    static protected $mapper_options = [
        'service' => 'mysql.adminDB',
        'collection' => 'workflow_process',
    ];

    // 数据定义
    static protected $attributes = [
        'process_id'  => ['type' => 'string', 'primary_key' => true],
        'defination_id'   => ['type' => 'string'],
        'enterprise_id'   => ['type' => 'string'],
        'process_desc'   => ['type' => 'string'],
        'context'   => ['type' => 'text', 'allow_null' => true],
        'current_node_index'   => ['type' => 'datetime', 'allow_null' => true],
        'start_time'   => ['type' => 'datetime', 'allow_null' => true],
        'finish_time'   => ['type' => 'datetime', 'allow_null' => true],
        'state'   => ['type' => 'integer'],
        'start_user'   => ['type' => 'string', 'allow_null' => true],
    ];

    // 获取未安排计划的任务
    static function getProcessById(string $process_id): array
    {
        $select = self::select();
        $select->where('process_id = ?', $process_id);

        return $select->limit(1)->execute()->fetch();
    }

}