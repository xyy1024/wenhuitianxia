<?php

class AboutAction extends AppAction
{
	public function _initialize() {
		parent::_initialize();		
	}

	/**
	 * 我的优惠券列表
	 * @param u_id 用户id
	 * @param use_type 是否使用
	 */
	public function Aboutcoupon()
	{
		if(I('u_id')){
			$where['status'] = array('eq','1');
			$where['u_id'] = array('eq',I('u_id'));
			if(I('use_type')==2){
				$where['is_use']=array('eq',2); 	//已使用
			}else if(I('use_type')==1){
				$where['is_use']=array('eq',1);	//未使用
				$where['start_time']=array('elt',time());
				$where['end_time']=array('egt',time());
			}else if(I('use_type')==3){
				$where['end_time']=array('lt',time()); //已过期
			}
			$Couponlist=D('ChitlistView')->where($where)->order('id')->select();
			$this->setSuccess(true);
			$this->setInfo($Couponlist);
			$this->show($this->type);
		}else{
				$this->setSuccess(false);
	            $this->setMsg('请登录');
	            $this->show($this->type);
		}
	}
	/**
	 * 相关推荐
	 * @param  ar_id 地区id
	 */
	public function merRecom(){
		if(!I('ar_id')){
			$this->setSuccess(false);
	        $this->setMsg('请选择相关地区');
	        $this->show($this->type);
		}else{
			$where['area'] = array('eq',I('ar_id'));
			$arr = R('Merchant/getMer',array($where,'','','',5));
			if($arr){
				$this->setSuccess(true);
				$this->SetInfo($arr);
		        $this->setMsg('相关推荐列表');
		        $this->show($this->type);
			}else{
				$this->setSuccess(false);
		        $this->setMsg('暂无相关推荐');
		        $this->show($this->type);
			}
		}	
	}
	
	/**
	 * 关于我们
	 * @param ar_id 地区id
	 * id,content,tel,email,area,addtime
	 */
	public function Aboutus()
	{
		$m=M('Aboutus');
		if(I('ar_id')){
			$where['area']=array('eq',I('ar_id'));
			$field = 'id,content,tel,email,addtime';
			$info['aboutlist'] = $m->where($where)->field($field)->find(); // 关于我们信息
			$info['area'] = R('Index/getArea'); //地区列表
			$this->setSuccess(true);
            $this->setMsg('关于我们');
            $this->SetInfo($info);
            $this->show($this->type);	
		}else{
			$this->setSuccess(false);
            $this->setMsg('请选择地区');
            $this->show($this->type);
		}		
	}
	


	
}