<?php
Class ChitViewModel extends ViewModel{
	public $viewFields = array(
		'Chit'=>array('id','title'=>'name','start_time','end_time','cate','price','price2','rule'=>'content','status'=>'isshow', '_type'=>'left'),
		'Area'=>array('ar_name','ar_id','_on'=>'Chit.area=Area.ar_id'),
	);   
}

?>