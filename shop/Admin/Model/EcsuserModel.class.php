<?php

namespace Admin\Model;

use Think\Model;

class EcsuserModel extends Model {
	public function UserCount($where) {
		$userCount = $this->db ( 1, "DB_CONFIG1" )->table ( "ecs_users" )->where ( $where )->count ();
		return $userCount;
	}
	public function UserInfos($start, $end, $where) {
		$userInfos = $this->db ( 1, "DB_CONFIG1" )->table ( "ecs_users" )->where ( $where )->order ( "reg_time desc" )->limit ( $start, $end )->select ();
		return $userInfos;
	}
	public function AddUserInfos($data) {
		$result = $this->db ( 1, "DB_CONFIG1" )->table ( "ecs_users" )->add ( $data );	
		if ($result) {
			return TRUE;
		}
		return FALSE;
	}
	public function CheckPhone($phone) {
		$result = $this->db ( 1, "DB_CONFIG1" )->table ( "ecs_users" )->where ( "mobile_phone = '" . $phone . "'" )->select ();
		if ($result) {
			return TRUE;
		}
		return FALSE;
	}
	public function LookDetail($user_id) {
		$result = $this->db ( 1, "DB_CONFIG1" )->table ( "ecs_users" )->where ( "user_id = " . $user_id . "" )->find ();
		return $result;
	}
}