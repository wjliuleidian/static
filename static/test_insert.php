<?php                                                                                                                                                                                                                                   
                                                                                                                                                                                                                                           
error_reporting(E_ALL);                                                                                                                                                                                                                 
ini_set('display_errors','On');

include_once './class/redis.instance.class.php';


//连接本地的 Redis 服务
$redis = RedisManager::getRedisInstance();

$listKey = 'static_queue_test';

//$mysql_num = 2;
$redisInfo = $redis->lRange($listKey, 0, -1);//获取n个队列内容

echo '<pre>';
print_r(array_reverse($redisInfo));exit;
