<?php
/**
 * Created by PhpStorm.
 * User: nigel
 * Date: 2018/11/12
 * Time: 11:51
 * todo:监视redis队列数据，导入mysql
 */

//error_reporting(E_ALL);
//ini_set('display_errors','On');
set_time_limit(0);
include_once '../class/redis.instance.class.php';

$redis = RedisManager::getRedisInstance();//连接本地的 Redis 服务

$listKey = 'static_queue';//数据队列

$dataLength = $redis->lLen($listKey);

$mysql_num = 2000;

if ($dataLength >= $mysql_num)
{
    $redisInfo = $redis->lRange($listKey, 0, $mysql_num-1);//获取n个队列内容
    if (!empty($redisInfo))
    {
        try
        {
            $model = get_count_db_coon();
            $model->autocommit(false);
            $redis->watch($listKey);

            $sql = "INSERT INTO static_detail (`wid`, `pid` , `p_type`,  `p_type_id` , `count`, `uid` , `eqid` , `t`) VALUES ";

            foreach ($redisInfo as $key=>$val)
            {
                $pid = json_decode($val, true)['platform_id'] ?? 0;
                $uid = json_decode($val, true)['uid'] ?? 0;
                $eqid = json_decode($val, true)['eqid'] ?? 0;
                $data = explode(',',json_decode($val, true)['data']);//业务id
                foreach ($data as $k=>$v)
                {
                    $temp           = explode('_', $v);
                    $wid            = $temp[0] ?? 0;
                    $count          = $temp[1] ?? 0;
                    $time           = $temp[2] ?? 0;
                    $p_type         = $temp[3] ?? 0;
                    $p_type_id      = $temp[4] ?? 0;

                    $sql .= " ('" . $wid . "','" . $pid . "','" . $p_type . "','" . $p_type_id . "','" . $count. "','" . $uid. "','" . $eqid. "','" . $time. "'),";
                }
            }

            $insert_sql = rtrim($sql, ",") . ";";

            $insertResult = $model->query($insert_sql);

            if (!$insertResult)
            {
                $model->rollback();
                @write_log("[".date("Y-m-d H:i:s",time())."] \t errcode=>501");
            }

            $model->commit();

            $redis->lTrim($listKey, $mysql_num, -1);//删除处理完的队列信息

            mysqli_close($model);

        }
        catch (Exception $e)
        {
            $model->rollback();
            @write_log("[".date("Y-m-d H:i:s",time())."] \t errcode=>502");
        }
    }
}

//写日志
function write_log($data)
{
    $years = date('Y-m');
    $url = '/a8root/work/soft/nginx/nginx/logs/static_inser_mysql/'.$years.'/'.date('Ymd').'_inset_log.txt';
    $dir_name=dirname($url);
    if(!file_exists($dir_name))
    {
        mkdir(iconv("UTF-8", "GBK", $dir_name),0777,true);
    }
    $fp = fopen($url,"a");
    fwrite($fp,var_export($data,true)."\r\n");
    fclose($fp);
}

//连接count数据库
function get_count_db_coon()
{
    include_once '../class/db.config.class.php';
    $object = new \config\configClass();
    $dbConfig = $object->loadCountDbConfig();
    return mysqli_connect($dbConfig['host'],$dbConfig['user'],$dbConfig['pwd'],$dbConfig['dbname']);
}