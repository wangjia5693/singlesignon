<?php
/**
 * Created by PhpStorm.
 * User: wangj@lizi.com
 * Date: 2016/6/21
 * Time: 10:07
 *
 * 查看mem中的session
 */
 $host='127.0.0.1';
 $port=11211;
 $mem=new Memcache();
 $mem->connect($host,$port);
//$mem->flush();exit;
 $items=$mem->getExtendedStats ('items');
 //echo "<pre>";

 $items=$items["$host:$port"]['items'];
 var_export($items);
 $result=array();
 foreach($items as $key=>$values){

    $str=$mem->getExtendedStats ("cachedump",$key,0);
    $line=$str["$host:$port"];

    if( is_array($line) && count($line)>0){
        foreach($line as $k=>$value){
            $result[$key][$k]=$mem->get($k);
        }
    }
 }
 echo "<pre>";
 print_r($result);