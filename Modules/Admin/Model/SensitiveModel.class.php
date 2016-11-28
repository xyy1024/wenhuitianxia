<?php
class SensitiveModel extends Model{
	//自动完成
	protected $_auto = array (
    	array('addtime','addTime',1,'callback'),// 新增 	   	     对time字段在新增的时候写入当前时间戳
    //	array('uid','getUid',1,'callback'),   // 新增 	   	     对uid字段在新增的时候写入当前用户id
	);
	//自动验证
	protected $_validate = array(
		array('name', 'require', '敏感词汇名称不能为空', 0, '', 3),
		array('name', '', '敏感词汇名称已存在', 0, 'unique', 3),
	);

	protected function addTime($date){
		$data=time();
		return $data;
	}
	/*protected function getUid(){
		return cookie('ADMIN_KEY');
	}*/
}
?>