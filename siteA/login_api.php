<?php

/*
 * 让登陆页面在iframe中打开本文件
 * 跨域请求Set-Cookie
 */
header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
ini_set('session.save_handler', 'memcache');
ini_set('session.save_path', '127.0.0.1:11211');
session_start();
$site = $_SERVER['SERVER_NAME'];
if ($_SESSION['islogin'] === 'true') {
    echo $site, '重复登录，直接返回';
    exit(0);
}
if ($_GET['ticket'] && $_SERVER['SERVER_NAME']) {
    $ip = $_SERVER["REMOTE_ADDR"];
    $refer_info = parse_url($_SERVER["HTTP_REFERER"]);
    $login_site = $refer_info['host'];
    $ticket = $_GET['ticket'];
    //认证ticket
    $soap = new SoapClient(null, array("location" => "http://sitec.com:2221/soap_server.php", "uri" => "http://sitec.com:2221/"));
    $res = $soap->decryptTicket($ticket, $login_site, $ip);
    if (!$res['error'] && $res['sid']) {
        $sid = $res['sid'];
        #判断该用户是否在本数据库中也存在，存在即共享$sid
        $file_path = 'data.xml';
        $f = file_get_contents($file_path);
        $xml = new SimpleXMLElement($f);
        $username = $_GET['user'];
        $users = $xml->xpath("/users/user[name='{$username}']");
        $user = $users[0];
        if ($user) {
            $soap->__setCookie("PHPSESSID", $sid);
            if ($c = $soap->checkTickerExpire()) {
                setcookie("PHPSESSID", $sid);
                echo $site, '登录成功', $C;
            }
        }
    } else {
        echo $site, "登录失败", $res['error'];
    }
}