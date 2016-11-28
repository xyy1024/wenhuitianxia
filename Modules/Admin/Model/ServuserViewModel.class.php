<?php
Class ServuserViewModel extends ViewModel{
	public $viewFields = array(
		'Servuser'=>array('id','mobile','name','Img','mer_id','isshow','addtime', '_type'=>'left'),
		'Area'=>array('ar_name','_on'=>'Servuser.area=Area.ar_id'),
	);   
}

?>