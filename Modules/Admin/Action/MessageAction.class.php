<?php
class MessageAction extends CommonAction {
	public $tab='Message';
	public $tabView='MessageView';
	public function __construct() {
		parent::__construct();
		R('Actlog/recorde');	//日志
	}
    public function index(){
		$Mess=D($this->tabView);
		import('ORG.Util.Pages2');
		if(cookie('ADMIN_CATES')){
			$map_cates['id'] = array('in',cookie('ADMIN_CATES'));
		}
		if(I('contentt')=='0')
		{
			$where['title']=array('like','%'.I('content').'%');
		}if(I('contentt')=='1')
		{
			$where['content']=array('like','%'.I('content').'%');
		}
		if(cookie('AREA')){//地区
			$where['area'] = array('eq',cookie('AREA'));
		}
		$count = $Mess->where($where)->count();
		if(I('stp') == 'all'){	//显示全部
			$p_num = $count;
		}else{
			$p_num = 20;
		}
		$Page = new Pages2($count,$p_num);
		if(intval(I('post.page'))) $Page->nowPage=abs(intval(I('post.page')))>$Page->totalPages?$Page->totalPages:abs(intval(I('post.page')));
		$orderby='addtime desc,id desc';
		$list = $Mess->order($orderby)->where($where)->page($Page->nowPage.','.$Page->listRows)->select();
		//echo $Mess->getLastSql();
		$PageConfig=array('prev'=>'<i class="fa fa-chevron-left"></i>','next'=>'<i class="fa fa-chevron-right"></i>','theme'=>'%prePage% %linkPage% %nextPage%');
		$Page->config=$PageConfig;
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('totalRows',$Page->totalRows);
		$this->assign('totalPages',$Page->totalPages);
		$this->assign('nowPage',$Page->nowPage);
		$this->assign('list',$list);
		$this->assign('search',I('content'));
		$this->display();
	}
	/**
	 * 发布消息推送
	 */
	public function add(){
		$Mess = D($this->tab);
		if($this->isPost()){
			if($_POST['user_type']==2 && !$_POST['user']){
				$this->error('请选择接收的用户');
			}
			$_POST['addtime']=strtotime(date('Y-m-d'));
			if($Mess->create()){
				$user_id = '';//接收者id
				if(empty($Mess->user)){
					$Mess->user = 'all';
					$map['area'] = array('eq',I('area'));
					$map['isshow'] = array('eq',1);
					$userlist = M('Servuser')->where($map)->getField('name,id');
					$user_id = implode(',',$userlist);
				}else{
					$Mess->user = implode(',',$Mess->user);
					$user_id = $Mess->user;
				}
				$lastid=$Mess->add();
				if($lastid){
					//R('JiguangApp/AuroraPush',array($user_id,$lastid,$_POST['title']));//极光推送(用户id多个)
					$this->success('消息通知推送成功',U('Message/index'));
				}else{
					$this->error('消息通知推送失败');
				}
			}else{
				$this->error($Mess->getError());
			}
		}else{
			$info = array();
			if(I('type')){
				$table = I('type').'View';
				$where['isshow'] = array('eq',1);
				$where['id'] = array('eq',I('id'));
				$info = D($table)->where($where)->find();
				if($info){
					if(I('type') == 'Active'){
						$info['type'] = '活动推送';
					}else{
						$info['type'] = '优惠券推送';
					}
					$str.= '起止时间：'.date('Y-m-d',$info['start_time']).'至'.date('Y-m-d',$info['end_time']).'。';
					$info['content'] = $str.$info['content'];
					$this->assign('data',$info);
				}else{
					$this->error('该信息不存在或状态已禁用，请确认后再推送');
				}	
			}
			$this->assign('arealist',$this->getArea());//地区
			$this->display();	
		}
	}
	/**
	 * ajax 异步获取用户
	 * @return json
	 */
	public function user_List(){
		if(!I('a_area')){
			$r['suc'] = false;
			$r['msg'] = '<font color="#b94a48">地区信息有误，请确认推送地区</font>';
			$r['data'] = '';
			echo json_encode($r);
			exit;
		}
		$where['area'] = array('eq',I('a_area'));
		$where['isshow'] = array('eq',1);
		$userlist = $this->getData('Servuser','id,name',array('id'=>'asc'),$where);
		/*echo M('Servuser')->getLastSql();
		dump($userlist);exit;*/
		if($userlist){
			$str='';
			foreach($userlist as $k => $v){
				$str.='<label class="checkbox-inline chit_1" style="width:100px;"><input type="checkbox" name="user[]" value="'.$k.'" ';
				$str.='>'.$v.'</label>';
			}
			$r['suc'] = true;
			$r['msg'] = '';
			$r['data'] = $str;
		}else{
			$r['suc'] = false;
			$r['msg'] = '无相关用户信息';
			$r['data'] = '';
		}
		echo json_encode($r);
	}
 /**
  * 查看推送
  */
	public function look(){
		$Mess=D($this->tabView);
		if($this->isPost()){	
				if($Mess->create()){
					$lastid=$Mess->where('id='.$this->_param('id'))->save();
					
					if($lastid){
						$this->success('内容修改成功',U('Message/index'));
					}else{
						$this->error('内容修改失败');
					}
				}else{
					$this->error($Mess->getError());
				}
		}else{
			$data=$Mess->where('id='.$this->_param('id'))->find();
			if($data['user']!='all'){
				$ser_arr = M('Servuser')->where('id in('.$data['user'].')')->field('name')->select();
				$ser_name = '';
				foreach($ser_arr as $v){
					$ser_name.= $ser_name?'，'.$v['name']:$v['name'];
				}
				$this->assign('ser_name',$ser_name);
			}
			$this->assign('data',$data);
			$this->display();
		}		
	}

	
	//删除文章
	public function delete(){
		$Mess=D($this->tab);
		if($this->isGet()){
			$map['id']=array('eq',$this->_param('id'));
			$aa=$Mess->where($map)->delete();
			if($aa){
				$this->success('删除成功！',U('index'));
			}else{
				$this->error('删除失败！');
			}
		}
	}

	public function form(){
		$Mess=D($this->tab);
		if($this->isPost()){
			$map['id']=array('in',$this->_param('check'));
			if($this->_param('Delete')){
				
				$aa=$Mess->where($map)->delete();
				if($aa){
					$this->success('删除成功！',U('index'));
				}else{
					$this->error('删除失败！');
				}
			}
		}
	}

}