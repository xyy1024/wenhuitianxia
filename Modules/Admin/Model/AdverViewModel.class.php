<?php
Class AdverViewModel extends ViewModel{
	public $viewFields = array(
		'Adver'=>array('id','cid','aid','name','url','logo','isshow','addtime','sort','area', '_type'=>'left'),
		/*'Adver_category'=>array('catname', '_type'=>'left','_on'=>'Adver.cid=Adver_category.id'),*/
		'Admin'=>array('name'=>'aname', '_on'=>'Adver.aid=Admin.id'),
	);   
}

?>