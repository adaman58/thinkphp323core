<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    // 兼容历史网址
    public function mrj()
    {
        $this->redirect('mrj/mrj');
    }
    
    // 兼容历史网址
    public function mrj_index()
    {
        $this->redirect('mrj/index');
    }
}