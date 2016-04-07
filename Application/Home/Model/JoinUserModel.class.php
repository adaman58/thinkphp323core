<?php
namespace Home\Model;

use Think\Model\MongoModel;

class JoinUserModel extends MongoModel
{

    public function addUser($data)
    {
        $where = array('openId' => $data['openId'], 'activeId' => $data['activeId'], 'nowDay' => $data['nowDay']);
        $temp = $this->where($where)->find();

        if (!$temp) {
            $key = (array)$this->add($data);
            $key = $key['$id'];
            D('ActiveUser')->update_vote($data['activeId']);
        } else {
            $this->error = '您今天已经参与了投票，请您明天再来支持他。';
            return false;
        }

        return true;
    }

    /** 获取总票数 **/
    public function voteCount($openId)
    {
        return $this->where(array('activeId' => $openId))->count();
    }

    public function findUser($myOpenId, $activeOpenId, $date)
    {
        //TODO: 一天只能助威一个人?
        $where = array('openId' => $myOpenId, 'activeId' => $activeOpenId, 'nowDay' => $date);
        $temp = $this->where($where)->find();
        return $temp;
    }
}

?>