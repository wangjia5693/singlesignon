<?php
/**
 * Created by PhpStorm.
 * User: wangj@lizi.com
 * Date: 2016/6/21
 * Time: 10:07
 *
 * 提供登录服务接口
 * 此处各个站点的登录由soap_server统一登录，返回session_id和ticket；
 * 也可也根据情况稍加改变， 由各个站点自己认证登录，成功后发送session_id到此服务器
 * 确保所有站点的session共享
 */
ini_set('session.save_handler', 'memcache');
ini_set('session.save_path', '127.0.0.1:11211');
session_start();

//开启webserver,启动登录和认证服务
require_once 'Authorize.class.php';
$options = array(
    'location' => 'http://sitec.com:2221/soap_server.php',
    'uri' => 'http://sitec.com:2221/'
);
$wsdl = null;
$soap = new SoapServer($wsdl, $options);
$soap->setClass("Authorize");
$soap->handle();
