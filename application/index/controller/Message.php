<?php
namespace app\index\controller;
use JMessage\JMessage;
use JMessage\IM\Group;
use JMessage\IM\User;
use \think\Exception;
use \think\Db;
use \think\Image;

const APPKEY = 'fd96be9326ca8466881539de';
const MASTERSECRET = 'de1ed90e61eb6690c5a0f363';
class Message {
	
	public $client;
	public function __construct () {
		$this->client = new JMessage(APPKEY, MASTERSECRET);
	}

	// 注册极光用户
	public function regise_user ($username) {
		$user = new User($this->client);
		$password = 'qwer123456789';
		$res = $user->register($username, $password);
		$obj = $res['body'][0];
		$registsuc = true;
		try {
		 $error =	$obj['error'];
		 if ($error) {
				$registsuc = false;
		 }
		} catch (Exception $e) {
			$registsuc = true;
		}
		// $this->$update_info($username, $map);
		return $registsuc;
	}

	// 创建聊天室
	public function create_chatroom () {
		$useranme = input('username');
		$grounname = input('grounname');
		$members = input('username', []);
		$description = input('description');
		$flag = input('flag', 2);
		$group  = new Group($this->client);
		$res = $group ->create($useranme, $grounname, $description, $members, '', $flag);
		$obj = $res['body'];

		$map['room_name'] = $useranme;
		$map['room_desbute'] = $description;
		$map['room_jpush_id'] = $obj['gid'];
		$map['room_addtime'] = time();
		$addres = db('chatroom_table')->insert($map);

		$resdata = ['code'=>0, 'data' => $res, 'message'=> '聊天室创建失败'];
		if ($addres) {
			$resdata = ['code'=>1, 'data' => $res, 'message'=> '聊天室创建成功'];
		} else {
			$group ->delete($obj['gid']);
		}

		return $resdata;
	}

	// 添加成员到聊天室
	public function add_menbers () {
		$roomid = input('roomid');
		$menbers = explode(',', input('menbers'));
		$group  = new Group($this->client);
		$res = $group->addMembers($roomid, $menbers);
		$obj = $res['body'];
		$resdata = ['code'=>1, 'data' => $res, 'message'=> '添加成功'];
		if ($obj) {
			$resdata = ['code'=>0, 'data' => $res, 'message'=> '添加失败'];
		}
		return $resdata;
	}


	// 发布约炮请求
	public function add_chat_dynamic () {
		Db::startTrans();
		$resdata = [];
		try {
			$map['chat_desbute'] = input('chat_desbute');
			$map['chat_userid'] = input('user_id');
			$map['chat_city_code'] = input('chat_cityid');
			$map['chat_addtime'] = time();
			Db::name('chatone_table')->insert($map);
			$res = Db::name('chatone_table')->getLastInsID();
			$file = request()->file('chat_images');
			$issicces = true;
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

	// 多图上传
	public function more_file_upload ($res) {
		$success = true;
		try {
			$array = [];
			$files = request()->file('chat_images');
	    foreach($files as $file){
	    	$info = $file->validate(['size' => 51200000, 'ext' => 'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads');
	      if($info){
	        $imagesrc = $info->getSaveName();
	        $image = \think\Image::open(ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . $imagesrc);
					$image->thumb(1200, 1200)->save(ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . $imagesrc);
	        $map['chat_image_path'] = $imagesrc;
	        $map['chatone_id'] = $res;
	        Db::name('chatimage_table')->insert($map);
	        $tres = Db::name('chatimage_table')->getLastInsID();
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
	    $upiscu = Db::name('chatone_table')->where('chat_id', $res)->update(['chat_image' => $imageskey]);
	    if (!$upiscu) {
	    	throw new \Exception("发布失败");
	    }
		} catch (Exception $e) {
			$success = false;
		}
    return $success;
	}


	// 约炮列表
	public function chat_lists () {
		$page = input('page', 0);
		$pagecount = $page * 10;
		$list = db('chatone_table')->where('chat_statue', 1)->limit($pagecount, 10)->order('chat_addtime desc')->select();
		foreach ($list as $key => &$value) {
			if ($value['chat_image']) {
				$value['chat_image'] = db('chatimage_table')->where('chatone_id', $value['chat_id'])->select();
			}
			$value['userinfo'] = db('user_table')->where('user_id', $value['chat_userid'])->find();
		}
		return ['code'=>1, 'data'=>$list, 'message'=>'请求成功'];
	}


	// 开始约炮
	public function chat_user () {
		$user_id = input('user_id');
		$userinfo = db('user_table')->where('user_id', $user_id)->find();
		return ['code'=>1, 'data'=>$userinfo, 'message'=>'请求成功'];
	}

	// 群列表
	public function group_lists() {
		$page = input('page', 0);
		$pagecount = $page * 10;
		$list = db('chatroom_table')->limit($pagecount, 10)->order('room_addtime desc')-select();
		return ['code'=>1, 'data'=>$list, 'message'=>'请求成功'];
	}

	// 我的约炮列表
	public function my_chat_lists () {
		$resdata = ['code'=>0, 'data'=>[], 'message'=>'暂无数据'];
		$page = input('page', 0);
		$user_id = input('userid', 29);
		$pagecount = $page * 10;
		$list = db('chatone_table')->where('chat_statue', 1)->where('chat_userid', $user_id)->limit($pagecount, 10)->order('chat_addtime desc')->select();
		$userinfo = db('user_table')->where('user_id', $user_id)->find();
		foreach ($list as $key => &$value) {
			if ($value['chat_image']) {
				$value['chat_image'] = db('chatimage_table')->where('chatone_id', $value['chat_id'])->select();
			}
			$value['userinfo'] = $userinfo;
		}
		if ($list) {
			$resdata = ['code'=>1, 'data'=>$list, 'message'=>'请求成功'];
		}
		return $resdata;
	}

	// 版本更新半段
	public function version_update () {
		$version = [
			'version' => 1,
			'links' => 'www.baidu.com',
			'is_update' => true,
			'desction' => '暂无描述'
		];
		return $version;
	}

	// 聊天室列表
	public function room_lists () {
		$resdata = ['code'=>0, 'data'=>[], 'message'=>'暂无数据'];
		$page = input('page', 0);
		$cityid = input('cityid', 1);
		$pagenum = $page * 10;
		$list = db('chatroom_table')->where('room_cityid', $cityid)->limit($pagenum, 10)->select();
		if ($list) {
			$resdata = ['code'=>1, 'data'=>$list, 'message'=>'请求成功'];
		}
		return $resdata;
	}
}