<?php
Class ChitViewModel extends ViewModel{
    public $viewFields = array(
        'Chit'=>array('id','area','title','cate','price','price2','status','start_time','end_time','_type'=>'left'),
        'Links'=>array('logo','_on'=>'Links.id=Chit.link_id'),
    );
}