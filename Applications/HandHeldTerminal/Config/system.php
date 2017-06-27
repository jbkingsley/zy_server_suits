<?php
/**
 * Created by PhpStorm.
 * User: Deby
 * Date: 2017/6/1
 * Time: 下午4:52
 */

return array(
    /**
     * Default Environment Config
     */
    'debug' => false,
    /**
     * MC Cache Config
     */
    'memcache' => array(
        'host' => '127.0.0.1',
        'port' => 11211,
        'object' => '\SlightPHP\Cache_MemcacheObject'
    ),
    /**
     * Encryption
     */
    'crypt' => array(
        'mcrypt_iv' => '12345678',      // 8 characters
    ),
);