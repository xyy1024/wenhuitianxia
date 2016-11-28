<?php
Class AuthruleModel extends Model{
	//自动完成
	protected $_auto = array (
    		// array('addtime','time',1,'function'),// 新增 	   	     对time字段在新增的时候写入当前时间戳
    		// array('uid','getUid',1,'callback'),   // 新增 	   	     对uid字段在新增的时候写入当前用户id
  		// array('updatatime','time',3,'function'),  // 新增和修改 	 对optime字段 写入当前时间戳
	);
	
	//自动验证
	protected  $_validate =array(
		array('catid','require','请选择分类',0,'',3),				//新增和修改栏目    栏目必须选择
		array('title','require','规则名称不能为空',0,'',3),				//新增和修改标题    标题必须填写、
		//array('title','checkTitle','标题已经存在',0,'callback',1),	//新增标题 			标题是否存在
		array('name','require','规则内容不能为空',0,'',3),
		array('name','checkName','规则已经存在',0,'callback',3),
	);
	protected function getUid(){
		return session('ADMIN_KEY');
	}
	protected function checkName($name){
		$where['name']=array('eq',$name);
		if($id = I('id',0,'intval')){
			$where['id']=array('neq',$id);
		}
		$r=M('Authrule')->where($where)->count();
		if($r){
			return false;
		}else{
			return true;
		}
	}
}

?>