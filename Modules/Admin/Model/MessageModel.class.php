<?php
Class MessageModel extends Model{

	//自动验证
	protected  $_validate =array(
		array('area','require','请选择推送地区',0,'',3),				//新增和修改标题    标题必须填写
		array('title','require','请填写推送标题',0,'',3),				//新增和修改标题    标题必须填写、
		//array('title','checkTitle','标题已经存在',0,'callback',1),	//新增标题 			标题是否存在
	);

	protected function addTime($date){
		$data=strtotime($date);
		return $data;
	}

}