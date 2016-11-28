<?php
Class AdminModel extends Model{
	//自动完成
	protected $_auto = array (
    	array('addtime','time',1,'function'),// 新增 	   	     对time字段在新增的时候写入当前时间戳ltime
		array('ltime','time',1,'function'),
		array('lip','GETip',1,'callback'),  
    	array('updatatime','time',3,'function'),  // 新增和修改 	 对optime字段 写入当前时间戳
		array('password','md5',1,'function'), 	 //新增    对password字段在新增的时候使md5函数处理
    	array('password2','md5',1,'function'), //新增    对repassword在新增和修改的时候使md5函数处理 用于验证两次密码是否输入一致
/*    	array('wenzhang','wenzhang',3,'callback'), 
    	array('cates','cates',3,'callback'), */
	);
	
	//自动验证
	protected  $_validate =array(
		array('area','require','所属地区不能为空',0,'',3),				//新增和修改栏目    栏目必须选择
		array('username','require','登录账号不能为空',0,'',3),				//新增和修改栏目    栏目必须选择
		array('username','','登录账号已经存在！',0,'unique',3), // 在新增的时候验证name字段是否唯一
		array('password','require','密码不能为空',0,'',1),	//
		array('password','/^.{5,}$/','密码必须5位数以上',0,'regex',1),  	//新增和修改用户	密码必须5位数以上
		
		array('password2','password','两次密码不一致',0,'confirm',3),		//新增和修改用户	确认密码不正确
		array('role','require','请选择角色',0,'',3),
		array('name','require','名称必须填写',0,'',3),				//新增和修改标题    标题必须填写
	);
	protected function GETip(){
		return $_SERVER['REMOTE_ADDR'];
	}
	protected function wenzhang($str){
		return intval($str);
	}
	protected function cates($arr){
		return implode(',', array_filter($arr));
	}
}