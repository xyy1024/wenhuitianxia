<?php
Class EvaluateViewModel extends ViewModel{
    public $viewFields = array(
        'Evaluate'=>array('id','mer_id','ser_id','score','img','content','addtime','_type'=>'left'),
        'Servuser'=>array('mobile','name','_on'=>'Evaluate.ser_id=Servuser.id'),
        'Merchant'=>array('name'=>'mer_name','per_money','address','img'=>'mer_img','_on'=>'Evaluate.mer_id=Merchant.id'),
    );
}