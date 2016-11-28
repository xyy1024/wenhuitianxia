<?php
class LiuyanAction extends CommonAction {
	public function __construct(){
		parent::__construct();
		R('Actlog/recorde');	//日志
	}	
    public function index(){
		$Links=M('Liuyan');
		import('ORG.Util.Pages');
		$where='';
		$count = $Links->where($where)->count();
		//echo $Links->getLastSql();
		$Page = new Pages($count,20);
		$nowPage  = isset($_GET['p'])?$_GET['p']:0;
		$orderby['id']='desc';
		$list = $Links->order($orderby)->where($where)->page($nowPage.','.$Page->listRows)->select();
		$show = $Page->show();
		//dump($list);
		$this->assign('page',$show);
		$this->assign('list',$list);
		
		$this->display();
	}
	public function form(){
		if($this->isPost()){
			if(I('isshow')){
				if($this->isshow(I('check'))){
					$this->success('审核成功！',U('Index',array('cid'=>I('cid'),'p'=>I('p'))));
				}else{
					$this->error('审核失败！');
				}
			}elseif(I('sortn')){
				if($this->sorts(I('sort'))){
					$this->success('排序成功！',U('Index',array('cid'=>I('cid'),'p'=>I('p'))));
				}else{
					$this->error('部分排序失败！');
				}
			}elseif(I('delete')){
				if($this->delete(I('check'))){
					$this->success('删除成功！',U('Index',array('cid'=>I('cid'),'p'=>I('p'))));
				}else{
					$this->error('删除失败！');
				}
			}else{
			
			}
		}else{
		
		}
	}
	public function look(){
		$Links=M('Liuyan');
		$map['id']=array('eq',I('id'));
		$data=$Links->where($map)->find();
		$this->assign('data',$data);
		$Links->where('id='.I('id'))->setField('isshow',1);
		$this->display();
	}
	public function delete($allid=0){
		
		$Links=D('Liuyan');
		
		if($this->isGet()){
			$map['id']=array('in',I('id'));
		
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
	public function isshow(){
			
		$Links=M('Liuyan');
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
			$this->success('修改成功！',U('index'),array('cid'=>I('cid'),'p'=>I('p')));
		}else{
			$this->error('修改失败！');
		}
	
	}
}
?>