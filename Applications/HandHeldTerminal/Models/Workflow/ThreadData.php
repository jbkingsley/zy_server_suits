<?php

namespace HandHeldTerminal\Models\Workflow;

class ThreadData extends \DBWorker\DataMapper\Db\Data {
    // 使用哪个Mapper类
    static protected $mapper = 'HandHeldTerminal\Models\Workflow\ThreadMapper';

    // Mapper类配置
    static protected $mapper_options = [
        'service' => 'mysql.adminDB',
        'collection' => 'workflow_thread',
    ];

    // 数据定义
    static protected $attributes = [
        'thread_id'  => ['type' => 'char', 'primary_key' => true],
        'process_id'   => ['type' => 'char'],
        'node_id'   => ['type' => 'char'],
        'executor'   => ['type' => 'string'],
        'plan_time'   => ['type' => 'datetime', 'allow_null' => true],
        'receive_time'   => ['type' => 'datetime', 'allow_null' => true],
        'finish_time'   => ['type' => 'datetime', 'allow_null' => true],
        'max_time'   => ['type' => 'datetime', 'allow_null' => true],
        'state'   => ['type' => 'integer'],
    ];

    // 获取未安排计划的任务
    static function getUnplannedTask(string $executor): array
    {
        $select = self::select();
        $select->where('plan_time = ?', '0000-00-00 00:00:00')->where('executor=?', $executor);

        return $select->execute()->fetch();
    }

    // 自定义方法
    static function getThreadByDay(string $date, string $executor): array
    {
        $time = strtotime($date);
        $start_time = date('Y-m-d H:i:s', strtotime($time));
        $end_time = date('Y-m-d H:i:s', strtotime($time+86400));
        $select = self::select();
        $select->where('plan_time >= ?', $start_time)->where('plan_time < ?', $end_time)->where('executor=?', $executor);

        return $select->execute()->fetch();
    }

    // 自定义方法
    static function asyncThreadFromLastTime(int $last_time, string $executor): array
    {
        $select = self::select();
        $select->where('last_update_time > ?', $last_time)->where('executor=?', $executor);
        return $select->execute()->fetch();
    }
}