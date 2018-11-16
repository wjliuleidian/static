<?php

/**
 * Created by PhpStorm.
 * User: nigel
 * Date: 2018/10/12
 * Time: 15:29
 * todo:db配置类
 */
namespace config;

class configClass
{

    public $gmDbConfig;
    public $countDbConfig;
    public $countRedisConfig;

    public function __construct()
    {
        $this->setCountDbConfig();
        $this->setRedisConfig();
        $this->setGmDbConfig();
    }

    public function loadRedisConfig() : array
    {
        return $this->countRedisConfig;
    }

    public function loadCountDbConfig() : array
    {
        return $this->countDbConfig;
    }

    public function loadGmDbConfig() : array
    {
        return $this->gmDbConfig;
    }

    public function setGmDbConfig() : void
    {
        $this->gmDbConfig = array(
            'host'=>"rm-2ze85007z5893t5i1.mysql.rds.aliyuncs.com",
            'user'=>'lego_gm_user',
            'pwd'=>'iLdi8^q$Wz0p',
            'dbname'=>'lego_gm',
            'port'=>3306
        );
    }

    public function setRedisConfig() : void
    {
        $this->countRedisConfig = array(
            'host'=>"r-2ze77a378acf29a4.redis.rds.aliyuncs.com",
            'port'=>6379,
            'auth'=>'Sjk1n78Adx',
        );
    }
    public function setCountDbConfig() : void
    {
        $this->countDbConfig = array(
            'host'=>"rm-2ze112s812ol51lkj.mysql.rds.aliyuncs.com",
            'user'=>'ddz_user',
            'pwd'=>'Xkjqh8^2v$xA',
            'dbname'=>'lego_sys_sta',
            'port'=>3306
        );
    }
}