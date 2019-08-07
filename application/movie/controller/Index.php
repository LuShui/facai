<?php
namespace app\movie\controller;
class Index {
	
	// 小电影首页数据
	public function index () {
		$list = db('type')->field('type_id,type_name')->select();
		foreach ($list as $key => &$value) {
			$type_id = $value['type_id'];
			$value['vodlist'] = db('vod')->where('type_id', $value['type_id'])->field('vod_id,vod_name,vod_pic,vod_blurb')->order('vod_time_add desc')->limit(0, 7)->select();
		}
		$resdata = ['code' => 0, 'data'=>[], 'message' => '暂无数据'];
		if ($list) {
			$swiper = db('vod')->field('vod_id,vod_name,vod_pic,vod_blurb')->order('vod_id desc')->limit(0, 10)->select();
			$resdata = ['code' => 1, 'data'=>$list, 'swiper' => $swiper, 'message' => '请求成功'];
		}
		return $resdata;
	}


	public function moresoure () {
		$page = input('page', 0);
		$typeid = input('typeid', 1);
		$pagenum = $page * 12;
		$list = db('vod')->where('type_id', $typeid)->field('vod_id,vod_name,vod_pic,vod_blurb')->order('vod_time_add desc')->limit($pagenum, 10)->select();
		$resdata = ['code' => 0, 'data'=>[], 'message' => '暂无数据'];
		if ($list) {
			$resdata = ['code' => 1, 'data'=>$list, 'message' => '请求成功'];
		}
		return $resdata;
	}


	public function searchkey () {
		$key = '%' . input('key', '韩国') . '%';
		$list = db('vod')->where('vod_name', 'like', $key)->field('vod_id,vod_name,vod_pic,vod_blurb')->order('vod_time_add desc')->select();
		$resdata = ['code' => 0, 'data'=>[], 'message' => '暂无数据'];
		if ($list) {
			$resdata = ['code' => 1, 'data'=>$list, 'message' => '请求成功'];
		}
		return $resdata;
	}


	public function voddetil () {
		$vodid = input('vodid', 278);
		$detil = db('vod')->where('vod_id', $vodid)->find();
		return $detil;
	}

}