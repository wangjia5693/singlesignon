<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
/**
 * 模拟一个站点，首页，登录两个页面，如果未登录直接跳到登录页面，如果已登录，将跳转到首页，并现实登录信息
 * 
 */
function main() {
    ini_set('session.save_handler', 'memcache');
    ini_set('session.save_path', '127.0.0.1:11211');
    session_start();
    $action = is_string($_REQUEST['act']) ? $_REQUEST['act'] : 'show_index';
    switch ($action) {
        case "show_index" :
            show_index();
            break;
        case 'do_login' :
            do_login();
            break;
        case 'show_login' :
            show_login();
            break;
        case 'do_logout':
            do_logout();
            break;
        default :
            show_index();
    }
}

main();

function show_index() {
    if (empty($_SESSION['islogin']) || $_SESSION['islogin'] == 'false') {
        header('location:index.php?act=show_login');
    }
    echo '<html><head><script type="text/javascript" src="http://sitec.com:2221/sso.php?uname='.$_SESSION['uname'].'"></script></head><body>';
    $sid = session_id();
    echo "welcome {$_SESSION['uname']},your id is {$_SESSION['uid']},password is {$_SESSION['upassword']},sessionid is {$sid}";
    echo " ,ip is {$_SESSION['ip']} , from {$_SESSION['login_site']}";
    echo '<a href="?act=do_logout">[logout]</a>';

    echo '</body></html>';
}

function do_login() {
    $username = is_string($_REQUEST['username']) ? $_REQUEST['username'] : '';
    $password = is_string($_REQUEST['password']) ? $_REQUEST['password'] : '';

    if ($username == '' || $password == '') {
        echo 'bad username or password !';
        return;
    }
    //确保这里给的site_name要和sso_script提供的login_site一致，否则会导致认证失败
    $site_name = $_SERVER['SERVER_NAME'];
    $ip = $_SERVER["REMOTE_ADDR"];
    //开启sopa客户端，无wsdl模式
    $wsdl = null;
    $options = array(
        'location' => 'http://sitec.com:2221/soap_server.php',
        'uri' => 'http://sitec.com:2221/'
    );
    $soap = new SoapClient($wsdl, $options);
    $result = $soap->login($username, $password, $site_name, $ip);
    if ($result['error']) {
        echo $result['error'];
    } else {
        setcookie('PHPSESSID', $result['sessid']);
        //如果是跳转到某个页面，将ticket保存到cookie,手动添加sso_script脚本
        //如果是ajax可以直接返回 $result['sso_sript'];登录成功后加载这个jsonp即可
        setcookie('ticket', $result['ticket']);
        header('location:index.php?act=show_index');
    }
}

function show_login() {
    if ($_SESSION['islogin'] == 'true') {
        header("location:index.php");
    }
    header("contentType:text/html;charset=utf-8");
    $html = <<<HTML
<!DOCTYPE html>
<html> 
    <head>
        <meta charset="" />
        <title></title>
        <script type="text/javascript" src="http://sitec.com:2221/sso.php"></script>
    </head>
    <body>
        <div>
            <form method="post" action="">
                <input type="hidden" value="do_login" name="act" />
                <input type="text"  name="username" value=""/>
                <input type="password" name="password"  value=""/>
                <input type="submit" name="submit" value="submit" />
            </form>
        </div>
    </body>
</html>	
HTML;
    echo $html;
}

function do_logout() {
    $_SESSION['islogin'] = 'false';
    session_destroy();//销毁session或者标识用户状态，达到单点退出的目的,共享的原因
    header('location:index.php?act=show_login');
}
