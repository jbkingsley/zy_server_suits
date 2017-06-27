<?php

namespace HandHeldTerminal\Models\Workflow;

class NodeData extends \DBWorker\DataMapper\Db\Data {
    // 使用哪个Mapper类
    static protected $mapper = 'HandHeldTerminal\Models\Workflow\NodeMapper';

    // Mapper类配置
    static protected $mapper_options = [
        'service' => 'mysql.adminDB',
        'collection' => 'workflow_node',
    ];

    // 数据定义
    static protected $attributes = [
        'node_id'  => ['type' => 'string', 'primary_key' => true],
        'defination_id'   => ['type' => 'integer'],
        'node_index'   => ['type' => 'integer'],
        'node_name'   => ['type' => 'string'],
        'node_type'   => ['type' => 'integer', 'allow_null' => true],
        'init_function'   => ['type' => 'string', 'allow_null' => true],
        'run_function'   => ['type' => 'string', 'allow_null' => true],
        'transi_function'   => ['type' => 'string', 'allow_null' => true],
        'prev_node_index'   => ['type' => 'integer'],
        'next_node_index'   => ['type' => 'integer', 'allow_null' => true],
        'executor'   => ['type' => 'string', 'allow_null' => true],
        'execute_type'   => ['type' => 'integer', 'allow_null' => true],
        'remind'   => ['type' => 'integer', 'allow_null' => true],
        'field'   => ['type' => 'string', 'allow_null' => true],
        'max_day'   => ['type' => 'integer', 'allow_null' => true],
    ];

    // 获取未安排计划的任务
    static function getNodeById(string $node_id): array
    {
        $select = self::select();
        $select->where('node_id = ?', $node_id);

        return $select->limit(1)->execute()->fetch();
    }

}