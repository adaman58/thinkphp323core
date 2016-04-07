<?php 
namespace Home\Controller;
use Think\Controller;

class HomeController extends Controller {

	protected function getAuthUrl($return_url)
  {
    $redirect_uri = $return_url;
    $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . C('MP')['APP_ID'] . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=snsapi_userinfo&state=state#wechat_redirect';
    return $url;
  }

  protected function wx_auth() {
  	$code = $_GET['code'];
    $MP = C('MP');
    if ($_GET['code']) {
      $getTokenUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $MP['APP_ID'] . '&secret=' . $MP['APP_SECRET'] . '&code=' . $code . '&grant_type=authorization_code';
      $accessTokenData = curlGet($getTokenUrl);
      $accessTokenInfo = (Array)json_decode($accessTokenData['receive_info']);

      $accessToken = $accessTokenInfo['access_token'];
      $openid = $accessTokenInfo['openid'];
      $unionid = $accessTokenInfo['unionid'];

      $getUserInfourl = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $accessToken . '&openid=' . $openid . '&lang=zh_CN';
      $userInfoData = curlGet($getUserInfourl);
      $userInfo = (Array)json_decode($userInfoData['receive_info']);
      
      $this->user_info = $userInfo;
      $this->appid = $MP['APP_ID'];
      $this->cache_signature();
      $this->display();
    }
  }

}
?>