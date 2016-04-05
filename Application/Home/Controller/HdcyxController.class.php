<?php
namespace Home\Controller;

use Think\Controller;

class HdcyxController extends Controller
{

    private function redirectAuth()
    {
        redirect(getAuthUrl(urlencode('http://' . C('SITE_DOMAIN') . '/hdcyx/index.html')));
    }

    public function index()
    {
        $MP = C('MP');
        $nickname = session('nickname');
        $nickname = 'aaaaaaaaaaa';
        if ($nickname || $_GET['code']) {
            if ($nickname) {
                $userInfo = Array(
                    'nickname' => $nickname,
                    'unionid' => session('unionid'),
                    'headimgurl' => session('headimgurl')
                );
            } else {
                $userInfo = get_weixin_user_info();
                if ($userInfo['errcode']) {
                    $this->redirectAuth();
                }

                session('nickname', $userInfo['nickname']);
                session('unionid', $userInfo['unionid']);
                session('headimgurl', $userInfo['headimgurl']);
            }
            $this->user_info = $userInfo;
            $this->appid = $MP['APP_ID'];
            $cached_signature = cache_signature();
            $this->assign($cached_signature);
            $this->display();
        } else {
            $this->redirectAuth();
        }
    }

    public function support()
    {
        $MP = C('MP');
        $nickname = session('nickname');
        if ($nickname || $_GET['code']) {
            if ($nickname) {
                $userInfo = Array(
                    'nickname' => $nickname,
                    'unionid' => session('unionid'),
                    'headimgurl' => session('headimgurl')
                );
            } else {
                $userInfo = get_weixin_user_info();
                if ($userInfo['errcode']) {
                    $this->redirectAuth();
                }

                session('nickname', $userInfo['nickname']);
                session('unionid', $userInfo['unionid']);
                session('headimgurl', $userInfo['headimgurl']);
            }
            $this->user_info = $userInfo;
            $this->appid = $MP['APP_ID'];
            $cached_signature = cache_signature();
            $this->assign($cached_signature);
            $this->display();
        } else {
            $this->redirectAuth();
        }
    }
}