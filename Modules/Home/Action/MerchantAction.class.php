<?php
/**
 * 商户信息接口
 */
class MerchantAction extends AppAction
{
	protected $tab = 'Merchant';
	protected $type;
    public function _initialize()
    {
        $this->type = '';
    }

	/**
	 * index 商户列表
	 * @param ar_id	地区id
	 * @param ins_id 行业id
	 * @param order	排序字段 倒序
	 * @return array：merlist商户信息，area地区信息，Industry行业信息
	 */
	public function merList(){
		$m = M($this->tab);
		if(I('ar_id')){
			$where['area'] = array('eq',I('ar_id'));
		}if(I('ins_id')){
			$where['ins_id'] = array('like','%,'.I('ins_id').',%');
		}if(I('order')){
			$order= I('order').' desc, sort desc';
		}else{
			$order= 'sort desc';
		}
		$field = 'id,name,address,postlong,postlat,per_money,description,img';
		$merlist = $this->getMer($where,$field,$order,false); // 商户基本信息
		if($merlist){
			foreach($merlist as $k=>$v){
				$merlist[$k]['pingfen'] = $this->starPraise($v['id'],false); // 获取商户评分
			}
		}
		$info['merlist'] = $merlist; //商户列表
		$info['area'] = R('Index/getArea'); //地区列表
		$info['industry'] = R('Index/getIndustry'); //行业列表
		//dump($info);exit;
		$this->setSuccess(true);
        $this->setMsg('商户信息列表');
        $this->SetInfo($info);
        $this->show($this->type);
	}

	/**
	 * merDetails 商户详情
	 * @param mer_id	商户id
	 * @return array：商户详情信息
	 */
	public function merDetails(){
		$mer_id = I('mer_id');
		$mer_arr = $this->merPingjia($mer_id,true); // 商户和评价信息
		if($mer_arr){
			$c_where['Chit.merchant'] = array('like','%,'.$mer_id.',%');
			$mer_arr['chitlist'] = R('Chit/Coupon',array($c_where,2)); //优惠券信息
			$this->setSuccess(true);
			$this->SetInfo($mer_arr);
	        $this->setMsg('商户详情');
	        $this->show($this->type);
		}else{
			$this->setSuccess(false);
	        $this->setMsg('该商户信息不存在');
	        $this->show($this->type);
		}
	}
	/**
	 * [pingjiaList 商户评价列表]
	 * @param mer_id	商户id
	 * @return [type] 商户基本信息和评价列表
	 */
	public function pingjiaList(){
	 	$mer_id = I('mer_id');
	 	$arr = $this->merPingjia($mer_id,false); // 商户和评价信息
	 	if($arr){
	 		$this->setSuccess(true);
			$this->SetInfo($arr);
	        $this->setMsg('商户评价');
	        $this->show($this->type);
	 	}else{
	 		$this->setSuccess(false);
	        $this->setMsg('该商户信息不存在');
	        $this->show($this->type);
	 	}
	 }
	

	/**
	 * [merPingjia 商户基本信息和评价列表]
	 * @param  string  $mer_id 商户id
	 * @param  boolean $isOne  评价信息条数 true:一条 false:全部
	 * @return [type]   商户基本信息和评价列表
	 */
	private function merPingjia($mer_id='',$isOne=true){
		$where['id'] = array('eq',$mer_id);
		$info = $this->getMer($where); // 商户基本信息
		if($info){
			$arr = $this->starPraise($mer_id,true,$isOne);
			$info['pingfen'] = $arr['pingfen']; //星级评价
			$info['ev_num'] = $arr['ev_num']; //消费评价数量
			$info['list'] = $arr['list']; //评价列表
			return $info;
		}else{
			return false;
		}
	}

	/**
	 * [getMer 商户基本信息]
	 * @param  array  $where 条件
	 * @param  string  $field 所需字段
	 * @param  string  $order 排序
	 * @param  boolean $isOne 商户信息条数 true:一条 false:全部
	 * @param  int $num 获取商户条数
	 * @return [array]   
	 */
	public function getMer($where='',$field='id,name,address,postlong,postlat,per_money,description,img,shop_sta,shop_end,son_tel',$order='',$isOne=true,$num=''){
		$m = M($this->tab);
		$map['isshow'] = array('eq',1);
		$whe = $map;
		if($where){
			$whe = array_merge($map,$where);
		}
		if($isOne){
			$info = $m->where($whe)->field($field)->find();
		}else if($num){
			$info = $m->where($whe)->field($field)->limit($num)->order($order)->select();
		}else{
			$info = $m->where($whe)->field($field)->order($order)->select();
		}
		return $info;
	}

	/**
	 * [Starpraise 星级评分和评价列表]
	 * @param string  $mer_id 商户id
	 * @param boolean $type   true调用评论信息
	 * @param integer $isOne  评价信息条数 true:一条 false:全部
	 */
	public function starPraise($mer_id='',$type=false,$isOne=true){
		$info = '';
		if($type){ //返回数据带评价信息
			$m = D('EvaluateView');
			$arr = $m->where('Evaluate.mer_id='.$mer_id)->order('Evaluate.addtime desc')->select();
			if($arr){
				$info['ev_num'] = count($arr);
				$score = '';
				if($arr){
					foreach($arr as $ek=>$ev){
						$score = $score+$ev['score'];
					}
					$info['pingfen'] = round($score/$info['ev_num'],1);//星级评分
				}
				if($isOne){
					$info['list'] = $arr[0]; //用户评论最新一条数据
				}else{
					$info['list'] = $arr;
				}
			}
		}else{ //仅仅返回星级评分
			$arr = M('Evaluate')->where('mer_id='.$mer_id)->field('score')->select();
			if($arr){
				$num = count($arr);// 评价数
				$score = '';
				foreach($arr as $k=>$v){
					$score = $score+$v['score'];
				}
				$info = round($score/$num,1);//星级评分
			}
		}
		return $info;	
	}

}