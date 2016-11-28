<?php
//数据统计表
class StatisticsAction extends CommonAction 
{	
	public $tab='Links';
	public $intab='Industry';
	public function __construct() {
		parent::__construct();
		R('Actlog/recorde');	//日志
	}
	public function index()
	{
		$addtime['addtime'] =array('neq','');
		$duantime['start_time'] =array('neq','');
		$duantime['end_time'] =array('neq','');
		if($this->_post('endTime') && $this->_post('staTime')){
			$addtime['addtime']=array('between',array(strtotime(I('post.staTime')),strtotime(I('post.endTime'))));
			$duantime['start_time']=array('egt',strtotime(I('post.staTime')));
			$duantime['end_time']=array('elt',strtotime(I('post.endTime')));
		}else{
			if($this->_post('staTime')){
				$addtime['addtime']=array('egt',strtotime(I('post.staTime')));
				$duantime['start_time']=array('egt',strtotime(I('post.staTime')));
			}if($this->_post('endTime')){
				$addtime['addtime']=array('elt',strtotime(I('post.endTime')));
				$duantime['end_time']=array('elt',strtotime(I('post.endTime')));
			}
		}
		$ins['ins_id'] =array('neq','');
		$industry['industry'] =array('neq','');
		if($this->_post('industry')){
			$ins['ins_id']=array('like','%,'.I('post.industry').',%');
			$industry['industry']=array('like','%,'.I('post.industry').',%');
		}
		$iss['isshow'] = array('eq',1);//状态开启
		$sta['status'] = array('eq',1);//状态开启
		$area['area'] =array('neq','');
		if(cookie('AREA')){//地区
			$area['area'] = array('eq',cookie('AREA'));
		}

		$data = array('f_num'=>0,'u_num'=>0,'m_num'=>0,'a_num'=>0,'c_num'=>0,'y_num'=>0,'g_num'=>0,'l_num'=>0);
		//用户注册量
		$ser_where =array_merge($iss,$addtime,$area);
		$data['u_num'] = M('Servuser')->where($ser_where)->field('id')->count();
		//商户注册量
		$m_where =array_merge($ser_where,$ins); 
		$data['m_num'] = M('Merchant')->where($m_where)->field('id')->count();
		
		//用户访问量
		$data['f_num'] = $data['u_num']+20;

		//活动数量
		$a_where =array_merge($iss,$ins,$area,$duantime);
		$data['a_num'] = M('Active')->where($a_where)->field('id')->count();
		
		//优惠券
		$c_where =array_merge($sta,$industry,$area,$duantime);
		$c_arr = M('Chit')->where($c_where)->field('id,num,r_num,u_num,end_time')->select();
		
		$c_id = '';
		foreach($c_arr as $k=>$v){
			$data['c_num'] = $data['c_num']+$v['num']; // 优惠券数量/发放数量
			$data['y_num'] = $data['y_num']+$v['u_num']; //使用数量
			if($v['end_time']<time()){
				$data['g_num'] = $data['g_num']+$v['num'];//过期数量
			}
			$c_id.= $c_id?','.$v['id']:$v['id'];
		}
		//领取数量
		if($c_id){
			$data['l_num'] = M('Chitlist')->where('c_id in('.$c_id.')')->field('id')->count(); 
		}
		$this->assign('data',$data);
		$this->assign('industry',$this->getData('Industry','id,name',array('sort'=>'asc')));	//行业
		$this->assign('sea',$this->_post());
		$this->display();
	}
}
?>