<?php
class AboutusAction extends CommonAction{
	protected $tab = 'Aboutus';
	protected $tabView = 'AboutusView';
	public function _initialize(){
		parent::_initialize();
		R('Actlog/recorde');	//日志
	}
	public function index(){
		$map = '';
		import('ORG.Util.Pages2');
		$m = D($this->tabView);
		if(cookie('AREA')){//地区
			$map['area'] = array('eq',cookie('AREA'));
		}
		$count = $m->where($map)->count();
		if(I('stp') == 'all'){	//显示全部
			$p_num = $count;
		}else{
			$p_num = 20;
		}
		$Page = new Pages2($count,$p_num);
		if(intval(I('post.page'))) $Page->nowPage=abs(intval(I('post.page')))>$Page->totalPages?$Page->totalPages:abs(intval(I('post.page')));
		$lists = $m->order('id desc')->where($map)->page($Page->nowPage.','.$Page->listRows)->select();
		$PageConfig=array('prev'=>'<i class="fa fa-chevron-left"></i>','next'=>'<i class="fa fa-chevron-right"></i>','theme'=>'%prePage% %linkPage% %nextPage%');
		$Page->config=$PageConfig;
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('totalRows',$Page->totalRows);
		$this->assign('totalPages',$Page->totalPages);
		$this->assign('nowPage',$Page->nowPage);
		$this->assign('lists',$lists);
		$this->display();
	}

	public function add(){
		$m = D($this->tab);
		if ($this->isPost()){
			if ($m->create()) {
				$affected = $m->add();
				if ($affected){
					$this->success('关于我们添加成功',U('index'));
					exit;
				}else {
					$this->error('关于我们添加失败');
					exit;
				}
			}else {
				$this->error($m->getError());
				exit;
			}
		}
		$this->assign('arealist',$this->getArea());
		$this->display();
	}
	public function edit(){ 
		$m = D($this->tab);
		if(cookie('AREA')){
			$where['area'] = array('eq',cookie('AREA'));
		}else{
			$where['id'] = array('eq',I('id'));
		}
		$data = $m->where($where)->find();
		$this->assign('data', $data);
		if ($this->isPost()){
			if ($m->create()) {
				if($data){
					$map['id']=array('eq',$this->_param('id'));
					$affected = $m->where($map)->save();
				}else{
					$affected = $m->add();
				}
				if ($affected){
					if(cookie('AREA')){
						$this->success('关于我们设置成功',U('edit'));
					}else{
						$this->success('关于我们设置成功',U('index'));
					}
					exit;
				}else {
					$this->error('关于我们设置失败');
					exit;
				}
			}else {
				$this->error($m->getError());
				exit;
			}
		}
		$this->assign('arealist',$this->getArea());
		$this->display();
	}
	public function delete(){
		$m=D($this->tab);
		if($this->isGet()){
			$map['id']=array('eq',$this->_param('id'));
			$aa=$m->where($map)->delete();
			if($aa){
				$this->success('删除关于我们成功',U('index'));
			}else{
				$this->error('删除关于我们失败');
			}
		}
	}

}