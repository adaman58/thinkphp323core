<?php 
namespace Home\Model;
use Think\Model\MongoModel;

class LoginUserModel extends MongoModel {

	public function addUser($data) {
		$where = array('unionid' => $data['unionid']);
		$temp = $this->where($where)->find();

		if (!$temp) {
			$key = (array)$this->add($data);
			$key = $key['$id'];
		} else {
			$this->where($where)->save($data);
			$key = $temp['_id'];
		}

		return $key;
	}

	public function findUser($openId) {
		$where = array('unionid' => $openId);
		$temp = $this->where($where)->find();

		return $temp;
	}
}
?>