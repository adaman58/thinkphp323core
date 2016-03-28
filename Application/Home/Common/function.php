<?php

function getAuthUrl($redirect_uri)
{
    $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . C('MP')['APP_ID'] . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=snsapi_userinfo&state=state#wechat_redirect';
    return $url;
}


function random_str($length)
{
    //生成一个包含 大写英文字母, 小写英文字母, 数字 的数组
    $arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));

    $str = '';
    $arr_len = count($arr);
    for ($i = 0; $i < $length; $i++) {
        $rand = mt_rand(0, $arr_len - 1);
        $str .= $arr[$rand];
    }
    return $str;
}

//php获取当前访问的完整url地址
function get_cur_url()
{
    $url = 'http://';
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
        $url = 'https://';
    }
    if ($_SERVER['SERVER_PORT'] != '80') {
        $url .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
    } else {
        $url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    }
    return $url;
}

function cache_signature()
{
    $noncestr = random_str(16);
    $time = time();
    $url = get_cur_url();
    $jsapi_ticket = getJsapiTicket();
    $string = 'jsapi_ticket=' . $jsapi_ticket . '&noncestr=' . $noncestr . '&timestamp=' . $time . '&url=' . $url;
    $signature = sha1($string);

    return Array(
        'nonce_str' => $noncestr,
        'timestamp' => $time,
        'signature' => $signature
    );
}

// 获取微信用户信息
function get_weixin_user_info()
{
    $code = $_GET['code'];
    $MP = C('MP');
    $getTokenUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $MP['APP_ID'] . '&secret=' . $MP['APP_SECRET'] . '&code=' . $code . '&grant_type=authorization_code';
    $accessTokenData = curlGet($getTokenUrl);
    $accessTokenInfo = (Array)json_decode($accessTokenData['receive_info']);

    $accessToken = $accessTokenInfo['access_token'];
    $openid = $accessTokenInfo['openid'];
    $unionid = $accessTokenInfo['unionid'];

    $getUserInfoUrl = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $accessToken . '&openid=' . $openid . '&lang=zh_CN';
    $userInfoData = curlGet($getUserInfoUrl);
    return (Array)json_decode($userInfoData['receive_info']);
}