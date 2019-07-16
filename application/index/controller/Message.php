<?php
namespace app\index\controller;
use JMessage\JMessage;
use JMessage\IM\Group;
use JMessage\IM\User;
use \think\Exception;

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
		} catch (Exception $e) {
			$registsuc = false;
		}
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

}