<?php

/**
 * POST的方式调用API
 * @param string $url       请求的API地址
 * @param array  $data      发送参数 
 */
function curlPost($url,$data=null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0 );

    $header = array ('Accept-Charset: utf-8',
            'Content-Type:application/x-www-form-urlencoded',
            );

    curl_setopt($ch, CURLOPT_HTTPHEADER, $header );
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    $temp = curl_exec($ch);         // 返回信息
    $status = curl_getinfo($ch);    // 执行结果
    curl_close($ch);
    
    $info = array('receive_info'=>$temp, 'status'=>$status);

    return $info;
}

function curlGet($url,$data=null) {
	$ch = curl_init();

	if (!empty($data)) {
		$params = http_build_query($data);
		if (strstr($url, '?')) {
			$url = $url . '&' . $params;
		} else {
			$url = $url . '?' . $params;
		}
	}


	$header = array ('Accept-Charset: utf-8',
            'Content-Type:application/x-www-form-urlencoded',
            );
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header );
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$temp = curl_exec($ch);
	$status = curl_getinfo($ch);    // 执行结果
	curl_close($ch);

	$info = array('receive_info'=>$temp, 'status'=>$status);

	return $info;
}


function requestWeixinApiGet($url,$getData){

	$baseurl = C('DOMAIN_URL_V1');
	$getData['access_token'] = getToken();
	if($getData['user_id']==null && is_login()>0){//额外增加登录用户的用户openid信息
		$getData['user_id'] = is_loginopen();
	}
	$api = C('UNDM_API');

	$info = curlGet($baseurl.$api[$url],$getData);

	return $info;
}

function requestWeixinApiPost($url, $postData){

	$baseurl = C('DOMAIN_URL_V1');
	$postData['access_token'] = getToken();
	if($postData['user_id']==null && is_login()>0){//额外增加登录用户的用户openid信息
		$postData['user_id'] = is_loginopen();
	}
	$api = C('UNDM_API');

	$info = curlPost($baseurl.$api[$url],$postData);
	return $info;
}

/**
 * 获取 API 授权令牌
 * @return string 授权令牌
 */
function getToken() {
	$accessToken = S('SUBSITE_ACCESSTOKEN');
	if (empty($accessToken)) {
		$params = C('MP');
		$baseurl = C('WEIXIN_DOMAIN_URL');
		$api = C('WEIXIN_API');

		$params = array('appid'=>$params['APP_ID'],
				'secret'=>$params['APP_SECRET'],
				'grant_type'=>$params['GRANT_TYPE']);

		$url = $baseurl . $api['GETTOKEN'];
		$info = curlGet($url,$params);
		$data = json_decode($info['receive_info'], true);
		$accessToken = $data['access_token'];
		S('SUBSITE_ACCESSTOKEN', $accessToken, (7200 - 60));
	}
	return $accessToken;
}

/**
 * 获取 API 授权令牌
 * @return string 授权令牌
 */
function getJsapiTicket() {
	$ticket = S('SUBSITE_JSAPITICKET');
	if (empty($ticket)) {
		$baseurl = C('WEIXIN_DOMAIN_URL');
		$api = C('WEIXIN_API');

		$params = array('access_token'=>getToken(),
				'type'=>'jsapi');

		$url = $baseurl . $api['GETJSAPI'];
		$info = curlGet($url,$params);
		$data = json_decode($info['receive_info'], true);
		$ticket = $data['ticket'];
		S('SUBSITE_JSAPITICKET', $ticket, (7200 - 60));
	}
	return $ticket;
}