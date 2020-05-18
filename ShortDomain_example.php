<?php
include_once 'src/businiao.lib/businiao.lib.php';
//require_once $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
$appid='12345678';
$appkey='GetAppKeyAtThe:https://www.wechaturl.us';
//本api对应会员中心：https://www.wechaturl.us/user/index.html#business_management/user_short_domain_list
//这里有本api详细说明:https://wechaturl.gitbook.io/wechaturl/shorturl/user_short_domain_list

$ShortDomain_result=(new ShortDomain($appid,$appkey))->lists('layer_top');
print_r(json_decode($ShortDomain_result,true));