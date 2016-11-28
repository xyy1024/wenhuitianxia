<?php
Class ActiveModel extends Model{
	//自动完成
	protected $_auto = array (
		array('start_time','strtotime',3,'function'),
		array('end_time','strtotime',3,'function'),
    	array('addtime','time',1,'function'),// 新增 	   	     对time字段在新增的时候写入当前时间戳
		array('ins_id','getUid',3,'callback'),   // 新增 	   	     对uid字段在新增的时候写入当前用户id
	);
	
	//自动验证
	protected  $_validate =array(
		array('area','require','请选择所属地区',0,'',3),				//新增和修改标题    标题必须填写、
		array('name','require','请填写活动名称',0,'',3),				//新增和修改标题    标题必须填写、
		array('start_time','require','请选择活动开始时间',0,'',3),				//新增和修改标题    标题必须填写、
		array('end_time','require','请选择活动结束时间',0,'',3),				//新增和修改标题    标题必须填写、
		array('ins_id','checkIndustry','请选择所属行业',1,'callback',3),
	);
	//验证行业不能空
	protected function checkIndustry()
	{
		if(I('post.ins_id') && count(array_filter(I('post.ins_id')))>=1)
			return true;
		else
			return false;
	}
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
}

?>