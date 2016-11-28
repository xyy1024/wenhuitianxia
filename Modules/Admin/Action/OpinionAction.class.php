<?php
/**
 * 意见反馈
 */
class OpinionAction extends CommonAction {
	public $tab='Opinion';
	public $tabView='OpinionView';
	public function __construct() {
		parent::__construct();
		R('Actlog/recorde');	//日志
	}
    public function index(){
		$Opinion=D($this->tabView);
		import('ORG.Util.Pages2');
		if($this->_post('ser')){
			$where['Servuser.name']=array('like','%'.I('ser').'%');
			$s['ser']=$this->_post('ser');
		}
		if($this->_post('mobile')){
			$where['Servuser.mobile']=array('like','%'.I('mobile').'%');
			$s['mobile']=$this->_post('mobile');
		}
		if($this->_post('addtime')){
			$where['Opinion.addtime']=array('between',array(strtotime(I('addtime').' 00:00:00'),strtotime(I('addtime').' 23:59:59')));
			$s['addtime']=$this->_post('addtime');
		}
		if($this->_post('content')){
			$where['Opinion.content']=array('like','%'.I('content').'%');
			$s['content']=$this->_post('content');
		}
		$this->assign('s',$s);
		if(cookie('AREA')){//地区
			$where['Opinion.area'] = cookie('AREA');
		}
		$count = $Opinion->where($where)->count();
		if(I('stp') == 'all'){	//显示全部
			$p_num = $count;
		}else{
			$p_num = 20;
		}
		$Page = new Pages2($count,$p_num);
		if(intval(I('post.page'))) $Page->nowPage=abs(intval(I('post.page')))>$Page->totalPages?$Page->totalPages:abs(intval(I('post.page')));
		
		$orderby='Opinion.addtime desc,Opinion.id desc';
		$list = $Opinion->order($orderby)->where($where)->page($Page->nowPage.','.$Page->listRows)->select();
		//echo $Opinion->getLastSql();
		$PageConfig=array('prev'=>'<i class="fa fa-chevron-left"></i>','next'=>'<i class="fa fa-chevron-right"></i>','theme'=>'%prePage% %linkPage% %nextPage%');
		$Page->config=$PageConfig;
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('totalRows',$Page->totalRows);
		$this->assign('totalPages',$Page->totalPages);
		$this->assign('nowPage',$Page->nowPage);
		$this->assign('list',$list);
		$this->display();
	}
	/**
	 * 删除单条意见反馈
	 */
	public function delete(){
		$Opinion=D($this->tab);
		if($this->isGet()){
			$map['id']=array('eq',$this->_param('id'));
			$aa=$Opinion->where($map)->delete();
			if($aa){
				$this->success('删除成功！',U('index'));
			}else{
				$this->error('删除失败！');
			}
		}
	}
	/**
	 * 删除勾选
	 */
	public function form(){
		$Opinion=D($this->tab);
		if($this->isPost()){
			$map['id']=array('in',$this->_param('check'));
			if($this->_param('Delete')){
				$aa=$Opinion->where($map)->delete();
				if($aa){
					$this->success('删除成功！',U('index',array('p'=>$this->_param('p'))));
				}else{
					$this->error('删除失败！');
				}
			}
		}
	}
}