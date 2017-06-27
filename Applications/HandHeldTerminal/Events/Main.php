<?php
namespace HandHeldTerminal\Events;

use \InterfaceWorker\Api;
use \InterfaceWorker\Functions;

use GatewayWorker\Lib\Gateway;

use HandHeldTerminal\Models\Admin\AdminData;

class Main extends Api{

    public function getRules() {
        return array(
            'login' => array(
                'admin_name' => array('name' => 'admin_name', 'type' => 'string', 'require' => true, 'desc' => '用户登录名'),
                'admin_pwd' => array('name' => 'admin_pwd', 'type' => 'string', 'require' => true, 'desc' => '用户密码'),
            )
        );
    }

    /**
     * 用户登录
     * @desc 用户登录作业APP及断线重连
     */
    function login() {
        $adminName = Functions::DI()->request->get('admin_name');
        $adminPassword = Functions::DI()->request->get('admin_pwd');

        //解绑已经绑定的admin_id
        $bind_ids = Gateway::getClientIdByUid($adminName);
        if (count($bind_ids) > 0) {
            foreach ($bind_ids as $id)
                Gateway::unbindUid($id, $adminName);
        }

        $client_id = Functions::DI()->currentClientId;

        //绑定新的连接
        Gateway::bindUid($client_id, $adminName);

        $response = Functions::DI()->response;

        $data = new AdminData;
        $admin = $data::checkAccount($adminName, $adminPassword);
        if($admin){
            $token = sha1($client_id.TOKEN_VERIFY_CODE);

            //保存在线用户
            $clientsUid = Functions::DI()->clientsUid;
            $clientsUid[$token] = array(
                'admin' => $admin,
                "client_id" => $client_id
            );
            Functions::DI()->clientsUid = $clientsUid;
            $response->setPayload(array('token'=>$token));
            $response->setMessageId(9001);
            return $response->ST_OK();
        }
        return $response->ST_ERROR(100003);
    }

    /**
     * 设备登出
     * @desc 用户点击注销功能
     */
    function logout() {

        $deviceId = Functions::DI()->request->get('dev_id');
        //解绑已经绑定的client_id
        $bind_id = Gateway::getClientIdByUid($deviceId);
        if (count($bind_id) > 0) {
            foreach ($bind_id as $v)
                Gateway::unbindUid($v, $deviceId);
        }

        $clientsUid = Functions::DI()->clientsUid;
        unset($clientsUid[$deviceId]);
        Functions::DI()->clientsUid = $clientsUid;

        $response = Functions::DI()->response;
        $response->setSt($response::R_VALID);
        $response->ST_OK();

    }

    /**
     * 心跳维持
     * @desc 用来保持作业APP和云平台的连接关系
     */
    function heart() {
        $response = Functions::DI()->response;
        $response->setSt($response::R_CONTENT);
        return $response->ST_OK();
    }

}