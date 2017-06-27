<?php
namespace HandHeldTerminal\Events;

use HandHeldTerminal\Models\Workflow\NodeData;
use HandHeldTerminal\Models\Workflow\ProcessData;
use HandHeldTerminal\Models\Workflow\ThreadData;
use \InterfaceWorker\Api;
use \InterfaceWorker\Functions;

use HandHeldTerminal\Subscribe;
use Channel;

class Calendar extends Api{

    public function getRules() {
        return array(
            'asyncThread' => array(
                'last_update_time' => array('name' => 'last_update_time', 'type' => 'int', 'require' => true, 'desc' => '最后同步更新的时间戳'),
                'executor' => array('name' => 'executor', 'type' => 'string', 'require' => true, 'desc' => '任务线程执行人'),
            )
        );
    }

    /**
     * 任务执行线程同步
     * @desc 与云端同步任务线程执行人的线程及状态
     */
    function asyncThread() {
        $last_update_time = Functions::DI()->request->get('last_update_time');
        $executor = Functions::DI()->request->get('executor');

        $response = Functions::DI()->response;

        $data = new ThreadData();
        $threads = $data::asyncThreadFromLastTime($last_update_time, $executor);
        if($threads){
            $node_ids = $process_ids = array();
            foreach($threads as $thread){
                $process_ids[] = $thread['process_ids'];
                $node_ids[] = $thread['node_id'];
            }

            if($process_ids and $node_ids){
                $process_arr = array();
                $process_ids = array_unique($process_ids);
                $processData = new ProcessData();
                foreach($process_ids as $process_id){
                    $process = $processData::getProcessById($process_id);
                    $process_arr[$process_id] = $process;
                }

                $node_arr = array();
                $node_ids = array_unique($node_ids);
                $nodeData = new NodeData();
                foreach($node_ids as $node_id){
                    $node = $nodeData::getNodeById($node_id);
                    $node_arr[$node_id] = $node;
                }

                foreach($threads as $key => $v){
                    $threads[$key]['process_desc'] = $process_arr[$threads[$key]['process_id']]['process_desc'];
                    $threads[$key]['node_name'] = $node_arr[$threads[$key]['node_id']]['node_name'];
                }
            }

            $response->setPayload($threads);
            $response->setMessageId(9100);
            return $response->ST_OK();
        }
    }

}