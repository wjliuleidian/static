<?php
/**
 * 模拟埋点请求
 * User: nigel
 * Date: 2018/10/12
 * Time: 16:14
 */
exit;
//模拟前台请求服务器api接口
function getDataFromServer()
{
    //时间戳
    $timeStamp = time();
    //生成签名
    $signature = arithmetic($timeStamp);
    //url地址
    $url = "http://ddzcount.ichaoren.com/static.php?w=1&p=1&tp=1&cl=1&t=$timeStamp&s=$signature&uid=10001&eqid=abcdefg";
    echo $url;exit;
}

/**
 * @param $timeStamp 时间戳
 * @param $randomStr 随机字符串
 * @return string 返回签名
 */
function arithmetic($timeStamp)
{
    $arr['timeStamp'] = $timeStamp;
    $arr['secret'] = 'Tj2018&&^ReAB';
    $signature = md5(md5(implode($arr)));//进行加密
    return $signature;
}

getDataFromServer();