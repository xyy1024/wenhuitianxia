<?php
class ChitAction extends AppAction
{
	public function _initialize() {
		parent::_initialize();		
	}

	

	/**
	 * 优惠券列表
	 */
	public function Couponlist()
	{
		$where['Chit.status']=array('eq','1');	
		$where['Chit.start_time']=array('elt',time());
		$where['Chit.end_time']=array('egt',time());
		$info['Couponlist'] = $this->Coupon($where);//优惠券列表
		$info['area'] = R('Index/getArea'); //地区列表
		$info['industry'] = R('Index/getIndustry'); //行业列表
		//dump($info);exit;
		$this->setSuccess(true);
        $this->setMsg('优惠券信息列表');
        $this->setInfo($info);
		$this->show($this->type);
	}
	/**
	 * 优惠券
	 * @param  map 条件
	 * @param  limit 获取条数
	 * @return  array [优惠券列表]
	 */
	public function Coupon($map='',$limit='')
	{
		if(I('ar_id')){
			$where['Chit.area']=array('eq',I('ar_id'));
		}
		if(I('ins_id')){
			$where['Chit.industry']=array('like','%,'.I('ins_id').',%');
		}
		$whe=array_merge($where,$map);
		if($limit){
			$m=D('ChitView')->where($whe)->order('id')->limit($limit)->select();
		}else{
			$m=D('ChitView')->where($whe)->order('id')->select();
		}
		return $m;
	}

	/**
	 * 优惠券详情
	 * @param  c_id 优惠券id
	 */
	public function Chitcont(){
		$c = M('Chit');
		$c_id=I('c_id');//优惠券id
		$c_wh['status'] = array('eq',1);
		$c_wh['id'] =array('eq',$c_id);
		$c_info = $c->where($c_wh)->field('id,title,start_time,end_time,cate,price,price2,rule')->find();
		if($c_info){
			$this->setSuccess(true);
			$this->setInfo($c_info);
			$this->show($this->type);
		}else{
			$this->setSuccess(false);
            $this->setMsg('优惠券信息有误');
            $this->show($this->type);
		}	
	}

	/**
	 * 领取优惠券
	 * @param  c_id 优惠券id
	 * @param  u_id 用户id
	 */
	public function getChit(){
		$n_data['number'] =$this->getNum();
		$m = M('Chitlist');
		$c = M('Chit');
		$c_id=I('c_id');//优惠券
		$u_id=I('u_id');//用户id
		if(!$u_id){
			$this->setSuccess(false);
            $this->setMsg('请登录');
            $this->show($this->type);
		}else{
			$where['c_id'] =array('eq',$c_id);
			$where['u_id'] =array('eq',$u_id);
			$where['addtime'] =array('eq',strtotime(date('Y-m-d')));
			$chitlist = $m->where($where)->field('id')->find();
			if($chitlist){
				$this->setSuccess(false);
	            $this->setMsg('您今天已经领取了这类优惠券');
	            $this->show($this->type);
			}else{
				$c_wh['status'] = array('eq',1);
				$c_wh['id'] =array('eq',$c_id);
				$c_wh['end_time']=array('egt',strtotime(date('Y-m-d')));
				$c_info = $c->where($c_wh)->field('id,title,num,r_num,area,start_time,end_time,cate,price,price2,link_id')->find();
				$map['c_id'] =array('eq',$c_id);
				$map['u_id'] =array('eq',$u_id);
				$map['start_time']=array('elt',$c_info['start_time']);
				$map['end_time']=array('egt',$c_info['end_time']);
				$map['is_use']=array('eq',1);
				$chochit = $m->where($map)->field('id')->find();
				if($chochit){
					$this->setSuccess(false);
		            $this->setMsg('您有未使用的优惠券，请使用');
		            $this->show($this->type);
				}
				if($c_info['r_num'] < $c_info['num'])
				{
					$data['u_id'] = $u_id;
					$data['c_id'] = $c_id;
					$data['c_title'] = $c_info['title'];
					$data['c_cate'] = $c_info['cate'];
					$data['price'] = $c_info['price'];
					$data['price2'] = $c_info['price2'];
					$data['start_time'] = $c_info['start_time'];
					$data['end_time'] = $c_info['end_time'];
					$data['link_id'] = $c_info['link_id'];
					$data['addtime'] = strtotime(date('Y-m-d'));				
					$re_id = $m->add($data); //插入优惠券列表
					//echo $m->getlastsql();exit;
					if($re_id){
						//获取串码
						$n_data['number'] =$this->getNum($c_info['cate'],$c_info['area'],$re_id);
						$re = $m->where('id='.$re_id)->data($n_data)->save();
						if($re){
							//修改chit表的领取数量+1
							$ce=$c->where($c_wh)->setInc('r_num');
							if($ce){
								$this->setSuccess(true);
					            $this->setMsg('优惠券领取成功');
					            $this->show($this->type);
							}
						}
					}else{
						$this->setSuccess(false);
			            $this->setMsg('优惠券领取失败');
			            $this->show($this->type);
					}
				}else{
						$this->setSuccess(false);
			            $this->setMsg('优惠券已经领取完');
			            $this->show($this->type);
					}
			}
		}
	}
	/**
	 * 使用优惠券
	 * id  优惠券id
	 * u_id 用户id
	 */
	public function useChit(){
		$m = M('Chitlist');
		$c = M('Chit');
		$c_id = I('c_id');	//优惠券id
		$u_id = I('u_id');	//用户id
		$where['c_id'] =array('eq',$c_id);  //优惠券id
		$where['u_id'] =array('eq',$u_id);		//用户id
		$where['use_time'] =array('eq',strtotime(date('Y-m-d')));
		$chochit = $m->where($where)->field('id')->find();
		//echo $m->getlastsql();exit;
		if($chochit){
			$this->setSuccess(false);
            $this->setMsg('今天您已经使用该优惠券');
            $this->show($this->type);
		}else{
			$map['c_id'] =array('eq',$c_id);  //优惠券id
			$map['u_id'] =array('eq',$u_id);  //用户id
			$map['is_use']=array('eq',1);
			$map['start_time']=array('elt',time());
			$map['end_time']=array('egt',time());
			if(I('ins_id')){
				$map['industry']=array('like', '%,'.I('ins_id').',%');//分行业消费
			}
			if(I('area_id')){
				$map['area']=array('like', I('area_id'));//分地区消费
			}
			if(I('mer_id')){
				$map['merchant']=array('like', '%,'.I('mer_id').',%');//分商户消费
			}
			$usechit = D('ChitlistView')->where($map)->field('Chitlist.id')->find();
			if($usechit){
				$data['is_use'] = 2;
				$data['use_time'] = strtotime(date('Y-m-d'));
				$re = $m->where($map)->save($data);//更改优惠券使用状态
				if($re){
					$info_id = $m->where(array('c_id'=>$c_id,'u_id'=>$u_id,'is_use'=>'2'))->getField('c_id');
					//更改优惠券中已使用数量+1
					$c->where('id='.$info_id)->setInc('u_num');
					$this->setSuccess(true);
		            $this->setMsg('优惠券使用成功');
		            $this->show($this->type);
				}else{
					$this->setSuccess(false);
		            $this->setMsg('优惠券使用失败，请确认后再使用');
		            $this->show($this->type);
				}	
			}else{
				$this->setSuccess(false);
	            $this->setMsg('优惠券信息有误，请确认');
	            $this->show($this->type);
			}
		}
	}


	/**
	 * 获取优惠券串码 16位  (1位优惠券类型+2位地区+6位日期+7位优惠券id)
	 * @param  string $cate 优惠券类型 
	 * @param  string $area 地区       
	 * @param  string $id   优惠券id  
	 * @return [type]       string
	 */
	private function getNum($cate='',$area='',$id=''){
		$ca_arr = C('CHIT_CATE');
		foreach($ca_arr as $k=>$v){
			if($v==$cate){
				$ca_id = $k;
			}
		}
		$area = rand(0,9).$area;
		$id = rand(1000000,9999999).$id;
		$cnum = $ca_id.substr($area,-2).rand(10,99).date('md').substr($id,-7);
		return $cnum;
	}
	

}