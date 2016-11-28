<?php
class AreaAction extends CommonAction{
	protected $tab = 'Area';
	public function __construct(){
		parent::__construct();
		R('Actlog/recorde');	//日志
	}
	public function index(){
		$map = '';
		$search = '';
		if(I('contentt')=='0')
		{
			$map['ar_name']=array('like','%'.I('title').'%');
		}if(I('contentt')=='1')
		{
			$map['ar_ename']=array('like','%'.I('title').'%');
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
		$lists = $m->order('ar_od desc,ar_ename asc')->where($map)->page($Page->nowPage.','.$Page->listRows)->select();
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
			$ar_name = I('ar_name');
			if ($m->create()) {
				$lastar_id = $m->add();
				if ($lastar_id !== false) {
					$this->success('地区添加成功',U('index'));
					$li_data['area'] = $lastar_id;
					$li_data['addtime'] = time();
					foreach(C('CHIT_LINKS') as $ck=>$cv){
						$li_data['ins_id'] = $ck;
						$li_data['logo'] = $cv;
						M('Links')->data($li_data)->add();
					}
				}else {
					$this->error('地区添加失败!');
					exit;
				}
			} else {
				$this->error($m->getError());
				exit;
			}
			exit;
		}
		$this->display();
	}
	public function edit(){
		$m = D($this->tab);
		if ($this->isPost()){
			if ($m->create()) {
				$map['ar_id']=array('eq',$this->_param('ar_id'));
				$affected = $m->where($map)->save();
				if ($affected){
					$this->success('地区修改成功',U('index'));
					exit;
				}else {
					$this->error('地区修改失败');
					exit;
				}
			}else {
				$this->error($m->getError());
				exit;
			}
		}
		$ar_id = I('get.ar_id');
		$data = $m->where('ar_id='.$ar_id)->find();
		$this->assign('data', $data);
		$this->display();
	}
	public function delete(){
		$m=D($this->tab);
		if($this->isGet()){
			$map['ar_id']=array('eq',$this->_param('ar_id'));
			$aa=$m->where($map)->delete();
			if($aa){
				$this->success('删除地区成功',U('index'));
			}else{
				$this->error('删除地区失败');
			}
		}
	}
	//异步获取地区拼音
	public function Pinyin(){
		import('ORG.Util.Pinyin');
		$py = new PinYin();
		$data = $py->getAllPY(I('diqu'));
		echo $data;
	}
/**
 * 获取图片 多图片尺寸
 */
	public function getchicun()
	{
		$str='200*300';
		$data = $str?$str:'未定义';
		$data = $str?$str:'未定义';
		echo json_encode($data);
	}
}
?>