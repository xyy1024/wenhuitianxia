<?php
Class LinksModel extends Model{
	//自动完成
	protected $_auto = array (
    	array('addtime','time',1,'function'),// 新增 	   	     对time字段在新增的时候写入当前时间戳
    	array('updatatime','time',2,'function'),  // 新增和修改 	 对optime字段 写入当前时间戳
		//array('ins_id','getUid',3,'callback'),   // 新增 	   	     对uid字段在新增的时候写入当前用户id
	);
	
	//自动验证
	protected  $_validate =array(
		array('area','require','所属地区必须选择',0,'',3),				//新增和修改标题    标题必须填写、
		array('ins_id','checkins','请选择所属行业',1,'callback',3),				//新增和修改标题    标题必须填写、
		array('logo','require','模版背景图片必须上传',0,'',3),				//新增和修改标题    标题必须填写、
		//array('title','checkTitle','标题已经存在',0,'callback',1),	//新增标题 			标题是否存在
	);
	protected function getUid(){
		$ins_id='';
		if($_POST['ins_id']){
			if(in_array('all',$_POST['ins_id'])){
				$ins_id = 'all';
			}else{
				$ins_id = ','.implode(',',$_POST['ins_id']).',';
			}
		}
		return $ins_id;
	}
	protected function checkins(){
		if(empty($_POST['ins_id'])){
			return false;
		}else{
			return true;
		}
	}
   
}

?>