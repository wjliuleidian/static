<?php
/**
 * Created by PhpStorm.
 * User: nigel
 * Date: 2018/11/12
 * Time: 11:51
 * todo:mysql按日分表，数据导出，数据删除
 */
//error_reporting(E_ALL);
//ini_set('display_errors','On');
set_time_limit(0);

$day = date("Ymd",time()-86400);
$table_name = 'static_detail_'.$day;

// 使用 sql 按日期创建数据表
$sql = get_create_table_sql($table_name);
$model = get_count_db_coon();

if ($model->query($sql) === true)
{
    $log = '【'.date("Y-m-d H:i:s", time()).'】'. "table 【".$table_name."】created successfully";
}
else
{
    $log = '【'.date("Y-m-d H:i:s", time()).'】'.'create table: 【'.$table_name.'】' . $model->error;
}
@write_log($log);
// 导出数据
$begin_today = mktime(0,0,0,date('m'),date('d')-1,date('Y'));	//昨日开始时间
$end_today = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;	//昨日结束时间

//$model->autocommit(false);
$sql = "INSERT INTO $table_name SELECT * FROM static_detail WHERE t BETWEEN $begin_today AND $end_today;";
if ($model->query($sql) === true)
{
    $log = '【'.date("Y-m-d H:i:s", time()).'】'."Export Data Successfully";
    @write_log($log);
    // 删除源表中已经导出的数据
//    $count_sql = "SELECT COUNT(1) as count FROM $table_name WHERE t BETWEEN $begin_today AND $end_today";
//    $result = $model->query($count_sql);
//    $result = mysqli_fetch_row($result);
//    $n = 10;//分几批执行删除语句
//
//    if ($result)
//    {
//        $count = $result[0];
//        $chun_count = get_number($count, $n);//平均分n份,执行n次sql
//        if ($chun_count)
//        {
//            for($i=0;$i<=$n-1;$i++)
//            {
//                $sql = "DELETE FROM static_detail WHERE t BETWEEN $begin_today AND $end_today ORDER BY id LIMIT $chun_count[$i];";
//
//                if ($model->query($sql) === TRUE)
//                {
//                    $log = '【'.date("Y-m-d H:i:s", time()).'】'. "delete data successfully";
//                }
//                else
//                {
//                    $model->rollback();
//                    $log = '【'.date("Y-m-d H:i:s", time()).'】'. 'delete data : 【'.$table_name.'】' . $model->error."【".$i."】";
//                }
//                write_log($log);
//            }
//        }
//    }
    //$model->commit();
}
else
{
    $log = '【'.date("Y-m-d H:i:s", time()).'】'.'export data : 【'.$table_name.'】' . $model->error;
    //$model->rollback();
}


$model->close();

//连接count数据库
function get_local_coon()
{
    return mysqli_connect('localhost','root','root','test');
}
//连接count数据库
function get_count_db_coon()
{
    include_once '../class/db.config.class.php';
    $object = new \config\configClass();
    $dbConfig = $object->loadCountDbConfig();
    return mysqli_connect($dbConfig['host'],$dbConfig['user'],$dbConfig['pwd'],$dbConfig['dbname']);
}

/* 一个数字平分为N等份
* @param int $number 待平分的数字
* @param int $taotl 平分总个数
* @param int $index 保留小数位
*/
function get_number($number, $total)
{
    $divide_number  = bcdiv($number, $total);// 除法取平均数
    $last_number = bcsub($number, $divide_number*($total-1));// 减法获取最后一个数
    $number_str = str_repeat($divide_number.',', $total-1).$last_number;// 拼装平分后的数据返回
    return explode(',', $number_str);
}

//根据日期返回创建表的sql
function get_create_table_sql($table_name)
{
    return "CREATE TABLE IF NOT EXISTS $table_name (
      `id` bigint(11) NOT NULL,
      `wid` int(11) DEFAULT NULL,
      `pid` int(11) DEFAULT NULL,
      `p_type` int(11) DEFAULT NULL,
      `p_type_id` int(11) DEFAULT '0',
      `count` int(11) DEFAULT NULL,
      `uid` int(11) DEFAULT NULL,
      `eqid` varchar(500) CHARACTER SET latin1 DEFAULT NULL,
      `t` int(10) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
}

//写日志
function write_log($data)
{
    $years = date('Y-m');
    $url = '/a8root/work/soft/nginx/nginx/logs/static_inser_mysql/'.$years.'/'.date('Ymd').'export_log.txt';
    $dir_name = dirname($url);
    if(!file_exists($dir_name))
    {
        mkdir(iconv("UTF-8", "GBK", $dir_name),0777,true);
    }
    $fp = fopen($url,"a");
    fwrite($fp,var_export($data,true)."\r\n");
    fclose($fp);
}


?>