<?php
Class MerchantModel extends Model{
	//自动完成
	protected $_auto = array (
    	array('addtime','time',1,'function'),// 新增 	   	     对time字段在新增的时候写入当前时间戳
		array('ins_id','getUid',3,'callback'),   // 新增和修改 	   	     对uid字段在新增的时候写入当前用户id
		array('uscore','getScore',1,'callback'),   // 新增 	   	     对uid字段在新增的时候写入当前用户id
	);
	
	//自动验证
	protected  $_validate =array(
		array('area','require','请选择所属地区',0,'',3),				//新增和修改标题    标题必须填写、
		array('name','require','请填写商户名称',0,'',3),				//新增和修改标题    标题必须填写、
		array('ins_id','checkIndustry','请选择所属行业',1,'callback',3),
		array('shop_sta','require','请选择营业开始时间',0,'',3),				//新增和修改标题    标题必须填写、
		array('shop_end','require','请选择营业结束时间',0,'',3),				//新增和修改标题    标题必须填写、
		array('address','require','请填写商户详细地址',0,'',3),				//新增和修改标题    标题必须填写、
		array('postlong','require','请填写具体位置的经度',0,'',3),				//新增和修改标题    标题必须填写、
		array('postlat','require','请填写具体位置的纬度',0,'',3),				//新增和修改标题    标题必须填写、
		array('contacts','require','请填写商户联系人',0,'',3),				//新增和修改标题    标题必须填写、
		array('son_tel','require','请填写联系电话',0,'',3),				//新增和修改标题    标题必须填写、			//新增和修改标题    标题必须填写、
		array('son_tel','/^1[34578]\d{9}$/','联系电话格式不正确',0,'regex',1),  	//新增和修改联系电话
		array('son_tel','/^1[34578]\d{9}$/','联系电话格式不正确',2,'regex',2),  	//新增和修改用户	密码必须5位数以上
		//array('title','checkTitle','标题已经存在',0,'callback',1),	//新增标题 			标题是否存在
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
	//将管理员打分放入总评分下
	protected function getScore(){
		$uscore='';
		if($_POST['score']){
			$uscore = $_POST['score'];
		}
		return $uscore;
	}
}

?>