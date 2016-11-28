<?php
//前台用户管理
Class ServuserAction extends CommonAction{

	private $tab = 'Servuser';
	private $tabView = 'ServuserView';
	public function __construct() {
		parent::__construct();
		R('Actlog/recorde');	//日志
	}
	
	public function index()
	{
		$Serv=D($this->tabView);
		import('ORG.Util.Pages2');
		$order['id']='desc';
		if(I('contentt')=='0')
		{
			$map['mobile']=array('like','%'.I('title').'%');
		}if(I('contentt')=='1')
		{
			$map['name']=array('like','%'.I('title').'%');
		}
		if(I('contentt')!='')
		{
			$this->assign('contentt',I('contentt'));
		}
		if(cookie('AREA')){//地区
			$map['area'] = array('eq',cookie('AREA'));
		}
		$count = $Serv->where($map)->count();
		if(I('stp') == 'all'){	//显示全部
			$ye = $count;
		}else{
			$ye = 20;
		}
		$Page = new Pages2($count,$ye);
		if(intval(I('post.page'))) $Page->nowPage=abs(intval(I('post.page')))>$Page->totalPages?$Page->totalPages:abs(intval(I('post.page')));
		// $nowPage = isset($_GET['p'])?$_GET['p']:0;
		$lists = $Serv->where($map)->page($Page->nowPage.','.$Page->listRows)->order($order)->select();
		//echo $Serv->getLastSql();
		$PageConfig=array('prev'=>'<i class="fa fa-chevron-left"></i>','next'=>'<i class="fa fa-chevron-right"></i>','theme'=>'%prePage% %linkPage% %nextPage%');
		$Page->config=$PageConfig;
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('totalRows',$Page->totalRows);
		$this->assign('totalPages',$Page->totalPages);
		$this->assign('nowPage',$Page->nowPage);
		$this->assign('lists',$lists);
		$this->assign('search',I('title'));
		$this->display();
	}
	//查看详情
	public function  look()
	{
		$id = I('id'); //用户id
		//用户基本信息
		$data=D($this->tabView)->where('id='.$id)->find();
		$this->assign('data',$data);
		//获取用户收藏信息
		$m_arr = M('Merchant')->field('id,name,ins_id')->where('id in('.substr($data['mer_id'],1,-1).')')->limit(3)->select();
		$this->assign('m_arr',$m_arr);
		$this->assign('ins_arr',$this->getData('Industry','id,name',array('sort'=>'asc')));	//行业
		//获取用户优惠券信息
		$c_arr = M('Chitlist')->where('u_id='.$id)->field('id,number,c_title,price,is_use,use_time,use_price')->limit(3)->select();
		$w_num = 0;
		$y_num = 0;
		$num = 0;
		$use_price = 0;
		foreach($c_arr as $kk=>$vv){
			$num++;
			$use_price = $use_price+$vv['use_price'];
			if($vv['is_use']==1){//未使用数量
				$w_num++;
			}else{
				$y_num++;
			}
		}
		$carr['count'] = $num;//已领取数
		$carr['w_num'] = $w_num;//未使用数
		$carr['y_num'] = $y_num;//已使用数
		$carr['use_price'] = $use_price;//消费金额
		$this->assign('carr',$carr);
		$this->assign('c_arr',$c_arr);
		$this->display(); 
	}
	//更多收藏商户
	public function  merlist()
	{

		import('ORG.Util.Pages2');
		$order['id']='desc';
		//用户基本信息
		$data=D($this->tabView)->where('id='.I('u_id'))->find();
		$this->assign('data',$data);
		//获取用户收藏信息
		$where['id'] = array('in',substr($data['mer_id'],1,-1));
		if(I('title')){
			$where['name'] =  array('like','%'.trim(I('title')).'%');
		}if(I('ins')){
			if(I('ins')=='all'){
				$where['ins_id']=array('eq','all');
			}else{
				$where['ins_id']=array('like','%,'.I('ins').',%');
			}
		}

		$count = M('Merchant')->where($where)->count();
		if(I('stp') == 'all'){	//显示全部
			$ye = $count;
		}else{
			$ye = 20;
		}
		$Page = new Pages2($count,$ye);
		if(intval(I('post.page'))) $Page->nowPage=abs(intval(I('post.page')))>$Page->totalPages?$Page->totalPages:abs(intval(I('post.page')));
		// $nowPage = isset($_GET['p'])?$_GET['p']:0;
		//$lists = M('Merchant')->where($where)->page($Page->nowPage.','.$Page->listRows)->order($order)->select();
		$m_arr = M('Merchant')->field('id,name,ins_id')->where($where)->page($Page->nowPage.','.$Page->listRows)->order($order)->select();
		$this->assign('m_arr',$m_arr);
		$this->assign('search',I(''));
		$this->assign('ins_arr',$this->getData('Industry','id,name',array('sort'=>'asc')));	//行业
		$PageConfig=array('prev'=>'<i class="fa fa-chevron-left"></i>','next'=>'<i class="fa fa-chevron-right"></i>','theme'=>'%prePage% %linkPage% %nextPage%');
		$Page->config=$PageConfig;
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('totalRows',$Page->totalRows);
		$this->assign('totalPages',$Page->totalPages);
		$this->assign('nowPage',$Page->nowPage);
		$this->display(); 
	}
	//更多用户优惠券
	public function  chitlist()
	{
		import('ORG.Util.Pages2');
		$order['id']='desc';
		if(I('name'))
		{
			$where['c_title']=array('like','%'.I('name').'%');
		}
		if(I('isuse')==3)
		{
			$where['end_time']=array('lt',time());
		}elseif(I('isuse'))
		{
			$where['is_use']=array('eq',I('isuse'));
			$where['end_time']=array('egt',time());
		}

		$where['u_id'] = array('eq',I('u_id'));

		$count = M('Chitlist')->where($where)->count();
		if(I('stp') == 'all'){	//显示全部
			$ye = $count;
		}else{
			$ye = 20;
		}
		$Page = new Pages2($count,$ye);
		if(intval(I('post.page'))) $Page->nowPage=abs(intval(I('post.page')))>$Page->totalPages?$Page->totalPages:abs(intval(I('post.page')));
		// $nowPage = isset($_GET['p'])?$_GET['p']:0;
		$lists = M('Chitlist')->where($where)->page($Page->nowPage.','.$Page->listRows)->order($order)->select();
		$w_num = 0;
		$y_num = 0;
		$num = 0;
		$use_price = 0;
		foreach($lists as $kk=>$vv){
			$num++;
			$use_price = $use_price+$vv['use_price'];
			if($vv['is_use']==1){//未使用数量
				$w_num++;
			}else{
				$y_num++;
			}
		}
		$carr['count'] = $num;//已领取数
		$carr['w_num'] = $w_num;//未使用数
		$carr['y_num'] = $y_num;//已使用数
		//echo $Serv->getLastSql();
		$PageConfig=array('prev'=>'<i class="fa fa-chevron-left"></i>','next'=>'<i class="fa fa-chevron-right"></i>','theme'=>'%prePage% %linkPage% %nextPage%');
		$Page->config=$PageConfig;
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('totalRows',$Page->totalRows);
		$this->assign('totalPages',$Page->totalPages);
		$this->assign('nowPage',$Page->nowPage);
		$carr['use_price'] = $use_price;//消费金额
		$this->assign('carr',$carr);
		$this->assign('c_arr',$lists);
		$this->assign('search',I(''));
		$this->display(); 
	}
	public function  edit()
	{
		$Serv=D($this->tab);
		$data=$Serv->where('id='.I('id'))->find();
		if($this->isPost()){
			if(!I('password')){
				unset($_POST['password']);
				unset($_POST['password2']);
			}else{
				$_POST['password']=md5(I('password'));
				$_POST['password2']=md5(I('password2'));
			}
			//添加用户
			if(!$Serv->create()){
				$this->error($Serv->getError());
			}else{
				$lastid=$Serv->where('id='.I('id'))->save();
				if($lastid>0){
					$this->success('用户修改成功',U('Servuser/index'));
				}else{
					$this->error('用户修改失败');
				}
			}
		}else{
			$this->assign('data',$data);
			$this->assign('arealist',$this->getArea());
			$this->display(); 
		}
	}
	
	public function add()
	{
		$Serv=D($this->tab);
		if($this->isPost()){
			//添加用户
			if(!$Serv->create()){
				$this->error($Serv->getError());
			}else{
				$lastid=$Serv->add();
				if($lastid>0){
					$this->success('系统用户添加成功',U('Servuser/index'));
				}else{
					$this->error('系统用户添加失败');
				}
			}
		}else{
			
			$this->assign('arealist',$this->getArea());
			$this->display(); 
		}
	}
	
	public function delete()
	{
		$where['id']=I('id');
		$Serv=D($this->tab);
		$data=$Serv->where($where)->find();
		if($data){
			$count=$Serv->where($where)->delete();
			if($count){
				$this->success('管理员删除成功',U('Servuser/index'));
			}else{
				$this->error('管理员删除失败');
			}
		}
	}
/**
 * 修改分组开启属性
**/
	public function editattr()
	{
		$table=$this->_post('table');
		$m=M($table);
		$where['id']=array('eq',$this->_post('id'));
		$data[$this->_post('f')]=$this->_post('status');
		$count=$m->where($where)->data($data)->save();
		if($count)
		{
			$msg['success']=true;
			$msg['msg']='状态已修改';
		}
		else
		{
			$msg['success']=false;
			$msg['msg']='状态修改失败';
		}
		echo json_encode($msg);
	}

}