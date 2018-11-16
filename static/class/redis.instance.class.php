<?php

/**
 * Created by PhpStorm.
 * User: nigel
 * Date: 2018/10/12
 * Time: 15:29
 * todo:单例模式对redis实例的操作的进一步封装
 */
class redisManager
{

    private static $redisInstance;

    /**
     * 私有化构造函数
     * 原因：防止外界调用构造新的对象
     */
    private function __construct(){}

    /**
     * 获取redis连接的唯一出口
     */
    static public function getRedisInstance()
    {
        if(!self::$redisInstance instanceof self)
        {
            self::$redisInstance = new self;
        }
        return self::$redisInstance->connRedis();
    }

    /**
     * 连接ocean 上的redis的私有化方法
     * @return Redis
     */
    static private function connRedis()
    {
        try
        {
            include_once 'db.config.class.php';
            $redis_ocean = new Redis();
            $object = new \config\configClass();
            $redis_config = $object->loadRedisConfig();
            $redis_ocean->pconnect($redis_config['host'], $redis_config['port']);
            $redis_ocean->auth($redis_config['auth']);

        }
        catch (Exception $e)
        {
            echo $e->getMessage().'<br/>';
        }
        return $redis_ocean;
    }

}