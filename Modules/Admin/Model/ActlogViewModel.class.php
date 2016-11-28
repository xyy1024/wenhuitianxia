<?php
Class ActlogViewModel extends ViewModel{
	public $viewFields = array(
		'Actionlog'=>array('id','aid','done','addtime','area','_type'=>'left'),
		'Admin'=>array('username','_on'=>'Admin.id=Actionlog.aid'),		
	);   
}