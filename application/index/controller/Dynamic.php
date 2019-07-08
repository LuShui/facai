<?php
namespace app\index\controller;
use think\Db;
use \think\File;
use \think\View;
use think\Exception;
class Dynamic extends Base{
	
	public function index () {
		return view('index');
	}

	// 发布动态
	public function send_dynamic () {
		Db::startTrans();
		$resdata = [];
		try {
			$map['user_id'] = input('user_id', 1);
			$map['dynamic_title'] = input('dynamic_title', '1231231223');
			$map['dynamic_cityid'] = input('dynamic_cityid', '12312321asdadsa3123213');
			$map['dynamic_time'] = time();
			$res = Db::name('dynamic_table')->insert($map);
			$issicces = true;
			$file = request()->file('dynamic_images');
			if ($file) {
				$issicces = $this->more_file_upload($res);
			}
			if ($res && $issicces) {
				$resdata = ['code' => 1, 'message' => '发布成功'];
				Db::commit();
			} else {
				throw new \Exception("发布失败");
			}
		} catch (Exception $e) {
			Db::rollback();
			$resdata = ['code' => 0, 'message' => $e->getMessage()];
		}
		return $resdata;
	}

	// 多图上传
	public function more_file_upload ($res) {
		$success = true;
		$array = [];
		$files = request()->file('dynamic_images');
    foreach($files as $file){
    	$info = $file->validate(['size' => 51200000, 'ext' => 'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads');
      if($info){
        $imagesrc = $info->getSaveName();
        $map['dynamic_image'] = $imagesrc;
        $map['dynamic_id'] = $res;
        $tres = Db::name('dynamic_images')->insert($map);
        if (!$tres) {
        	$success = false;
        } else {
        	array_push($array, $tres);
        }
      }else{
        // 上传失败获取错误信息
        $success = false;
      }  
    }
    $imageskey = "";
    foreach ($array as $key => $value) {
    	if ($imageskey == "") {
				$imageskey = $value;
    	} else {
    		$imageskey = $imageskey + ',' + $value ;
    	}
    }
    $upiscu = Db::name('dynamic_table')->where('dynamic_id', $res)->update(['dynamic_images' => $imageskey]);
    echo $res;
    return $success;
	}

	// 动态列表
	public function dynamic_list () {
		$page = input('page');
		$cityid = input('cityid');
		$pagenum = $page * 10;
		$list = db('dynamic_table')->where('dynamic_id', $cityid)->limit($pagenum, 10)->select();
		$resdata = ['code'=>0, 'data'=>[], 'message'=>'暂无数据'];
		if ($list) {
			$resdata = ['code'=>1, 'data'=>$list, 'message'=>'请求成功'];
		}
		return $resdata;
	}

	// 评论动态
	public function add_commit() {
		$map['commit_dynamic_id'] = input('dynamic_id');
		$map['commit_user_id'] = input('user_id');
		$map['commit_content'] = input('commit_content');
		$map['commit_type'] = input('commit_type');
		$map['commit_conent_id'] = input('commit_conent_id', 0);
		$map['commit_two_userid'] = input('commit_two_userid', 0);
		$map['addtime'] = time();
		$res = db('commit_table')->insert($map);
		$resdata = ['code'=>0, 'data'=>[], 'message'=>'评论失败'];
		if ($res) {
			$resdata = ['code'=>1, 'data'=>[], 'message'=>'评论成功'];
		}
		return $resdata;
	}


	// 评论列表
	public function commit_list() {
		$resdata = ['code'=>1, 'data'=>[], 'message'=>'请求失败'];
		$dynamic_id = input('dynamic_id');
		$page = input('page');
		$pagenum = $page * 10;
		$list = db('commit_table')->where('commit_dynamic_id',$dynamic_id)->limit($pagenum, 10)->select();
		if ($list) {
			$resdata = ['code'=>1, 'data'=>$list, 'message'=>'请求成功'];
		}
		return $resdata;
	}

}
