<?php
Class Adver_categoryModel extends CommonModel
{
	
	//自动完成
	protected $_auto = array (
		array('addtime','time',1,'function'),     // 新增      写入当前时间戳
		array('updatetime','time',3,'function'), //新增和修改  操作时间	
	);
	
	//自动验证
	protected  $_validate =array(
		array('catname','require','分类名称必须填写',0,'',3),				//新增和修改分类  	分类名称必须填写
		array('chicun','require','尺寸说明必须填写',0,'',3),					
	);
}

?>