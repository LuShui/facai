<?php
namespace app\index\controller;
use think\Cache;
use think\Exception;
class User {

	// 注册登录
	public function regist_user () {
		$resdata = ['code' => 0, 'message'=>'添加失败'];
		$user_openid = input('user_openid');
		$userinfo = db('user_table')->where('user_openid', $user_openid)->find();
		if ($userinfo) {
			$resdata = ['code' => 1, 'message'=>'添加成功', 'data' => $userinfo];
		} else {
			$data['user_openid'] = input('user_openid');
			$data['user_name'] = input('user_name');
			$data['user_head_image'] = input('user_head_image');
			$data['user_regist_type'] = input('user_regist_type');
			$res = db('user_table')->insert($data);
			if ($res) {
				$userinfo = db('user_table')->where('user_openid', $user_openid)->find();
				$resdata = ['code' => 1, 'message'=>'添加成功', 'data' => $userinfo];
			}
		}
		return $resdata;
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