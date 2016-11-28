<?php
Class AdverModel extends Model{
	//自动完成
	protected $_auto = array (
		array('addtime','time',1,'function'),// 新增 	   	     对time字段在新增的时候写入当前时间戳
		array('updatatime','time',2,'function'),  // 新增和修改 	 对optime字段 写入当前时间戳
		array('aid','getUid',1,'callback'),   // 新增 	   	     对uid字段在新增的时候写入当前用户id
		//array('url','getUrl',3,'callback'), 
	);
	
	//自动验证
	protected  $_validate =array(
		array('area','require','所属地区必须选择',0,'',3),				//新增和修改标题    标题必须填写、
		array('name','require','标题必须填写',0,'',3),				//新增和修改标题    标题必须填写、
		array('type','require','链接类型必须选择',0,'',3),				//新增和修改标题    标题必须填写、
		array('logo','require','焦点图图片必须上传',0,'',3),				//新增和修改标题    标题必须填写、
		//array('title','checkTitle','标题已经存在',0,'callback',1),	//新增标题 			标题是否存在
	);
	protected function getUid(){
		return cookie('ADMIN_KEY');
	}
	/*protected function getUrl($url){
		return 'http://'.ltrim($url,'http://');
	}*/
   
}

?>