<?php
//ini_set("display_errors", "On");
//error_reporting(E_ALL);

include_once './class/db.config.class.php';

$object = new \config\configClass();

$config = $object->loadGmDbConfig();

$model = mysqli_connect($config['host'],$config['user'],$config['pwd'],$config['dbname']);

if (mysqli_connect_errno($model))
{
    echo "connect mysql error: " . mysqli_connect_error();exit;
}

include_once './class/redis.instance.class.php';
$redis = RedisManager::getRedisInstance();//连接本地的 Redis 服务
$urls = $redis->get('work_list');
if ($urls && !empty($urls))
{
    echo implode(',' , json_decode($urls , true));
}
else
{
    $query = "SELECT * FROM `lg_point` WHERE status = 1";
    if ($result = $model->query($query))
    {
        while ( $row  =  $result -> fetch_assoc ())
        {
            $urls[] =  $row["id"].'_'.$row['platform_id'].'_'.$row['point_type'].'_'.$row['type'].'_'.$row['check_level'];
        }
        if($urls)
        {
            $redis->set('work_list',json_encode($urls));
            $redis->expire('work_list' , 3600);
            echo implode(',' , $urls);
        }
        $result -> free ();
    }
}




$model -> close ();



?>