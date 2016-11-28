<?php
Class MessageViewModel extends ViewModel{
	public $viewFields = array(
		'Message'=>array('id','title','content','user','type','addtime', '_type'=>'left'),
		'Area'=>array('ar_name','ar_id','_on'=>'Message.area=Area.ar_id'),
	);   
}

?>