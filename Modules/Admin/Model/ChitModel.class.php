<?php 
class ChitModel extends Model{
	//自动验证
	protected $_validate = array(
		array('area','require','地区不能为空',1,'',3),
		array('title','require','优惠券名称不能为空',0,'',3),
        array('cate','require','优惠券类型不能为空',0,'',3),
        array('price','require','优惠力度不能为空',0,'',3),
        array('price2','require','优惠力度满额数不能为空',0,'',3),
		array('num','require','优惠券数量不能为空',0,'',3),
		array('start_time','require','活动起始时间不能为空',0,'',3),
		array('end_time','require','活动结束时间不能为空',0,'',3),
		array('industry','checkIndustry','行业不能为空',1,'callback',3),
		array('merchant','checkMerchant','商户不能为空',1,'callback',3),
		array('link_id','checkLinks','优惠券模版不能为空',1,'callback',3),
	);
	//自动完成
	protected $_auto = array ( 
		array('start_time','strtotime',3,'function'),
		array('end_time','strtotime',3,'function'),
		array('addtime','time',1,'function'),
		array('edittime','time',3,'function'),
	);
	//验证行业不能空
	protected function checkIndustry()
	{
		if(I('post.industry') && count(array_filter(I('post.industry')))>=1)
			return true;
		else
			return false;
	}
	//验证商户不能为空
    protected function checkMerchant(){
        if(I('post.merchant') && count(array_filter(I('post.merchant')))>=1)
            return true;
        else
            return false;
    }
    //验证行业不能空
	protected function checkLinks()
	{
		if(I('post.link_id'))
			return true;
		else
			return false;
	}
}