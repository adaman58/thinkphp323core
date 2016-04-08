<?php
namespace Home\Controller;

use Org\Util\Date;
use Think\Controller;

class HdcyxController extends Controller
{
    CONST END_TIME = 1460563200; // 1460563200
    private function redirectAuth()
    {
        redirect(getAuthUrl(urlencode('http://' . C('SITE_DOMAIN') . '/hdcyx/index.html')));
    }

    public function index()
    {
        $MP = C('MP');
        $openid = cookie('openid');
        if ($openid || $_GET['code']) {
            if ($openid) {
                $info = D('LoginUser')->findUser($openid);
                if (!$info) {
                    if(empty($_GET['code'])) {
                        cookie('openid', null);
                        $this->redirectAuth();
                    }
                }
                $userInfo = Array(
                    'nickname' => $info['nickname'],
                    'unionid' => $openid,
                    'headimgurl' => $info['headimgurl']
                );
            } else {
                $userInfo = get_weixin_user_info();
                if ($userInfo['errcode']) {
                    $this->redirectAuth();
                }
                $openid = $userInfo['unionid'];
                cookie('openid', $userInfo['unionid'], 604800);
            }

            $this->user_info = $userInfo;
            $this->appid = $MP['APP_ID'];
            $cached_signature = cache_signature();
            $this->assign($cached_signature);

            $bmInfo = D('ActiveUser')->findUser($openid);
            if (!empty($bmInfo)) {
                $this->assign('has_bm', 1);
                $this->assign('bm_img', $bmInfo['img_path']);
            }

            $this->display();
        } else {
            $this->redirectAuth();
        }
    }

    /** 报名 **/
    public function updateData()
    {
        if (self::END_TIME < NOW_TIME) {
            $this->ajaxReturn(array('status'=>0, 'info'=>('活动报名已结束，感谢您的参与。' . NOW_TIME)));
        }
        $data = array('openId' => $_REQUEST['openId'], 'mobile' => $_REQUEST['mobile'],
            'name' => $_REQUEST['name'], 'describe' => $_REQUEST['describe'], 'img_path' => $_REQUEST['image'], 'face_img' => $_REQUEST['image']);

        $img_path = $this->face_img($data);
        $data['img_path'] = $img_path;
        $data['face_img'] = $img_path;
        $img_path = $this->composeImg($data);
        $data['img_path'] = $img_path;

        D('ActiveUser')->addUser($data);

        $this->ajaxReturn(array('status' => 1, 'img_path' => $data['img_path'], 'table_name' => D('ActiveUser')->getTableName()));
    }

    public function face_img($data)
    {

        $path = APP_PATH . '../Uploads/face/face_' . $data['openId'] . '.png';
        $ret = put_file_from_url_content($data['img_path'], $path);

        $size = getimagesize($path);
        $image = new \Think\Image();
        $image->open($path);
        $image->thumb(187, 187, \Think\Image::IMAGE_THUMB_FIXED);

        $image->save($path);
        $img_url = 'http://' . C('SITE_DOMAIN') . '/Uploads/face/face_' . $data['openId'] . '.png';
        return $img_url;
    }


    /** 生成推广图片**/
    private function composeImg($data)
    {
        $img_url = '/Uploads/new/';
        $bg_path = APP_PATH . '../Uploads/base/bg.jpg';
        $out_path = APP_PATH . '../Uploads/new/';
        $font_path = APP_PATH . '../Uploads/font/yahei.ttf';
        $bg1_path = $data['face_img'];

        // 设置背景图
        $dst = imagecreatefromstring(file_get_contents($bg_path));

        // 头像圆形处理
        $user_img = new \image_cutter($bg1_path);
        // 添加圆形头像到背景图指定位置
        imagecopy($dst, $user_img->cutted_image, 80, 180, 0, 0, 187, 187);
        // 文字颜色-名字
        $font_color = imagecolorallocate($dst, 0x43, 0x37, 0x2b);
        // 添加姓名文字到背景图
        imagefttext($dst, 45, 0, 350, 365, $font_color, $font_path, $data['name']);
        // 文字颜色-描述
        $font_color = imagecolorallocate($dst, 0xca, 0x9f, 0x75);
        // 描述文字内容过长时，自动换行
        $describe = autowrap(24, 0, $font_path, $data['describe'], 640);
        // 添加描述文字到背景图
        imagefttext($dst, 24, 0, 73, 450, $font_color, $font_path, $describe);

        // 获取二维码图片地址
        $qrCode = $this->qrCode($data['openId']);
        $src = imagecreatefromstring(file_get_contents($qrCode));

        // 背景图大小
        $dst_size = getimagesize($bg_path);
        // 二维码大小
        $src_size = getimagesize($qrCode);
        // 二维码在背景图的坐标
        $x = 50;
        $y = 600;
        // 添加二维码到背景图
        imagecopy($dst, $src, $x, $y, 0, 0, $src_size[0], $src_size[1]);

        $filename = $out_path . $data['openId'] . '-' . NOW_TIME;
        $img_url = $img_url . $data['openId'] . '-' . NOW_TIME;
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
        $img_url = 'http://' . C('SITE_DOMAIN') . $img_url;
        // 返回图片路径
        return $img_url;
    }

    /** 自作二维码图片 **/
    private function qrCode($openId)
    {
        $url = 'http://my.tv.sohu.com/user/a/wvideo/getQRCode.do?';
        $text = urlencode('http://wx.dreammove.cn/hdcyx/vote/openId/' . $openId);
        $url = $url . 'width=240&height=240&text=' . $text;

        return $url;
    }

    public function vote()
    {
        $MP = C('MP');
        $myOpenId = cookie('openid');
        //$myOpenId = 'oUzL_sjFfjU2q3izKJ3Eey7hkNP4';
        $openId = $_GET['openId'];
        if ($myOpenId || $_GET['code']) {
            if ($myOpenId) {
                $info = D('LoginUser')->findUser($myOpenId);

                if (!$info) {
                    if(empty($_GET['code'])) {
                        cookie('openid', null);
                        redirect(getAuthUrl(urlencode('http://' . C('SITE_DOMAIN') . '/hdcyx/vote/openId/' . $openId . '.html')));
                    }
                }
                
                $userInfo = Array(
                    'nickname' => $info['nickname'],
                    'unionid' => $myOpenId,
                    'headimgurl' => $info['headimgurl']
                );
            } else {
                
                $userInfo = get_weixin_user_info();
                if ($userInfo['errcode']) {
                    redirect(getAuthUrl(urlencode('http://' . C('SITE_DOMAIN') . '/hdcyx/vote/openId/' . $openId . '.html')));
                }
                $myOpenId = $userInfo['unionid'];
                cookie('openid', $userInfo['unionid'], 604800);
            }
            $this->user_info = $userInfo;
            $this->appid = $MP['APP_ID'];
            $cached_signature = cache_signature();
            $this->assign($cached_signature);


            $data = D('ActiveUser')->findUser($openId);

            if (!$data) {
                $this->redirect('index');
            } else {
                $vote_count = D('JoinUser')->voteCount($openId);
                $voteInfo = D('JoinUser')->findUser($myOpenId, $openId, date('Ymd', time()));
                if (!empty($voteInfo)) {
                    $this->assign('has_voted', 1);
                }

                $this->assign('openId', $openId);
                $this->assign('myOpenId', $myOpenId);
                $this->assign('vote_count', $vote_count);
                $this->assign('nickname', $data['name']);
                $this->assign('headimgurl', $data['face_img']);
            }
            $this->display();
        } else {
            $url = getAuthUrl(urlencode('http://' . C('SITE_DOMAIN') . '/hdcyx/vote/openId/' . $openId . '.html'));
            redirect($url);
        }
    }

    public function getrank() {
        $field = array('vote_count' => 1, 'name' => 1, 'face_img'=> 1, 'openId'=>1, '_id'=>1);
        $list = D('ActiveUser')->order('vote_count desc')->limit(10)->select(array('field' => $field));
        $active_count = D('ActiveUser')->count() + 50;
        $active = D('ActiveUser')->where(array('openId' => $_GET['openId']))->find();
        $vote_count = $active['vote_count'] ? $active['vote_count'] : 0;
        $rank = D('ActiveUser')->where(array('vote_count'=> array('gt', $vote_count)))->count();
        $active['rank'] = $rank;

        $data = array('top_list' => (Array)$list, 'active_user' => $active, 'active_count' => $active_count);

        $this->ajaxReturn($data);
    }

    public function votepost()
    {
        if (self::END_TIME < NOW_TIME) {
            $this->ajaxReturn(array('status'=>0, 'info'=>'活动投票已结束，感谢您的参与。'));
        }
        $openId = $_REQUEST['openId'];
        $myOpenId = $_REQUEST['myOpenId'];

        $data = array('activeId' => $openId, 'openId' => $myOpenId,
            'nowDay' => date('Ymd', time()));

        $ret = D('JoinUser')->addUser($data);
        if ($ret) {
            $this->ajaxReturn(array('status' => 1, 'info' => '投票成功'));
        } else {
            $this->ajaxReturn(array('status' => 0, 'info' => D('JoinUser')->getError()));
        }
    }
}