<?php
Class ActiveViewModel extends ViewModel{
	public $viewFields = array(
		'Active'=>array('id','name','isshow','explain'=>'content','start_time','end_time', '_type'=>'left'),
		'Area'=>array('ar_name','ar_id','_on'=>'Active.area=Area.ar_id'),
	);   
}

?>