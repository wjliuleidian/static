<?php
/**
 * Created by PhpStorm.
 * User: nigel
 * Date: 2018/10/12
 * Time: 15:29
 * todo:接受统计服务第一步，验证加密，消息入队
 */

//error_reporting(E_ALL);
//ini_set('display_errors','On');


if ($_POST)
{
    $time = $_POST['t'];
    $signature = $_POST['s'];
    $secret = "Tj2018&&^ReAB";
    if(arithmetic($time, $secret) != $signature)
    {
        exit('message 1');
    }
    else
    {
        push_redis($_POST);
        exit('message 2');
    }
}
else
{
    exit('message 0');
}

//消息入队
function push_redis($data)
{
    include_once './class/redis.instance.class.php';
    $params = array(
        'data'          =>  htmlspecialchars($data['w']),
        'platform_id'   =>  (int)$data['p'],
        'uid'           =>  (int)$data['uid'],
        'eqid'          =>  htmlspecialchars($data['eqid'])
    );

    //连接本地的 Redis 服务
    $redis = redisManager::getRedisInstance();
    $day = date("Ymd",time());
    $redis->incr('static_count_'.$day);
    //$redis->watch('static_queue');
    $llen = $redis->rPush('static_queue', json_encode($params)); //执行成功后返回当前列表的长度
    if (!$llen)
    {
        $redis->incr('static_count_error'.$day);
        $log = '【'.date("Y-m-d H:i:s", time()).'】'.'push redis error data : 【'.htmlspecialchars($data['w']).'】';
        write_log($log);
    }
    //$redis->exec();
}

//写日志
function write_log($data)
{
    $years = date('Y-m');
    $url = '/a8root/work/soft/nginx/nginx/logs/static_inser_mysql/'.$years.'/'.date('Ymd').'push_redis_log.txt';
    $dir_name = dirname($url);
    if(!file_exists($dir_name))
    {
        mkdir(iconv("UTF-8", "GBK", $dir_name),0777,true);
    }
    $fp = fopen($url,"a");
    fwrite($fp,var_export($data,true)."\r\n");
    fclose($fp);
}

/**
 * http请求支持post
 * @param $url
 * @param int $timeout
 * @param string $ip
 * @param string $cookie
 * @return array|string
 */
function http_post($post_str, $srv_ip, $url)
{

    $srv_port = 80;//端口

    $fp = '';

    $errno = 0;//错误处理

    $errstr = '';//错误处理

    $timeout = 30;//多久没有连上就中断

    //打开网络的 Socket 链接。

    $fp = fsockopen($srv_ip,$srv_port,$errno,$errstr,$timeout);

    if (!$fp)
    {
        echo('fp fail');
    }
    //拼接http协议头
    $content_length = strlen($post_str);

    $post_header = "POST $url HTTP/1.1\r\n";

    $post_header .= "Content-Type: application/x-www-form-urlencoded\r\n";

    $post_header .= "User-Agent: MSIE\r\n";

    $post_header .= "Host: ".$srv_ip."\r\n";

    $post_header .= "Content-Length: ".$content_length."\r\n";

    $post_header .= "Connection: close\r\n\r\n";

    $post_header .= $post_str."\r\n\r\n";

    fwrite($fp,$post_header);

    $inheader = 0;

    while(!feof($fp))
    {//测试文件指针是否到了文件结束的位置

        $line = fgets($fp,1024);//去掉请求包的头信息
        if ($inheader && ($line == "n" || $line == "rn"))
        {
            $inheader = 0;
        }
        else
        {
            echo $line;
        }
    }

    fclose($fp);

    // unset ($line);
}

/**
 * @param $timeStamp    时间戳
 * @return string       返回签名
 */
function arithmetic($timeStamp, $secret)
{
    $arr['timeStamp'] = $timeStamp;
    $arr['secret'] = $secret;
    $signature = md5(md5(implode($arr)));//进行加密
    return $signature;
}

/**
 * @param $time    时间戳
 * @return bool
 */
function checkTimeOut($time)
{
    $timeDiff = time() - $time;
    return $timeDiff > 86400 ? 0 : 1;
}


/**
 * 非阻塞http请求支持GET
 * @param $url
 * @param int $timeout
 * @param string $ip
 * @param string $cookie
 * @return array|string
 */
function http_get($url,$timeout = 10,$ip = '',$cookie = '')
{

    $return = '';
    $uri = parse_url($url);

    isset($uri['host']) ||$uri['host'] = '';
    isset($uri['path']) || $uri['path'] = '';
    isset($uri['query']) || $uri['query'] = '';
    isset($uri['port']) || $uri['port'] = '';

    $host = $uri['host'];
    $path = $uri['path'] ? $uri['path'] . ($uri['query'] ? '?' . $uri['query'] : '') : '/';
    $port = !empty($uri['port']) ? $uri['port'] : 80;

    $out = "GET $path HTTP/1.0\r\n";
    $out .= "Accept: */*\r\n";
    $out .= "Accept-Language: zh-cn\r\n";
    $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
    $out .= "Host: $host\r\n";
    $out .= "Connection: Close\r\n";
    $out .= "Cookie: $cookie\r\n\r\n";
    try
    {
        $fp = fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
        if (!$fp)
        {
            return ['retCode' => -1,'errMsg' => $errstr.':'.$errno];
        }
        else
        {
            stream_set_blocking($fp, 0);#非阻塞模式
            //设置流的超时时间
            stream_set_timeout($fp, $timeout);
            @fwrite($fp, $out);
            @fclose($fp);
            return $return;
        }
    }
    catch (\Exception $e)
    {
        return ['retCode' => -1,'errMsg' => $e->getMessage()];
    }
}

?>
