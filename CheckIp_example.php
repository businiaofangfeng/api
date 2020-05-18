<?php
header("Content-Type: text/html; charset=utf-8");
include_once 'src/businiao.lib/businiao.lib.php';
//require_once $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
$appid='12345678';
$appkey='GetAppKeyAtThe:https://www.wechaturl.us'; 
//本功能的作用是屏蔽厂商的云端检测功能(我们强烈建议你的网站使用https协议，至于什么原因详细查看class CheckIp()方法)
//本功能完全兼容世界上主流的CDN
//如果你的网站是多页面,为了让某个用户访问时候只检测一次,所以我们需要启动session;如果你的session有问题，你可以自己动手改成cookie
//这里有对api详细的介绍https://wechaturl.gitbook.io/wechaturl/check_ip
session_start();
$CheckIpResult=null;
if(!isset($_SESSION['CheckIpResult'])){
    $CheckIp=new CheckIp($appid,$appkey);
    $data=$CheckIp->CheckIp();//当然你在括号内,填写任意ip地址,可以测试效果 如这个ip:101.227.139.6
    $CheckIpResult=json_decode($data,true);
    if(!empty($CheckIpResult)){
        if(is_numeric($CheckIpResult['code']) and $CheckIpResult['code']>1){
            exit($CheckIpResult['message']);
        }
        if(is_numeric($CheckIpResult['code'])){
            $_SESSION['CheckIpResult']=$CheckIpResult;
            setcookie("phonix_ipcheck", $data, time()+1800);
        }
    }
}else{
    $CheckIpResult=$_SESSION['CheckIpResult'];
}
/******debug--正式使用时候,请注释掉print_r($CheckIpResult)*************/
print_r($CheckIpResult);
/******debug--正式使用时候,请注释掉上面的print_r($CheckIpResult)*************/
if($CheckIpResult['code']==1){
    //here,show  error page
    //http_response_code(404);
    //exit('page no found!');
    if(isset($CheckIpResult['show_error_url'])){
        exit(file_get_contents($CheckIpResult['show_error_url']));//当然,你可以自己写个网址
    }else{
        exit('page no found!');
    }
}


//下面就是你的代码
//echo '这里写你的代码吧';
?>
<?php 
//如果你的是静态单页面网站或者vue单页面,如./default.html,那使用下面方法
//echo file_get_contents('./default.html')
?>


