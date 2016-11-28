<?php
Class FileViewModel extends ViewModel{
	public $viewFields = array(
		'Article'=>array('id','title','thumb','tid','img','file','vedio','content','_type'=>'left'),
		'Category'=>array('catname','_on'=>'Article.tid=Category.id'),
   );
}
?>