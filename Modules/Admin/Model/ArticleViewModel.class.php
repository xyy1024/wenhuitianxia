<?php
Class ArticleViewModel extends ViewModel{
	public $viewFields = array(
		'Article'=>array('id','tid','aid','uid','title','description','keywords','thumb','content','addtime','updatetime','onclick','sort','isshow','file','img','vedio','dotop','link'),
		'Category'=>array('catname','special','spe_val', '_type'=>'left','catename','_on'=>'Article.tid=Category.id'),
		'Admin'=>array('name', '_on'=>'Article.aid=Admin.id'),
   );
}
?>