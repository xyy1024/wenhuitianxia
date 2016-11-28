<?php
Class ChitlistViewModel extends ViewModel{
	public $viewFields = array(
		'Chitlist'=>array('id','number','c_title','c_cate','price','price2','start_time','end_time','is_use','use_price','addtime','use_time','link_id', '_type'=>'left'),
		'Servuser'=>array('mobile','name','_on'=>'Servuser.id=Chitlist.u_id'),
   );
}
?>