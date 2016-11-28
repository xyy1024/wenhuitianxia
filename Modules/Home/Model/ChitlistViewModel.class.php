<?php
Class ChitlistViewModel extends ViewModel{
    public $viewFields = array(
    	'Chitlist'=>array('id','u_id','c_id','c_title','c_cate','start_time','end_time','is_use','use_time','link_id','_type'=>'left'),
        'Chit'=>array('id','area','title','num','r_num','cate','price','price2','rule','status','industry','merchant','_on'=>'Chitlist.c_id=Chit.id'),
    );
}