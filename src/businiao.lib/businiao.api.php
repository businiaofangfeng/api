<?php

class BuSiNiaoApi{
    private $appid='';
    private $appkey='';
    private $url=BUSINIAO_API_HOST;
    function __construct($appid,$appkey){
        $this->appid=$appid;
        $this->appkey=$appkey;
        
    }
    function get_url($api_type){
        $this->url.=$api_type;
        $this->url.=sprintf('?appid=%s&appkey=%s',$this->appid,$this->appkey);
        return $this->url;
        
    }
}

class UrlCycleCheck{
    private $curl_url='';
    function __construct($appid,$appkey){
        $this->curl_url=(new BuSiNiaoApi($appid,$appkey))->get_url(BUSINIAO_API_TYPE_UrlCycleCheck);
        
    }
    /** 添加
     * 
     * **/
    function add($url,$frequency,$is_monitor=false){
        $postArr['type']='add';
        $postArr['url']=$url;
        $postArr['is_monitor']=$is_monitor;
        $postArr['frequency']=$frequency;
        return $this->curl($postArr);
    }
    function edit($url,$frequency=null,bool $is_monitor=null){
        $postArr['type']='edit';
        $postArr['url']=$url;
        $postArr['is_monitor']=$is_monitor;
        $postArr['frequency']=$frequency;
        return $this->curl($postArr);
    }
    function delete($url){
        $postArr['type']='delete';
        $postArr['url']=$url;
        return $this->curl($postArr);
    }
    function lists($page=1,$rows=10){
        $postArr['type']='list';
        $postArr['page']=$page;
        $postArr['rows']=$rows;
        return $this->curl($postArr);
    }
    function frequency(){
        $postArr['type']='frequency';
        return $this->curl($postArr);
    }
    function help(){
        $postArr['type']='help';
        return $this->curl($postArr);
    }
    private function curl($postArr){
        $curl=new ApiCurlLib($this->curl_url,[],$postArr);
        return $curl->curl();
    }
    
}
class CheckIp{
    private $curl_url='';
    function __construct($appid,$appkey){
        $this->curl_url=(new BuSiNiaoApi($appid,$appkey))->get_url(BUSINIAO_API_TYPE_CheckIp);
        
    }
    /**
     * $ip:ipv4
     * $search_range:可以是 360,tencent,jinshan,baidu,albaba;多个 请用逗号隔开。
     * 具体查看官方接口https://wechaturl.gitbook.io/wechaturl/check_ip
     */
    function CheckIp($ip=null,$search_range=''){
        if($ip==''){
            $ip=$this->get_real_client_ip();
        }else{
            $postArr['test_ip']=$ip;//为了避开more_header()判断准确问题，这个单独传一个test_ip
        }
        $postArr['ip']=$ip;
        
        if($search_range!=""){
            $postArr['search_range']=$search_range;
        }
        $postArr=array_merge($postArr,$this->more_header());
        return $this->curl($postArr);
    }
    //由于单独1个header已经无法准确获取真实ip。以下信息帮助我们精准获取访问者的ip地址
    private function more_header(){
        $post=[];
        $keys=[
            'HTTP_CF_VISITOR',//Cloudflare scheme
            'HTTP_CF_CONNECTING_IP',//Cloudflare
            'HTTP_X_REAL_IP',//nginx
            'HTTP_X_FORWARDED_FOR',//nginx
            'HTTP_X_CLIENT_SCHEME',//aliyun cdn scheme
            'HTTP_ALI_CDN_REAL_IP',//aliyun cdn ip
            'REMOTE_ADDR',//原生的ip
            'REQUEST_SCHEME',//原生scheme
            'HTTP_X_PHOENIX_SCHEME',//不死鸟自定义scheme
            'HTTP_CLIENTIP',//不死鸟自定义ip
            'HTTP_USER_AGENT',//user-agent
            'HTTP_REFERER'//来路
        ];
        foreach ($keys as $key){
            if(isset($_SERVER[$key])){
                $post[$key]=$_SERVER[$key];
            }
        }
        $post['current_url']=$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(isset($_COOKIE['phonix_ipcheck'])){
            $post['phonix_ipcheck']=$_COOKIE['phonix_ipcheck'];//为了防止部分客户没有使用session导致伺服器压力，因此同时我们也同时以cookies作为补充
        }
        
        return $post;
    }
    private function curl($postArr){
        $curl=new ApiCurlLib($this->curl_url,[],$postArr);
        return $curl->curl();
    }
    private function get_real_client_ip(){
        $real_client_ip=$_SERVER['REMOTE_ADDR'];
        $ng_client_ip=( isset($_SERVER['HTTP_X_FORWARDED_FOR'])  ?   $_SERVER['HTTP_X_FORWARDED_FOR']    :   "");//反向代理
        
        if(isset($_SERVER['HTTP_CLIENTIP'])){
            $real_client_ip=$_SERVER['HTTP_CLIENTIP'];
        }
        if($ng_client_ip!="" and strlen($ng_client_ip)>5){
            if(strstr($ng_client_ip, ',')!=""){
                $a=explode(',', $ng_client_ip);
                $real_client_ip=$a[0];
                if($this->check_intranet_ip($real_client_ip)){
                    if(isset($_SERVER['HTTP_X_REAL_IP'])){
                        $real_client_ip=$_SERVER['HTTP_X_REAL_IP'];
                    }
                }
            }else{
                $real_client_ip=$ng_client_ip;
            }
        }
        return $real_client_ip;
    }

    //检查下内网ip
    private function check_intranet_ip($ip){
        if(!IS_CHECK_INTRANET_IP){
            return ;
        }
        
        //排除google cloud自己的内网ip
        if(startWith($ip, '10.170.0.')){
            return ;
        }
        $ip_num_list=[
            [ip2long('192.168.0.0'),ip2long('192.168.255.255')],
            [ip2long('10.0.0.0'),ip2long('10.255.255.255')],
            [ip2long('172.16.0.0'),ip2long('172.31.255.255')],
            [ip2long('100.64.0.0'),ip2long('100.127.255.255')],
            [ip2long('127.0.0.0'),ip2long('127.255.255.255')]
        ];
        $ip_long=ip2long($ip);
        $find=false;
        foreach ($ip_num_list as $item){
            if($ip_long>=$item[0] AND $ip_long<=$item[1]){
                $find=true;
                break;
            }
        }
        if(!$find){
            return;
        }
        return $find;
    }
}

class SingleShortUrl{
    private $curl_url='';
    function __construct($appid,$appkey){
        $this->curl_url=(new BuSiNiaoApi($appid,$appkey))->get_url(BUSINIAO_API_TYPE_SingleShortUrl);
        
    }
    /** 添加
     *$visit_type值只能是:browser,frame,jump.默认 jump。如果你不知道它含义请到会员中心页面版查看
     * **/
    function add($url,$visit_type='jump',$title=null,$keywords=null,$description=null){
        $postArr['type']='add';
        $postArr['url']=$url;
        $postArr['visit_type']=$visit_type;
        
        if($title!=null){
            $postArr['title']=$title;
        }
        if($keywords!=null){
            $postArr['keywords']=$keywords;
        }
        if($description!=null){
            $postArr['description']=$description;
        }
        return $this->curl($postArr);
    }
    function edit($url,$visit_type=null,$title=null,$keywords=null,$description=null){
        $postArr['type']='edit';
        $postArr['url']=$url;
        if($visit_type!=null){
            $postArr['visit_type']=$visit_type;
        }
        if($title!=null){
            $postArr['title']=$title;
        }
        if($keywords!=null){
            $postArr['keywords']=$keywords;
        }
        if($description!=null){
            $postArr['description']=$description;
        }
        return $this->curl($postArr);
    }
    function delete($url_or_id){
        $postArr['type']='delete';
        if(is_numeric($url_or_id)){
            $postArr['id']=$url_or_id;
        }else{
            $postArr['url']=$url_or_id;
        }
        return $this->curl($postArr);
    }
    function lists($url=null,$page=1,$rows=10){
        $postArr['type']='list';
        if($url!=null){
            $postArr['url']=$url;
        }
        $postArr['page']=$page;
        $postArr['rows']=$rows;
        return $this->curl($postArr);
    }
    function help(){
        $postArr['type']='help';
        return $this->curl($postArr);
    }
    private function curl($postArr){
        $curl=new ApiCurlLib($this->curl_url,[],$postArr);
        return $curl->curl();
    }
    
}


class DomainShortUrl{
    private $curl_url='';
    function __construct($appid,$appkey){
        $this->curl_url=(new BuSiNiaoApi($appid,$appkey))->get_url(BUSINIAO_API_TYPE_DomainShortUrl);
        
    }
    function GetLandDomainList(){
        $postArr['type']='GetLandDomainList';
        return $this->curl($postArr);
    }
    /** 添加
     *$visit_type值只能是:browser,frame,jump.默认 jump。如果你不知道它含义请到会员中心页面版查看
     * **/
    function add($url,$visit_type='jump',$group_id=0,$title=null,$keywords=null,$description=null){
        $postArr['type']='add';
        $postArr['url']=$url;
        $postArr['visit_type']=$visit_type;
        $postArr['group_id']=$group_id;
        
        if($title!=null){
            $postArr['title']=$title;
        }
        if($keywords!=null){
            $postArr['keywords']=$keywords;
        }
        if($description!=null){
            $postArr['description']=$description;
        }
        return $this->curl($postArr);
    }
    function edit($url,$visit_type=null,$group_id=0,$title=null,$keywords=null,$description=null){
        $postArr['type']='edit';
        $postArr['url']=$url;
        $postArr['group_id']=$group_id;
        if($visit_type!=null){
            $postArr['visit_type']=$visit_type;
        }
        if($title!=null){
            $postArr['title']=$title;
        }
        if($keywords!=null){
            $postArr['keywords']=$keywords;
        }
        if($description!=null){
            $postArr['description']=$description;
        }
        return $this->curl($postArr);
    }
    function delete($url_or_id){
        $postArr['type']='delete';
        if(is_numeric($url_or_id)){
            $postArr['id']=$url_or_id;
        }else{
            $postArr['url']=$url_or_id;
        }
        
        return $this->curl($postArr);
    }
    function lists($url=null,$group_id=null,$page=1,$rows=10){
        $postArr['type']='list';
        if($url!=null){
            $postArr['url']=$url;
        }
        if(is_numeric($group_id)){
            $postArr['group_id']=$group_id;
        }
        
        $postArr['page']=$page;
        $postArr['rows']=$rows;
        return $this->curl($postArr);
    }
    function HighFrequencyCheck($url){
        $postArr['type']='HighFrequencyCheck';
        $postArr['url']=$url;
        return $this->curl($postArr);
    }
    function help(){
        $postArr['type']='help';
        return $this->curl($postArr);
    }
    private function curl($postArr){
        $curl=new ApiCurlLib($this->curl_url,[],$postArr);
        return $curl->curl();
    }
    
}
class UrlCheck{
    private $curl_url='';
    function __construct($appid,$appkey){
        $this->curl_url=(new BuSiNiaoApi($appid,$appkey))->get_url(BUSINIAO_API_TYPE_UrlCheck);
        
    }
    function get($url){
        $postArr['url']=$url;
        return $this->curl($postArr);
    }
    private function curl($postArr){
        $curl=new ApiCurlLib($this->curl_url,[],$postArr);
        return $curl->curl();
    }
}
/* class GetWechatShortUrl{
    private $curl_url='';
    function __construct($appid,$appkey){
        $this->curl_url=(new BuSiNiaoApi($appid,$appkey))->get_url(BUSINIAO_API_TYPE_GetWechatShortUrl);
        
    }

    function get($url){
        $postArr['url']=$url;
        return $this->curl($postArr);
    }
    private function curl($postArr){
        $curl=new ApiCurlLib($this->curl_url,[],$postArr);
        return $curl->curl();
    }
} */

/* class GetWeiboShortUrl{
    private $curl_url='';
    function __construct($appid,$appkey){
        $this->curl_url=(new BuSiNiaoApi($appid,$appkey))->get_url(BUSINIAO_API_TYPE_GetWeiboShortUrl);
        
    }

    function get($url){
        $postArr['url']=$url;
        return $this->curl($postArr);
    }

    private function curl($postArr){
        $curl=new ApiCurlLib($this->curl_url,[],$postArr);
        return $curl->curl();
    }
} */

class Long2ShortUrl{
    private $curl_url='';
    function __construct($appid,$appkey){
        $this->curl_url=(new BuSiNiaoApi($appid,$appkey))->get_url(BUSINIAO_API_TYPE_Long2ShortUrl);
        
    }
    
    function get($url,$entry_type='default'){
        $postArr['url']=$url;
        $postArr['entry_type']=$entry_type;
        return $this->curl($postArr);
    }
    
    private function curl($postArr){
        $curl=new ApiCurlLib($this->curl_url,[],$postArr);
        return $curl->curl();
    }
}

class ShortDomain{
    private $curl_url='';
    function __construct($appid,$appkey){
        $this->curl_url=(new BuSiNiaoApi($appid,$appkey))->get_url(BUSINIAO_API_TYPE_ShortDomain);
        
    }
    
    function lists($layer_type=null,$status=null,$page=1,$rows=50){
        $postArr['type']='list';
        if($layer_type!=""){
            $postArr['layer_type']=$layer_type;
        }
        if(is_numeric($status)){
            $postArr['status']=$status;
        }
        $postArr['page']=$page;
        $postArr['rows']=$rows;
        return $this->curl($postArr);
    }
    
    private function curl($postArr){
        $curl=new ApiCurlLib($this->curl_url,[],$postArr);
        return $curl->curl();
    }
}

