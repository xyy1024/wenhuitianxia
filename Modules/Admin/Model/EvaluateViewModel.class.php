<?php
Class EvaluateViewModel extends ViewModel{
	public $viewFields = array(
		'Evaluate'=>array('id','score','img','content','addtime','sort', '_type'=>'left'),
		'Servuser'=>array('mobile','name'=>'sername', '_type'=>'left','_on'=>'Servuser.id=Evaluate.ser_id'),
		'Merchant'=>array('name'=>'mername','area', '_on'=>'Merchant.id=Evaluate.mer_id'),
   );
}
?>