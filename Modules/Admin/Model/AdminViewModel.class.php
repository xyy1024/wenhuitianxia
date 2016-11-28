<?php
Class AdminViewModel extends ViewModel{
	public $viewFields = array(
		'Admin'=>array('id','username','isshow','addtime','ltime','lip','name','tel','email','area', '_type'=>'left'),
		'Authgroupaccess'=>array('group_id','_type'=>'left','_on'=>'Admin.id=Authgroupaccess.uid'),
		'Authgroup'=>array('title','_type'=>'left', '_on'=>'Authgroupaccess.group_id=Authgroup.id'),
		'Area'=>array('ar_name','_on'=>'Admin.area=Area.ar_id'),
	);   
}

?>