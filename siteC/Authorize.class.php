<?php
/*
 * Created by PhpStorm.
 * User: wangj@lizi.com
 * Date: 2016/6/21
 * Time: 10:07
 * 提供登录服务，此处模拟了简单的登录动作
 * 没有使用数据库，用户信息保存在一个xml文件中。当然实际开放中不能这么做。
 * 用户登陆服务
 */

class Authorize {
    const KEY = "$^&&*guh"; //ticket加密解密的密钥

    /**
     * 登录服务并返回多点登录的ticket
     * @param type $username 用户登录名或者id
     * @param type $password    登录密码
     * @param type $login_site 来源的站点(为防止加密解密失败，约定为 xxxx.xx(如:abc.com) 格式，自行处理)
     * @param type $ip  登录用户客户端IP
     * @return array  返回数组，error=>错误信息,'sessid'=>session id,'sso_script'=>多点登录接口脚本，ticket=>多点登录凭证
     */

    public function login($username = '', $password = '', $login_site = '', $ip = '') {
        if ($login_site == '' || $username == '' || $password == '') {
            return array();
        }
        //分配并获取session_id();
        $sid = session_id();
        //返回值结构
        $result = array(
            'error'=>'',  //错误信息
            'sessid' => $sid, //session id
            "sso_script" => 'http://sitec.com:2221/sso.php?ticket=', //多点登录的脚本地址
            'ticket'=>''
        );

        //连接数据库，可能根据登录站点不同，连接不同的数据库;
        $arr = array('sitea.com'=>'./data.xml','siteb.com'=>'./datab.xml','sitec.com'=>'./datac.xml',);
        $file_path = $arr[$login_site];
        if (!file_exists($file_path)) {
            $result['error'] = 'can not connect db';
            return $result;
        }
        $f = file_get_contents($file_path);
        $xml = new SimpleXMLElement($f);
        $users = $xml->xpath("/users/user[name='{$username}']");
        $user = $users[0];
        if ($user) {
            $uname = (string) $user->name;
            $upassword = (string) $user->password;
            if ($password != $upassword) {
                $result['error'] =  'username or password is wrong !';
                return $result;
            }
            $uid = (string) $user->attributes()->id;
            $_SESSION['uid'] = $uid;
            $_SESSION['uname'] = $uname;
            $_SESSION['upassword'] = $upassword;
            $_SESSION['islogin'] = 'true';
            $_SESSION['login_site'] = $login_site;
            $_SESSION['ip'] = $ip;
            $_SESSION['ticket_expire'] = 2;  //ticket登录计数器，获取需要登录的站点个数，每登录一个-1；<=0将不能使用
            //生成ticket
            require_once 'encrypt.php';
            $key = $ip . self::KEY . $login_site; //用ip和登录地址做密钥防止接口连接被复制粘贴;如果session周期太长，仍然是不安全的
            $ticket = encrypt($result['sessid'], "E", $key);
            $result['ticket'] = $ticket;
            $result['sso_script'] .= $ticket;
            return $result;
        }
    }
    /**
     * ticket解密并验证有效性
     * @param type $ticket
     * @param type $server_name
     * @return type array("sid")
     */
    public function decryptTicket($ticket = '', $login_site = '', $ip = '') {
        //返回值结构
        $res = array(
            "sid" => '',
            "error" => ''
        ); 
        if ($ticket == '' || $login_site == '') {
            $res['error'] = "tciket和login_site不能为空";
            return $res;
        }
        //解密ticket，获得session id后返回
        $key = $ip . self::KEY . $login_site;
        require_once 'encrypt.php';
        $sid = encrypt($ticket, "D", $key);
        if($sid){
           $res['sid'] = $sid;
        }
        return $res;
    }
    
    /**
     * 检查ticket是否有效
     * @return int 1为认证成功，0为失败
     */
    public function checkTickerExpire() {
        if ($_SESSION['ticket_expire'] && ($_SESSION['ticket_expire'] > 0)) {
            return 1;
        }
        return 0;
    }
}
