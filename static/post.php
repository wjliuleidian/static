<?php
/**
 * Created by PhpStorm.
 * User: nigel
 * Date: 2018/10/12
 * Time: 15:29
 * todo:接受统计服务第一步，验证加密，发起非阻塞请求
 */
exit;
error_reporting(E_ALL);
ini_set('display_errors','On');

$a = array (
  "w"   =>"23_1_1541504281_1_0,24_2_1541504282_3_1",
  "p"   =>"0",
  "tp"  =>"2",
  "cl"  =>"1",
  "t"   =>"1541498697",
  "s"   =>"62f7b582a45e5cfe7b82e4a25d5f797b",
  "uid" =>"685",
  "eqid"=>"huawei-6P",
);
http_post(http_build_query($a));

function http_post($post_str)
{

    $srv_ip = 'ddzcount.ichaoren.com';//你的目标服务地址.

    $srv_port = 80;//端口

    $url = 'http://ddzcount.ichaoren.com/static.php'; //接收你post的URL具体地址

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





?>
