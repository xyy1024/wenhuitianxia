<?php
Class OpinionViewModel extends ViewModel{
	public $viewFields = array(
		'Opinion'=>array('id','content','addtime', '_type'=>'left'),
		'Servuser'=>array('mobile','name', '_type'=>'left','_on'=>'Servuser.id=Opinion.ser_id'),
		'Area'=>array('ar_name', '_on'=>'Area.ar_id=Opinion.area'),
   );
}
?>