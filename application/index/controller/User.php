<?php
namespace app\index\controller;
use think\Cache;
use think\Exception;
use \think\Db;

class User {


	public function resetlist () {
		$list = db('user_table')->select();
		foreach ($list as $key => $value) {
			$map['user_code'] = uniqid();
			db('user_table')->where('user_id', $value['user_id'])->update($map);
		}
	}


	// 注册登录
	public function regist_user () {
		$resdata = ['code' => 0, 'message'=>'添加失败', 'data'=> []];
<<<<<<< HEAD
		$user_openid = input('user_openid');
		$userinfo = db('user_table')->where('user_openid', $user_openid)->find();
		if ($userinfo) {
=======
		$user_openid = input('user_openid', 'testA00006');
		$userinfo = db('user_table')->where('user_openid', $user_openid)->find();
		if ($userinfo) {
			$userinfo = db('user_table')->where('user_openid', $user_openid)->find();
>>>>>>> 488a8d95fba81e0f812fc454cabb850b43ffa6c2
			$resdata = ['code' => 1, 'message'=>'用户存在', 'data' => $userinfo];
		} else {
			$user_imname = uniqid() . 'QWER';
			$data['user_openid'] = input('user_openid','');
			$data['user_name'] = input('user_name','');
			$data['user_head_image'] = input('user_head_image','');
			$data['user_regist_type'] = input('user_regist_type', 1);
			$data['user_imname'] = $user_imname;
			$data['user_code'] = uniqid();
			$usergetcode = input('user_get_code', '');
			$data['user_get_code'] = $usergetcode;
			if ($usergetcode) {
				// 邀请者的用户信息
				$getuser = db('user_table')->where('user_code', $usergetcode)->find();
				if ($getuser['user_type'] == 2) {
					$data['user_shop_code'] = $getuser['user_code'];
				} else {
					$data['user_shop_code'] = $getuser['user_shop_code'];
				}
			}

			$res = db('user_table')->insert($data);
			$registsuc = $this->regist_imuser($user_imname);
			if ($res && $registsuc) {
				$userinfo = db('user_table')->where('user_openid', $user_openid)->find();
				$resdata = ['code' => 1, 'message'=>'添加成功', 'data' => $userinfo];
			}
		}
		return $resdata;
	}


	// 绑定用户code
	public function bind_usercode () {
		$userid = input('user_id');
		$usergetcode = input('user_get_code', '');
		$data['user_get_code'] = $usergetcode;
		if ($usergetcode) {
			// 邀请者的用户信息
			$getuser = db('user_table')->where('user_code', $usergetcode)->find();
			if ($getuser['user_type'] == 2) {
				$data['user_shop_code'] = $getuser['user_code'];
			} else {
				$data['user_shop_code'] = $getuser['user_shop_code'];
			}
		}
<<<<<<< HEAD
		$res = db('user_table')->where('user_id',$userid)->update($data);
=======
		$res = db('user_table')->where('user_id', $userid)->update($data);
>>>>>>> 488a8d95fba81e0f812fc454cabb850b43ffa6c2
		$resdata = ['code' => 0, 'message'=>'绑定失败', 'data'=> []];
		if ($res) {
			$resdata = ['code' => 1, 'message'=>'绑定成功', 'data'=> []];
		}
		return $resdata;
	}

<<<<<<< HEAD
=======

	// 绑定电影邀请code
	public function bind_movecode () {
		$userid = input('user_id');
		$usermovecode = input('user_move_code', '');
		$data['user_get_move_code'] = $usergetcode;
		$resdata = ['code' => 0, 'message'=>'绑定失败', 'data'=> []];
		$has = db('user_move_table')->where('move_code', $usermovecode)->find();
		if ($has) {
			$res = db('user_table')->where('user_id', $userid)->update($data);
			if ($res) {
				$resdata = ['code' => 1, 'message'=>'绑定成功', 'data'=> []];
			}
		}
		// if ($usermovecode) {
		// 	// 邀请者的用户信息
		// 	$getuser = db('user_table')->where('user_move_code', $usergetcode)->find();
		// 	if ($getuser['user_type'] == 2) {
		// 		$data['user_shop_code'] = $getuser['user_code'];
		// 	} else {
		// 		$data['user_shop_code'] = $getuser['user_shop_code'];
		// 	}
		// }
		// $res = db('user_table')->where('user_id', $userid)->update($data);
		return $resdata;
	}


	// 将用户设置为电影邀请人
	public function seMoveuser () {
		Db::startTrans();
		$resdata = ['code' => 1, 'message'=>'绑定成功', 'data'=> []];
		try {
			$userid = input('user_id', 36);
			$codestr = uniqid();
			$map['user_move_code'] = $codestr;
			$add = db('user_table')->where('user_id', $userid)->update($map);

			$data['move_code'] = $codestr;
			$data['move_link'] = 'http://api.kantv.vip/movie/index.html';
			$data['move_addtime'] = time();
			$moveadd = db('user_move_table')->insert($data);

			if (!$add || !$moveadd) {
				throw new \Exception("发布失败");
			}
			Db::commit();
		} catch (Exception $e) {
			Db::rollback();
			$resdata = ['code' => 0, 'message'=>'绑定失败', 'data'=> []];
		}
		
		dump($resdata);
	}

>>>>>>> 488a8d95fba81e0f812fc454cabb850b43ffa6c2
	public function regist_imuser ($user_imname){
		$msg = new Message();
		$registsuc = $msg->regise_user($user_imname);
		return $registsuc;
	}


	/*
	*	生成验证码和发送
	*/
	public function get_captche_code () {
		$arr = array_merge(range('a','b'),range('A','B'),range('0','9'));
		shuffle($arr);
		$arr = array_flip($arr);
		$arr = array_rand($arr,4);
		$res = '';
		foreach ($arr as $v){
		   $res.=$v;
		}
		Cache::set($res, $res, 60);
		return ['code'=> 1, 'data' => $res];
	}

	// 校验验证码绑定手机号
	public function bind_phone () {
		$resdata = ['code' => 1, 'message' => '绑定成功'];
		try {
			$codekey = input('code');
			$user_id = input('user_id');
			$user_phone = input('user_phone');
			$code = Cache::get($codekey);
			$has = db('user_table')->where('user_phone', $user_phone)->find();
			if ($has) {
				throw new Exception("该手机号已绑定其他用户");
			}
			if ($code) {
				$res = db('user_table')->where('user_id', $user_id)->setField('user_phone', $user_phone);
				if (!$res) {
					throw new Exception("绑定失败");
				}
			} else {
				throw new Exception("验证码已过期");
			}
		} catch (Exception $e) {
			$resdata = ['code' => 0, 'message' => $e->getMessage()];
		}
		return $resdata;
	}


	/*
	*	获取城市列表
	*/
  public function get_city_list () {
  	$resdata = ['code' => 0, 'message'=>'请求失败', 'data' => []];
  	$citylist = db('home_town')->select();
  	if ($citylist) {
  		$resdata = ['code' => 1, 'message'=>'请求成功', 'data' => $citylist];
  	}
  	return $resdata;
  }


  // 绑定城市
  public function bind_city () {
  	$resdata = ['code' =>1, 'message' => '绑定成功'];
  	$user_id = input('user_id');
  	$home_id = input('home_id');
  	try {
			$res = db('user_table')->where('user_id', $user_id)->setField('user_home_town', $home_id);
			if (!$res) {
				throw new Exception("绑定失败");
			}
  	} catch (Exception $e) {
  		$resdata = ['code' =>1, 'message' => $e->getMessage()];
  	}
  	return $resdata;
  }


  // 回复消息
  public function back_message () {
  	$user_id = input('user_id');
  	$page = input('page');
  	$pagenum = $page * 10;
  	$list = db('commit_table')->where('commit_two_userid', $user_id)->limit($pagenum, 10)->select();
  	$resdata = ['code'=>1, 'data'=>[], 'message'=>'暂无数据'];
  	if ($list) {
  		$resdata = ['code'=>1, 'data'=>$list, 'message'=>'请求成功'];
  	}
  	return $resdata;
  }

}
