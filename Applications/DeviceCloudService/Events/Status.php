<?php
/**
 * 设备云端管理
 * @author KINGS
 */
namespace DeviceCloudService\Events;

use InstrumentProtocols\Protocol;

use InterfaceWorker\Api;
use InterfaceWorker\Functions;

use GatewayWorker\Lib\Gateway;

use DeviceCloudService\Models\Device\DeviceData;

class Status extends Api{

    public function getRules() {
        return array(
            'data' => array(
                'deviceId' => array('name' => 'dev_id', 'type' => 'string', 'require' => true, 'desc' => '用户ID'),
                'payload' => array('name' => 'payload', 'type' => 'string', 'require' => true, 'desc' => '测量数据负载'),
            ),
        );
    }

    /**
     * 数据上报
     * @desc 采集设备连入云平台后上报数据
     */
    function data() {
        $client_id = Functions::DI()->currentClientId;
        $readCache = 'ReadCache'.$client_id;

        if(Functions::DI()->$readCache){
            Functions::DI()->$readCache .= Functions::DI()->request->get('payload');
        }else{
            Functions::DI()->$readCache  = Functions::DI()->request->get('payload');
        }

        var_dump(Functions::DI()->$readCache );

        $protocol = new Protocol();
        $protocol->setInstrument('AwaProtocol');
        $protocol->setReadCache(Functions::DI(), $readCache);

        $content = $protocol->baseRead();

        if($content){
            var_dump($content);
        }else{
            echo '********';
        }

    }

}