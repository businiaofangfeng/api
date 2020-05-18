<?php
include_once 'businiao.defined.php';
include_once 'businiao.curlLib.php';
include_once 'businiao.api.php';
/**github
git tag -a v1.2.0 -m 'v1.2.0'
git push origin --tags
composer require goodyes/wechat_url_check_preventing_blocked_api ^1.2.0
 * */


function startWith($str, $needle) {
    
    return strpos($str, $needle) === 0;
    
}
function endWith($haystack, $needle) {
    $length = strlen($needle);
    if($length == 0)
    {
        return true;
    }
    return (substr($haystack, -$length) === $needle);
}