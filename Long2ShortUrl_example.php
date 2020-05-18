<?php
include_once 'src/businiao.lib/businiao.lib.php';
//require_once $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
$appid='12345678';
$appkey='GetAppKeyAtThe:https://www.wechaturl.us';
//这里有本api详细说明:https://wechaturl.gitbook.io/wechaturl/shorturl/long2shorturl
$url='https://yourdomain.com/anyurl?any_get=any_value#any_front';
//目前支持['wechaturl','sohuurl','ueeurl','weibourl','tencent_weibourl','is_gd_url']，默认情况会使用wechaturl；详细请到https://wechaturl.gitbook.io/wechaturl/the-third-shorturl了解他们的用途
$Long2ShortUrl_result=(new Long2ShortUrl($appid,$appkey))->get($url,'wechaturl');
print_r(json_decode($Long2ShortUrl_result,true));
