<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    private function random_str($length)
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
    private function get_cur_url()
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

    private function cache_signature()
    {
        $noncestr = $this->random_str(16);
        $time = time();
        $url = $this->get_cur_url();
        $jsapi_ticket = getJsapiTicket();
        $string = 'jsapi_ticket=' . $jsapi_ticket . '&noncestr=' . $noncestr . '&timestamp=' . $time . '&url=' . $url;
        $signature = sha1($string);

        $this->assign('nonce_str', $noncestr);
        $this->assign('timestamp', $time);
        $this->assign('signature', $signature);
    }

    public function index()
    {
    	print_r(getToken());exit();
        $this->cache_signature();
        $this->display();
    }

    public function index2()
    {
        $this->display('index');
    }
}