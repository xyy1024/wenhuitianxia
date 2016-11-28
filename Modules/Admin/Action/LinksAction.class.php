<?php
class LinksAction extends CommonAction 
{	
	public $tab='Links';
	public $intab='Industry';
	protected $imgsize = '710*180';
	public function __construct() {
		parent::__construct();
		R('Actlog/recorde');	//日志
	}
	public function index()
	{
		
		$Links=D($this->tab);
		//dump($cat);
		import('ORG.Util.Pages2');
		
		if(I('ins'))
		{
			if(I('ins')=='all'){
				$where['ins_id']=array('eq','all');
			}else{
				$where['ins_id']=array('eq',I('ins'));
			}
		}
		if(cookie('AREA')){//地区
			$where['area'] = array('eq',cookie('AREA'));
		}

		$count = $Links->where($where)->count();
		if(I('stp') == 'all'){
			$num=$count;
		}else{
			$num=20;
		}
		$Page = new Pages2($count,$num);
		if(intval(I('post.page'))) $Page->nowPage=abs(intval(I('post.page')))>$Page->totalPages?$Page->totalPages:abs(intval(I('post.page')));
		$orderby['sort']='desc';
		$orderby['id']='desc';
		$list = $Links->where($where)->order($orderby)->page($Page->nowPage.','.$Page->listRows)->select();
		//echo 1111;
		//echo $Links->getLastSql();exit;
		$PageConfig=array('prev'=>'<i class="fa fa-chevron-left"></i>','next'=>'<i class="fa fa-chevron-right"></i>','theme'=>'%prePage% %linkPage% %nextPage%');
		$Page->config=$PageConfig;
		$show=$Page->show();
		$this->assign('page',$show);
		$this->assign('search',I('ins'));
		$this->assign('totalRows',$Page->totalRows);
		$this->assign('totalPages',$Page->totalPages);
		$this->assign('nowPage',$Page->nowPage);
		$this->assign('list',$list);
		//获取行业信息
		$ins_arr = M($this->intab)->field('id,name')->order('id asc,sort desc')->select();
		$this->assign('ins_arr',$ins_arr);
		$this->assign('area',$this->getData('Area',$field='ar_id,ar_name',$order=array('ar_ename'=>'asc')));
		$this->display();
	}
	
	
	public function isshow($arrid=0){
		$Links=D($this->tab);
		if(I('id')){
			$map['id']=array('eq',I('id'));
			$ba=$Links->where($map)->find();
			if($ba['isshow']==1){
				$mdata['isshow']=0;
			}else{
				$mdata['isshow']=1;
			}
			$mdata['updatetime']=time();
			$aa=$Links->where($map)->save($mdata);
			if($aa){
				$this->success('审核成功！',U('index'),array('cid'=>I('cid'),'p'=>I('p')));
			}else{
				$this->error('审核失败！');
			}
		}else{
			$map['id']=array('in',$arrid);
			$mdata['isshow']=1;
			$mdata['updatetime']=time();
			$aa=$Links->where($map)->save($mdata);
			return $aa;
		}		
	}
	
	public function delete($allid=0){
		$Links=D($this->tab);
		
		if($this->isGet()){
			$map['id']=array('in',I('id'));
			//删除附件
			$art=$Links->where($map)->field('logo')->find();	
			if($art['logo']){//单图
				$this->delFile2($art['logo']);
			}
			$aa=$Links->where($map)->delete();
			if($aa){
				$this->success('删除成功！',U('index',array('cid'=>I('cid'),'p'=>I('p'))));
			}else{
				$this->error('删除失败！');
			}
		}else{
			$map['id']=array('in',$allid);
			$aa=$Links->where($map)->delete();
			return $aa;
		}
	}
	
	
	public function add(){
		$Links=D($this->tab);		
		if($this->isPost()){		
			if($Links->create(I('post.'))){
				$aa=$Links->add();
				if($aa){
					$this->success('添加优惠券背景模版成功！',U('index'));
				}else{
					$this->error('添加优惠券背景模版失败！');
				}
			}else{
				$this->error($Links->getError());
			}
			
		}else{
			//获取行业信息
			$ins_arr = M($this->intab)->field('id,name')->order('id asc,sort desc')->select();
			$this->assign('ins_arr',$ins_arr);
			$maxsort=$Links->where($map)->max('sort');			
			$this->assign('cat',$cat);
			$this->assign('maxsort',$maxsort+1);
			$this->assign('arealist',$this->getArea());
			$this->assign('imgsize',$this->imgsize);//图片尺寸
			$this->display();
		}
	}
	
	public function edit(){
		$Links=D($this->tab);
		if($this->isPost()){
			if($Links->create(I('post.'))){
				//删除附件
				if($_POST['logo'] != $_POST['oldlogo'])
				{
					del_attach($_POST['oldlogo']);//将attachments中的数据和本地文件删除
				}
				$aa=$Links->where('id='.I('id'))->save();
				if($aa){
					$this->success('修改成功！',U('index',array('cid'=>I('cid'),'p'=>I('p'))));
				}else{
					$this->error('修改失败！');
				}
			}else{
				$this->error($Links->getError());
			}
		}else{
			//获取行业信息
			$ins_arr = M($this->intab)->field('id,name')->order('id asc,sort desc')->select();
			$this->assign('ins_arr',$ins_arr);
			$data=$Links->where('id='.I('id'))->find();
			$this->assign('data',$data);
			$this->assign('arealist',$this->getArea());
			$this->assign('imgsize',$this->imgsize);//图片尺寸
			$this->display();
		}
	}
	public function form(){
		$Links=D($this->tab);
		if($this->isPost()){
			$map['id']=array('in',$this->_param('check'));
			if($this->_param('Delete')){
				//删除附件
				$art_arr=$Links->where($map)->field('logo')->select();	
				foreach ($art_arr as $artk => $artv) {
					if($artv['logo']){//单图
						del_attach($artv['logo']);//将attachments中的数据删除
						$this->delFile2($artv['logo']);
					}	
				}		
				$aa=$Links->where($map)->delete();
				if($aa){
					$this->success('删除成功！',U('index',array('p'=>$this->_param('p'))));
				}else{
					$this->error('删除失败！');
				}
			}
		
		}
	
	}
	public function Shenhe(){
		$Links=D($this->tab);
		$is=$Links->where('id='.$this->_param('id'))->getField('isshow');
		if($is==1){
			$isnew=0;
		}else{
			$isnew=1;
		}
		$aa=$Links->where('id='.$this->_param('id'))->setField('isshow',$isnew);
		if($aa){
			$msg['success']=true;
			$msg['msg']='修改成功';
			$msg['val']=$isnew;
		}else{
			$msg['success']=false;
		}
		echo json_encode($msg);
	}
	public function paixu() {
		$link=M($this->tab);
		//$data['sort']=I('sort');
		$ress=$link->where('id='.I('id'))->setField('sort',I('sort',0,'intval'));
		if($ress){
			echo 'yes';
		}else{
			echo 'no';
		}
	}
	/**
 * 获取图片 多图片尺寸
 */
	public function getchicun()
	{
		$data = $this->imgsize?$this->imgsize:'未定义';
		echo json_encode($data);
	}
}
?>