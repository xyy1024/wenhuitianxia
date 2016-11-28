<?php
Class AboutusViewModel extends ViewModel{
	public $viewFields = array(
		'Aboutus'=>array('id','content','tel','email','addtime', '_type'=>'left'),
		'area'=>array('ar_name', '_on'=>'area.ar_id=Aboutus.area'),
	);   
}

?>