<?php
class AboutusModel extends Model{
	//自动完成
	protected $_auto = array (
    	array('addtime','addTime',1,'callback'),//  新增和编辑   	     对time字段在新增的时候写入当前时间戳
    //	array('uid','getUid',1,'callback'),   // 新增 	   	     对uid字段在新增的时候写入当前用户id
	);
	//自动验证
	protected $_validate = array(
		array('area', 'require', '所属地区必须选择', 0, '', 3),
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