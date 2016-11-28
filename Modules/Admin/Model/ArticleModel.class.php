<?php
Class ArticleModel extends Model{
	//自动完成
	protected $_auto = array (
    	array('addtime','addTime',3,'callback'),// 新增 	   	     对time字段在新增的时候写入当前时间戳
    //	array('uid','getUid',1,'callback'),   // 新增 	   	     对uid字段在新增的时候写入当前用户id
    	array('dotopday','dotopday',3,'callback'),
    	array('dotopend','dotopend',3,'callback'),
    	array('updatetime','time',2,'function'),  // 新增和修改 	 对optime字段 写入当前时间戳
		array('aid','getUid',1,'callback'),   // 新增 	   	     对uid字段在新增的时候写入当前用户id
	);
	
	//自动验证
	protected  $_validate =array(
		// array('tid','require','请选择栏目',0,'',3),				//新增和修改栏目    栏目必须选择
		array('area','require','所属地区必须选择',0,'',3),				//新增和修改标题    标题必须填写、
		array('title','require','标题必须填写',0,'',3),				//新增和修改标题    标题必须填写、
		//array('title','checkTitle','标题已经存在',0,'callback',1),	//新增标题 			标题是否存在
	);
	protected function getUid(){
		return cookie('ADMIN_KEY');
	}
	protected function addTime($date){
		$data=strtotime($date);
		return $data;
	}
	/**
	 * 自动完成 置顶时间（天）
	 */
	protected function dotopday(){
		if($day = intval(I('dotopday')) && intval(I('dotop')) > 0){
			return $day;
		}else{
			return 0;
		}
	}
	/**
	 * 自动完成 置顶结束时间戳
	 */
	protected function dotopend(){
		if($day = intval(I('dotopday')) && intval(I('dotop')) > 0){
			$data=time() + $day * 86400;
			return $data;
		}else{
			return 0;
		}
	}
}