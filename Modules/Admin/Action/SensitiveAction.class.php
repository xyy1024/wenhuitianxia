<?php
//敏感词汇管理
class SensitiveAction extends CommonAction{
	protected $tab = 'Sensitive';
	public function __construct(){
		parent::__construct();
		R('Actlog/recorde');	//日志
	}
	public function index(){
		$map = '';
		$search = '';
		if(I('contentt')=='0'){
			$map['name']=array('like','%'.I('title').'%');
		}
		import('ORG.Util.Pages2');
		$m = M($this->tab);
		$count = $m->where($map)->count();
		if(I('stp') == 'all'){	//显示全部
			$p_num = $count;
		}else{
			$p_num = 20;
		}
		$Page = new Pages2($count,$p_num);
		if(intval(I('post.page'))) $Page->nowPage=abs(intval(I('post.page')))>$Page->totalPages?$Page->totalPages:abs(intval(I('post.page')));
		$lists = $m->order('sort desc')->where($map)->page($Page->nowPage.','.$Page->listRows)->select();
		$PageConfig=array('prev'=>'<i class="fa fa-chevron-left"></i>','next'=>'<i class="fa fa-chevron-right"></i>','theme'=>'%prePage% %linkPage% %nextPage%');
		$Page->config=$PageConfig;
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('totalRows',$Page->totalRows);
		$this->assign('totalPages',$Page->totalPages);
		$this->assign('nowPage',$Page->nowPage);
		$this->assign('lists',$lists);
		$this->assign('search',I(''));
		$this->display();
	}

	public function add(){
		$m = D($this->tab);
		if($this->isPost()){
			if ($m->create()) {
				$lastid = $m->add();
				if ($lastid !== false) {
					$this->success('敏感词汇添加成功',U('index'));
					exit;
				}else {
					$this->error('敏感词汇添加失败!');
					exit;
				}
			} else {
				$this->error($m->getError());
				exit;
			}
			exit;
		}
		$maxsort=$m->max('sort');			
		$this->assign('maxsort',$maxsort+1);
		$this->display();
	}
	public function edit(){
		$m = D($this->tab);
		if ($this->isPost()){
			if ($m->create()) {
				$map['id']=array('eq',$this->_param('id'));
				$affected = $m->where($map)->save();
				if ($affected){
					$this->success('敏感词汇修改成功',U('index'));
					exit;
				}else {
					$this->error('敏感词汇修改失败');
					exit;
				}
			}else {
				$this->error($m->getError());
				exit;
			}
		}
		$id = I('get.id');
		$data = $m->where('id='.$id)->find();
		$this->assign('data', $data);
		$this->display();
	}
	public function delete(){
		$m=M($this->tab);
		if($this->isGet()){
			$map['id']=array('eq',$this->_param('id'));
			$aa=$m->where($map)->delete();
			if($aa){
				$this->success('删除敏感词汇成功',U('index'));
			}else{
				$this->error('删除敏感词汇失败');
			}
		}
	}
}
?>