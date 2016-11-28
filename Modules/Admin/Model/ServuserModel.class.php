<?php
Class ServuserModel extends Model{
	//自动完成
	protected $_auto = array (
    	array('addtime','time',1,'function'),// 新增 	   	     对time字段在新增的时候写入当前时间戳ltime
		array('ltime','time',1,'function'),
		array('lip','GETip',1,'callback'),  
    	array('updatatime','time',2,'function'),  // 新增和修改 	 对optime字段 写入当前时间戳
		array('password','md5',1,'function'), 	 //新增    对password字段在新增的时候使md5函数处理
    	array('password2','md5',1,'function'), //新增    对repassword在新增和修改的时候使md5函数处理 用于验证两次密码是否输入一致
	);
	
	//自动验证
	protected  $_validate =array(
		array('area','require','请选择所属地区',0,'',3),				//新增和修改栏目    栏目必须选择
		array('mobile','require','请填写手机号',0,'',3),				//新增和修改栏目    栏目必须选择
		array('mobile','','手机号已经存在！',0,'unique',1), // 在新增的时候验证name字段是否唯一
		/*array('password','require','密码不能为空',0,'',1),	//
		array('password','/^.{5,}$/','密码必须5位数以上',0,'regex',1),  	//新增和修改用户	密码必须5位数以上
		array('password2','password','两次密码不一致',0,'confirm',3),		//新增和修改用户	确认密码不正确*/
		//array('addr','require','填写具体位置',0,'',3),
	);
	protected function GETip(){
		return $_SERVER['REMOTE_ADDR'];
	}
}