<?php
namespace Home\Controller;

use Think\Controller;

class MrjController extends Controller
{

    private function redirectAuth()
    {
        redirect(getAuthUrl(urlencode('http://' . C('SITE_DOMAIN') . '/mrj/mrj.html')));
    }

    public function mrj()
    {
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
            if ($userInfo['errcode']) {
                $this->redirectAuth();
            }
            print_r($userInfo['errcode']);

            $this->user_info = $userInfo;
            $this->appid = $MP['APP_ID'];
            $cached_signature = cache_signature();
            $this->assign($cached_signature);
            $this->display();
        } else {
            $this->redirectAuth();
        }
    }

    public function index()
    {
        $floor = 18.5;
        $ceil = 23;
        $bmi = I('bmi');
        if (empty($bmi)) {
            $this->redirectAuth();
        } else {
            if ($bmi >= $ceil) {
                $weight_text = 'fat';
            } else if ($bmi <= $floor) {
                $weight_text = 'thin';
            } else {
                $weight_text = 'normal';
            }
            $this->weight_text = $weight_text;
            $this->bmi = number_format($bmi, 2);
            $this->authUrl = getAuthUrl(urlencode('http://' . C['SITE_DOMAIN'] . '/mrj/mrj.html'));
            $this->display();
        }
    }


}