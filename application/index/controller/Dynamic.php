<?php
namespace app\index\controller;
use \think\Db;
use \think\File;
use \think\View;
use \think\Exception;
use \think\Image;
class Dynamic extends Base{
	
	public function index () {
		return view('index');
	}

	// 发布动态
	public function send_dynamic () {
		Db::startTrans();
		$resdata = [];
		try {
			$map['user_id'] = input('user_id');
			$map['dynamic_title'] = input('dynamic_title');
			$map['dynamic_cityid'] = input('dynamic_cityid');
			$map['dynamic_time'] = time();
			Db::name('dynamic_table')->insert($map);
			$res = Db::name('dynamic_table')->getLastInsID();
			$issicces = true;
			$file = request()->file('dynamic_images');
			if ($file) {
				$issicces = $this->more_file_upload($res);
			}
			if ($res && $issicces) {
				$resdata = ['code' => 1, 'data' => [], 'message' => '发布成功'];
				Db::commit();
			} else {
				throw new \Exception("发布失败");
			}
		} catch (Exception $e) {
			Db::rollback();
			$resdata = ['code' => 0, 'data' => [], 'message' => $e->getMessage()];
		}
		return $resdata;
	}


	// public function image_file () {
	// 	$image = \think\Image::open(ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'image.png');
	// 	$image->thumb(300, 300)->save(ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'image.png');
	// }

	// 多图上传
	public function more_file_upload ($res) {
		$success = true;
		try {
			$array = [];
			$files = request()->file('dynamic_images');
	    foreach($files as $file){
	    	$info = $file->validate(['size' => 51200000, 'ext' => 'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads');
	      if($info){
	        $imagesrc = $info->getSaveName();
	        $image = \think\Image::open(ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . $imagesrc);
					$image->thumb(1200, 1200)->save(ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . $imagesrc);
	        $map['dynamic_image'] = $imagesrc;
	        $map['dynamic_id'] = $res;
	        Db::name('dynamic_images')->insert($map);
	        $tres = Db::name('dynamic_images')->getLastInsID();
	        if (!$tres) {
	        	throw new \Exception("发布失败");
	        } else {
	        	array_push($array, $tres);
	        }
	      }else{
	        // 上传失败获取错误信息
	        throw new \Exception("发布失败");
	      }
	    }
      $imageskey = "";
	    foreach ($array as $key => $value) {
	    	if ($imageskey == "") {
					$imageskey = $value;
	    	} else {
	    		$imageskey = $imageskey . ',' . $value ;
	    	}
	    }
	    $upiscu = Db::name('dynamic_table')->where('dynamic_id', $res)->update(['dynamic_images' => $imageskey]);
	    if (!$upiscu) {
	    	throw new \Exception("发布失败");
	    }
		} catch (Exception $e) {
			$success = false;
		}
    return $success;
	}

	// 动态列表
	public function dynamic_list () {
		$page = input('page', 0);
		$cityid = input('cityid', 3);
		$pagenum = $page * 10;
		$list = db('dynamic_table')->where('dynamic_cityid', $cityid)->limit($pagenum, 10)->order('dynamic_time desc')->select();
		$resdata = ['code'=>0, 'data'=>[], 'message'=>'暂无数据'];
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
			$resdata = ['code'=>1, 'data'=>$list, 'message'=>'请求成功'];
		}
		return $resdata;
	}

	// 某一条动态的详情
	public function dynamic_detil () {
		$dynamic_id = input('dynamic_id', 68);
		$dynamic_info = db('dynamic_table')->where('dynamic_id', $dynamic_id)->find();
		// 图片信息
		$dynamic_info['dynamic_images'] = db('dynamic_images')->where('dynamic_id', $dynamic_id)->field('dynamic_image')->select();
		// 发帖人信息
		$dynamic_info['userinfo'] = db('user_table')->where('user_id', $dynamic_info['user_id'])->find();

		// 添加浏览记录
		$map['user_id'] =  $dynamic_info['user_id'];
		$map['dynamic_id'] = $dynamic_id;
		$map['look_time'] = time();
		db('look_table')->insert($map);
		$resdata = ['code'=>0, 'data'=>[], 'message'=>'请求失败'];
		if ($dynamic_info) {
			$resdata = ['code'=>1, 'data'=>$dynamic_info, 'message'=>'请求成功'];
		}
		return $resdata;
	}


	// 用户给动态点赞
	public function dynamic_zan() {
		$dynamic_id = input('dynamic_id', 59);
		$user_id = input('user_id', 5);
		$finds = db('zan_table')->where('dynamic_id', $dynamic_id)->where('user_id', $user_id)->find();
		if (!$finds) {
			$map['user_id'] =  $user_id;
			$map['dynamic_id'] = $dynamic_id;
			$map['zan_time'] = time();
			$res = db('zan_table')->insert($map);
			$resdata = ['code'=>0, 'data'=>[], 'message'=>'点赞失败'];
			if ($res) {
				$resdata = ['code'=>1, 'data'=>[], 'message'=>'点赞成功'];
				$this->add_zan_message($dynamic_id, $user_id);
			}
		} else {
			$res = db('zan_table')->where('dynamic_id', $dynamic_id)->where('user_id', $user_id)->delete();
			if ($res) {
				$resdata = ['code'=>2, 'data'=>[], 'message'=>'点赞取消成功'];
			}
		}
		return $resdata;
	}


	// 添加点赞消息记录
	public function add_zan_message ($dynamic_id, $user_id) {
		$userinfo = db('dynamic_table')->where('dynamic_id', $dynamic_id)->find();
		$send_userid = $userinfo['user_id'];
		$map['message_type'] = 1;
		$map['message_dynamic_id'] = $dynamic_id;
		$map['message_sen_dynamic_userid'] = $send_userid;
		$map['message_dynamic_userid'] = $user_id;
		$map['addtime'] = time();
		db('message_table')->insert($map);
	}


	// 评论动态
	public function add_commit() {
		Db::startTrans();
		$resdata = ['code'=>1, 'data'=>[], 'message'=>'评论成功'];
		try {
			$dynamic_id = input('dynamic_id', 74);
			$user_id = input('user_id',5);
			$commit_conent_id = input('commit_conent_id',31);
			$commit_two_userid = input('commit_two_userid',5);
			$commit_type = input('commit_type',1);
			$map['commit_dynamic_id'] = $dynamic_id;
			$map['commit_user_id'] = $user_id;
			$map['commit_content'] = input('commit_content', '回复他人评论');

			// 评论的类型,1一级评论,直接对动态进行评论 2,回复他人评论
			$map['commit_type'] = $commit_type;
			if ($commit_type == 2) {
				if ($commit_conent_id || $commit_two_userid) {
					$map['commit_conent_id'] = $commit_conent_id;
					$map['commit_two_userid'] = $commit_two_userid;
				} else {
					throw new \Exception("发布失败");
				}
			} else {
				$map['commit_conent_id'] = 0;
				$map['commit_two_userid'] = 0;
			}
			$map['addtime'] = time();
			$res = db('commit_table')->insertGetId($map);
			$msgres = true;
			if ($commit_type == 2) {
				// commit_two_userid
				$msgres = $this->add_pinlun_message($dynamic_id, $user_id, $commit_conent_id, $commit_two_userid);
			} else {
				$userinfo = db('dynamic_table')->where('dynamic_id', $dynamic_id)->find();
				$send_userid = $userinfo['user_id'];
				$msgres = $this->add_pinlun_message($dynamic_id, $user_id, $res, $send_userid);
			}
			if (!$res) {
				$resdata = ['code'=>0, 'data'=>[], 'message'=>'评论失败'];
				throw new \Exception("发布失败");
			}
			if (!$msgres) {
					throw new \Exception("发布失败");
				}
			Db::commit();
		} catch (Exception $e) {
			$resdata = ['code'=>0, 'data'=>[], 'message'=>'评论失败'];
			Db::rollback();
		}
		return $resdata;
	}


	// 添加评论消息记录
	public function add_pinlun_message ($dynamic_id, $user_id, $commit_id, $send_userid) {
		$map['message_type'] = 2;
		$map['message_dynamic_id'] = $dynamic_id;
		$map['message_sen_dynamic_userid'] = $send_userid;
		$map['message_dynamic_userid'] = $user_id;
		$map['message_commit_id'] = $commit_id;
		$map['addtime'] = time();
		$res = db('message_table')->insert($map);
		return $res;
	}


	// 评论列表
	public function commit_list() {
		$resdata = ['code'=>0, 'data'=>[], 'message'=>'请求失败'];
		$dynamic_id = input('dynamic_id', 74);
		$page = input('page');
		$pagenum = $page * 10;
		$list = Db::query("SELECT c.*,u.user_head_image,u.user_name FROM vip_commit_table as c LEFT JOIN vip_user_table as u ON c.commit_user_id=u.user_id where commit_dynamic_id=? order by addtime desc limit ?, 10 ", [$dynamic_id, $pagenum]);
		foreach ($list as $key => &$value) {
			$commit_two_userid = $value['commit_two_userid'];
			$commit_conent_id = $value['commit_conent_id'];
			$value['two_userinfo'] = db('user_table')->where('user_id', $commit_two_userid)->find();
			$value['two_commit'] = db('commit_table')->where('commit_id', $commit_conent_id)->find();
		}
		 // db('commit_table')->where('commit_dynamic_id',$dynamic_id)->limit($pagenum, 10)->select();
		if ($list) {
			$resdata = ['code'=>1, 'data'=>$list, 'message'=>'请求成功'];
		}
		return $resdata;
	}

}
