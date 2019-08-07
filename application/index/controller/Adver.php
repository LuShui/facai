<?php
namespace app\index\controller;
use think\Cache;
use think\Exception;
/**
 * 
 */
class Adver{

	public function index () {
		return view('index'); 
	}

	// 上传图片
	public function uploadonefile () {
		$imagesrc = '';
		$files =  request()->file('file');
		dump($files);
		if ($files) {
			foreach($files as $file){
	    	$info = $file->validate(['size' => 51200000, 'ext' => 'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads');
		      if($info){
		        $imagesrc = $info->getSaveName();
		        $image = \think\Image::open(ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . $imagesrc);
						$image->thumb(1200, 1200)->save(ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . $imagesrc);
		      }
		    }	
		}
		return $imagesrc;
	}


	// 广告列表
	public function ad_list () {
		$shop_code = input('user_shop_code', '');
		$list = db('adver_table')->where('ad_shop_code', $shop_code)->limit(0, 20)->order('ad_addtime desc')->select();
		$resdata = ['code' => 0, 'message'=>'暂无记录', 'data'=> []];
		if ($list) {
			$resdata = ['code' => 1, 'message'=>'获取数据成功', 'data'=> $list];
		}
		return $resdata;
	}

	// 店长下级列表
	public function shop_nest_list () {
		$shop_code = input('user_code', '5d4005e36a7f3');
		$page = input('page', 0);
		$pagenum = $page * 10;
		$list = db('user_table')->where('user_shop_code', $shop_code)->limit($pagenum, 10)->select();
		$resdata = ['code' => 0, 'message'=>'暂无记录', 'data'=> []];
		if ($list) {
			$resdata = ['code' => 1, 'message'=>'获取数据成功', 'data'=> $list];
		}
		return $resdata;
	}

}