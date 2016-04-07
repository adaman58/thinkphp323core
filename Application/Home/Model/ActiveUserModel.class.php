<?php 
namespace Home\Model;
use Think\Model\MongoModel;

class ActiveUserModel extends MongoModel {

	public function addUser($data) {
		$where = array('openId' => $data['openId']);
		$temp = $this->where($where)->find();

		if (!$temp) {
			\Think\Log::write('11111');
			$key = (array)$this->add($data);
			$key = $key['$id'];
		} else {
			\Think\Log::write('2222');
			\Think\Log::write(json_encode($temp));
			$this->where($where)->save($data);
			$key = $temp['_id'];
		}

		return $key;
	}

	public function findUser($openId) {
		$where = array('openId' => $openId);
		$temp = $this->where($where)->find();

		return $temp;
	}
}
?>