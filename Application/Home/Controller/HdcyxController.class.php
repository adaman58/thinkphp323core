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
            $this->assign('has_bm', 0);
            $this->assign('bm_img', 'http://wx.dreammove.cn/Uploads/new/-1459939120.png');
            $this->display();
        } else {
            $this->redirectAuth();
        }
    }

    /** 报名 **/
    public function updateData() {
        $data = array('openId' => $_REQUEST['openId'], 'mobile' => $_REQUEST['mobile'],
            'name' => $_REQUEST['name'], 'describe' => $_REQUEST['describe'], 'img_path'=>$_REQUEST['image'], 'face_img'=>$_REQUEST['image']);

        $img_path = $this->face_img($data);
        $data['img_path'] = $img_path;
        $img_path = $this->composeImg($data);
        $data['img_path'] = $img_path;

        $this->ajaxReturn(array('status'=> 1, 'img_path' => $data['img_path']));
    }

    public function sharepage() {

    }

    public function face_img($data) {

        $path = APP_PATH . '../Uploads/face/face_'.$data['openId']. '.png';
        $ret = put_file_from_url_content($data['img_path'], $path);

        $size = getimagesize($path);
        $image = new \Think\Image();
        $image->open($path);
        $image->thumb(230, 230);

        $image->save($path);
        return $path;
    }


    /** 生成推广图片**/
    private function composeImg($data) {
        $img_url = '/Uploads/new/';
        $bg_path = APP_PATH . '../Uploads/base/bg3.png';
        $out_path = APP_PATH . '../Uploads/new/';
        $font_path = APP_PATH . '../Uploads/font/yahei.ttf';
        $bg1_path = $data['img_path'];

        // 设置背景图
        $dst = imagecreatefromstring(file_get_contents($bg_path));

        // 头像圆形处理
        $user_img = new \image_cutter($bg1_path);
        // 添加圆形头像到背景图指定位置
        imagecopy($dst, $user_img->cutted_image, 75, 70, 0, 0, 230,230);
        // 文字颜色
        $font_color = imagecolorallocate($dst, 0xca, 0x9f, 0x75);
        // 添加姓名文字到背景图
        imagefttext($dst, 42, 0, 75, 380, $font_color, $font_path, $data['name']);
        // 描述文字内容过长时，自动换行
        $describe = autowrap(20, 0, $font_path, $data['describe'], 440);
        // 添加描述文字到背景图
        imagefttext($dst, 20, 0, 73, 430, $font_color, $font_path, $describe);

        // 获取二维码图片地址
        $qrCode = $this->qrCode($data['openId']);
        $src = imagecreatefromstring(file_get_contents($qrCode));

        // 背景图大小
        $dst_size = getimagesize($bg_path);
        // 二维码大小
        $src_size = getimagesize($qrCode);
        // 二维码在背景图的坐标
        $x = $dst_size[0] - $src_size[0] - 50;
        $y = $dst_size[1] - $src_size[1] - 110;
        // 添加二维码到背景图
        imagecopy($dst, $src, $x, $y, 0, 0, $src_size[0], $src_size[1]);

        $filename = $out_path . $data['openId'] . '-' . NOW_TIME;
        $img_url = $img_url .$data['openId'] . '-' . NOW_TIME;
        // 保存图片
        switch ($dst_size[2]) {
            case 1://GIF
                $filename .= '.gif';
                $img_url .= '.gif';
                imagegif($dst, $filename);
                break;
            case 2://JPG
                $filename .= '.jpg';
                $img_url .= '.jpg';
                imagejpeg($dst, $filename);
                break;
            case 3://PNG
                $filename .= '.png';
                $img_url .= '.png';
                imagepng($dst, $filename);
                break;
            default:
                break;
        }
        // 返回图片路径
        return $img_url;
    }

    /** 自作二维码图片 **/
    private function qrCode($openId) {
        $url = 'http://my.tv.sohu.com/user/a/wvideo/getQRCode.do?';
        $text = urlencode('http://wx.dreammove.cn/hdcyx/vote/openId/'.$openId);
        $url = $url . 'width=200&height=200&text='. $text;

        return $url;
    }

    public function vote() {
        $_GET['openId'] = '';
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
            $this->assign('nickname', '水长东水长东水长东');
            $this->assign('headimgurl', 'http://wx.qlogo.cn/mmopen/JWSbnTudFFuW6c8yOHnKUUVfwM3RgBNzHp0icakcvq8S6gdcQNbWT4UlBNVp1MH5nhKkGc5zyD4tWPccSmwd0Yy7foeM9U9NI/0');
            $this->display();
        } else {
            $this->redirectAuth();
        }
	}

    public function votePost() {
        $this->ajaxReturn(Array('status' => 1));
    }
}