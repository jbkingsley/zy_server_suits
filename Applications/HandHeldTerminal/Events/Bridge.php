<?php
namespace HandHeldTerminal\Events;

use \InterfaceWorker\Api;
use \InterfaceWorker\Functions;

use GatewayWorker\Lib\Gateway;

class Bridge extends Api{

    public function getRules() {
        return array(
            'pushByAdminId' => array(
                'admin_id' => array('name' => 'admin_id', 'type' => 'int', 'require' => true, 'desc' => '用户ID'),
            )
        );
    }

    /**
     * 用户登录
     * @desc 用户登录作业APP及断线重连
     */
    function pushByAdminId() {
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

}