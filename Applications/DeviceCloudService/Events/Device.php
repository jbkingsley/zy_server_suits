<?php
/**
 * 设备云端管理
 * @author KINGS
 */
namespace DeviceCloudService\Events;

use InterfaceWorker\Api;

use GatewayWorker\Lib\Gateway;
use InterfaceWorker\Functions;
use Models\Device\DeviceData;

use Channel;

class Device extends Api{

    public function getRules() {
        return array(
            'active' => array(
                'macAddress' => array('name' => 'mac', 'type' => 'string', 'require' => true, 'desc' => '采集设备硬件MAC地址'),
                'deviceSn' => array('name' => 'sn', 'type' => 'string', 'require' => true, 'desc' => '采集设备硬件序列号'),
                'version' => array('name' => 'version', 'type' => 'string', 'require' => true, 'desc' => '采集设备硬件版本'),
            ),
            'login' => array(
                'deviceId' => array('name' => 'dev_id', 'type' => 'string', 'require' => true, 'desc' => '采集设备ID'),
                'version' => array('name' => 'version', 'type' => 'string', 'require' => true, 'desc' => '采集设备硬件版本'),
            ),
            'offline' => array(
                'deviceId' => array('name' => 'dev_id', 'type' => 'string', 'require' => true, 'desc' => '采集设备ID'),
            ),
            'heart' => array(
                'deviceId' => array('name' => 'dev_id', 'type' => 'string', 'require' => true, 'desc' => '用户ID'),
            ),
        );
    }

    /**
     * 激活设备
     * @desc 采集设备第一次使用时向云平台申请激活
     * @return string id 设备ID
     */
    function register() {
        $response = Functions::DI()->response;
        $response->setSt($response::R_CREATED);
        $response->setPayload(array('id'=>'1234567890'));
        $response->ST_OK();
    }
    /**
     * 设备登录
     * @desc 采集设备连入云平台后执行登录
     */
    function login() {
        $deviceId = Functions::DI()->request->get('dev_id');
        //解绑已经绑定的client_id
        $bind_id = Gateway::getClientIdByUid($deviceId);
        if (count($bind_id) > 0) {
            foreach ($bind_id as $v)
                Gateway::unbindUid($v, $deviceId);
        }

        //绑定新的连接
        $client_id = Functions::DI()->currentClientId;
        Gateway::bindUid($client_id, $deviceId);

        //publish一个连接采集器消息
        $event_name = '_NOTIFY_OF_COLLECTOR_STATE_CHANGE_';
        $event_data = array('client_id'=>Functions::DI()->currentClientId, 'device_id'=>$device_id, 'device_state'=>$device_state);
        Channel\Client::publish($event_name, $event_data);

        //$data = new DeviceData;
        //$data::find('1');
        $response = Functions::DI()->response;
        $response->setSt($response::R_VALID);
        $response->ST_OK();
    }

    /**
     * 设备登出
     * @desc 采集设备与云平台断开连接
     */
    function offline() {

        $deviceId = Functions::DI()->request->get('dev_id');
        //解绑已经绑定的client_id
        $bind_id = Gateway::getClientIdByUid($deviceId);
        if (count($bind_id) > 0) {
            foreach ($bind_id as $v)
                Gateway::unbindUid($v, $deviceId);
        }

        $response = Functions::DI()->response;
        $response->setSt($response::R_VALID);
        $response->ST_OK();

    }

    /**
     * 心跳维持
     * @desc 用来保持采集设备和云平台的连接关系
     */
    function heart() {
        $response = Functions::DI()->response;
        $response->setSt($response::R_CONTENT);
        return $response->ST_OK();
    }


}