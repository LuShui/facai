<?php
namespace app\index\controller;
use \think\Db;
class Menber extends Base {
	
	// 修改用户信息
	public function update_userinfo(){
		$user_id = input('user_id');
		$user_brithday = input('user_brithday');
		$user_name = input('user_name');
		$user_head_image = input('user_head_image');
		$user_detil = input('user_detil');
		if ($user_brithday) {
			$map['user_brithday'] = $user_brithday;
		}
		if ($user_name) {
			$map['user_name'] = $user_name;
		}
		if ($user_head_image) {
			$map['user_head_image'] = $user_head_image;
		}
		if ($user_detil) {
			$map['user_detil'] = $user_detil;
		}
		$res = db('user_table')->where('user_id', $user_id)->update($map);
		$resdata = ['code' => 0, 'data' => [], 'message' => '修改失败'];
		if ($res) {
			$resdata = ['code' => 1, 'data' => [], 'message' => '修改成功'];
		}
		return $resdata;
	}

	// 获取用户信息
	public function menber_userinfo () {
		$user_id = input('user_id', 5);
		$userinfo = db('user_table')->where('user_id', $user_id)->find();
		
		$userinfo['dynamic_count'] = db('dynamic_table')->where('user_id', $user_id)->count();
		$userinfo['message_count'] = db('message_table')->where('message_dynamic_userid', $user_id)->where('message_is_look', 1)->count();
		$userinfo['commit_count'] = db('commit_table')->where('commit_user_id', $user_id)->count();
		return ['code' => 1, 'data' => $userinfo, 'message' => '请求成功'];
	}

	// 我的动态
	public function myDynamic_list() {
		$user_id = input('user_id', 5);
		$page = input('page', 0);
		$pagenum = $page * 10;
		$list = db('dynamic_table')->where('user_id', $user_id)->limit($pagenum, 10)->select();
		$resdata = ['code' => 0, 'data' => [], 'message' => '暂无数据'];
		if ($list) {
			foreach ($list as $key => &$value) {
				$dynamic_id = $value['dynamic_id'];
				// 获取图片
				$value['dynamic_images'] = db('dynamic_images')->where('dynamic_id', $dynamic_id)->field('dynamic_image')->select();
				// 获取发布动态的用户信息
				$value['userinfo'] = db('user_table')->where('user_id', $value['user_id'])->find();
				// 浏览量
				$value['look_count'] = db('look_table')->where('dynamic_id', $dynamic_id)->count();
				// 点赞量
				$value['zan_count'] = db('zan_table')->where('dynamic_id', $dynamic_id)->count();
				// 评论数量
				$value['commit_count'] = db('commit_table')->where('commit_dynamic_id', $dynamic_id)->count();
				// 是否点赞
				$finds = db('zan_table')->where('dynamic_id', $dynamic_id)->where('user_id', $value['user_id'])->find();
				$value['iszan'] = false;
				if ($finds) {
					$value['iszan'] = true;
				}
			}
			$resdata = ['code' => 1, 'data' => $list, 'message' => '请求成功'];
		}
		return $resdata;
	}


	// 我的消息
	public function myMessage_list () {
		$user_id = input('user_id', 5);
		$page = input('page', 0);
		$pagenum = $page * 10;
		$resdata = ['code' => 0, 'data' => [], 'message' => '暂无数据'];
		$list = Db::query('SELECT msg.*, dyn.dynamic_title FROM vip_message_table as msg LEFT JOIN vip_dynamic_table dyn ON msg.message_dynamic_id=dyn.dynamic_id WHERE msg.message_sen_dynamic_userid=? ORDER BY addtime DESC  LIMIT ?,10', [$user_id, $pagenum]);
		if ($list) {
			foreach ($list as $key => $value) {
				if ($value['message_is_look'] == 1) {
					db('message_table')->where('message_id', $value['message_id'])->update(['message_is_look' => 1]);
				}
			}
			$resdata = ['code' => 1, 'data' => $list, 'message' => '请求成功'];
		}
		return $list;
	}

	// 我的评论列表
	public function myCommit_list(){
		$user_id = input('user_id', 5);
		$page = input('page', 0);
		$pagenum = $page * 10;
		$resdata = ['code' => 0, 'data' => [], 'message' => '暂无数据'];
		$list = db('commit_table')->where('commit_user_id', $user_id)->limit($pagenum, 10)->select();
		foreach ($list as $key => &$value) {
			// 用户信息
			$value['userinfo'] = db('user_table')->where('user_id', $value['commit_user_id'])->find();
			if ($value['commit_two_userid']) {
				// 被评论的用户信息
				$value['userinfo_two'] = db('user_table')->where('user_id', $value['commit_two_userid'])->find();
			}
			if ($value['commit_conent_id']) {
			 $value['commit_info'] =	db('commit_table')->where('commit_conent_id', $value['commit_conent_id'])->find();
			}
		}
		$resdata = ['code' => 1, 'data' => $list, 'message' => '请求成功'];
		return $list;
	}


}