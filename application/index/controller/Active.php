<?php
namespace app\index\controller;
use think\Cache;
use think\Exception;
class Active {

	// 报名参加活动 
	public function add_active () {
		Db::startTrans();
		$resdata = [];
		try {
			$map['active_id'] = input('active_id');
			$map['user_id'] = input('user_id');
			$map['name'] = input('name');
			$map['phone'] = input('phone');
			$map['desction'] = input('desction');
			$map['addtime'] = input('addtime');
			$res = db('addactive_table')->insert($map);
			$issicces = $this->more_file_upload($res);
			$resdata = ['code'=>0, 'message'=>'添加失败'];
			if ($res && $issicces) {
				$resdata = ['code' => 1, 'message' => '添加成功'];
			} else {
				throw new Exception("添加成功");
			}
		} catch (Exception $e) {
			$resdata = ['code' => 1, 'message' => $e->getMessage()];
		}
		return $resdata;
	}

	// 多图上传
	public function more_file_upload ($res) {
		$success = true;
		$map = [];
		$file = request()->file('dynamic_images');
    foreach($files as $file){
    	$info = $file->validate(['size' => 51200, 'ext' => 'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads');
      if($info){
        $imagesrc = $info->getSaveName();
        $map[] = ['dynamic_image'] = $imagesrc;
        $map[] = ['dynamic_id'] = $res;
      }else{
        // 上传失败获取错误信息
        $success = false;
      }    
    }
    $res = Db::name('activeimg_table')->insertAll($data);
    if (!$res) {
    	$success = false;
    }
    return $success;
	}
	
}