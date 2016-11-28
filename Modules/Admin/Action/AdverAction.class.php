<?php
class AdverAction extends CommonAction {	
	public $tab='Adver';
	public $tabcat='Adver_category';
	public $tabView='AdverView';
	protected $imgsize = '750*320';
	public function __construct() {
		parent::__construct();
		R('Actlog/recorde');	//日志
	}
	public function index()
	{
		
		$Adver=D($this->tabView);
		$Advercat=M($this->tabcat);
		$cat=$Advercat->select();
		//dump($cat);
		import('ORG.Util.Pages2');
		$cid=I('cid');
		$cid=$cid>0?$cid:0;
		if($cid){
			$where['cid']=array('eq',$cid);
		}
		if(I('contentt')=='0')
		{
			$where['name']=array('like','%'.I('title').'%');
		}
		if(I('contentt')!='')
		{
			$this->assign('contentt',I('contentt'));
		}
		if(cookie('AREA')){//地区
			$where['area'] = array('eq',cookie('AREA'));
		}
		$count = $Adver->where($where)->count();
		if(I('stp') == 'all'){	//显示全部
			$p_num = $count;
		}else{
			$p_num = 20;
		}
		$Page = new Pages2($count,$p_num);
		if(intval(I('post.page'))) $Page->nowPage=abs(intval(I('post.page')))>$Page->totalPages?$Page->totalPages:abs(intval(I('post.page')));
		$orderby['sort']='desc';
		$orderby['id']='desc';
		$list = $Adver->order($orderby)->where($where)->page($Page->nowPage.','.$Page->listRows)->select();
		//echo $Adver->getLastSql();
		$PageConfig=array('prev'=>'<i class="fa fa-chevron-left"></i>','next'=>'<i class="fa fa-chevron-right"></i>','theme'=>'%prePage% %linkPage% %nextPage%');
		$Page->config=$PageConfig;
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('totalRows',$Page->totalRows);
		$this->assign('totalPages',$Page->totalPages);
		$this->assign('nowPage',$Page->nowPage);
		$this->assign('list',$list);
		$this->assign('search',I('title'));
		$this->assign('authlist',$this->getPower());
		$this->assign('area',$this->getData('Area',$field='ar_id,ar_name',$order=array('ar_ename'=>'asc')));
		$this->display();
	}
	
	public function form()
	{	
		$Adver =M($this->tab);
		if($this->isPost()){
			$map['id']=array('in',$this->_param('check'));
			if(I('isshow')){
				if($this->isshow(I('check'))){
					$this->success('审核成功！',U('Index',array('p'=>I('p'))));
				}else{
					$this->error('审核失败！');
				}
			}elseif(I('sortn')){
				if($this->sorts(I('sort'))){
					$this->success('排序成功！',U('Index',array('p'=>I('p'))));
				}else{
					$this->error('部分排序失败！');
				}
			}elseif(I('Delete')){
				//删除附件
				$art_arr=$Adver->where($map)->field('logo')->select();	
				foreach ($art_arr as $artk => $artv) {
					if($artv['logo']){//单图
						del_attach($artv['logo']);//将attachments中的数据删除
						$this->delFile2($artv['logo']);
					}	
				}		
				if($this->delete(I('check'))){
					$this->success('删除成功！',U('Index',array('p'=>I('p'))));
				}else{
					$this->error('删除失败！');
				}
			}else{
			
			}
		}else{
		
		}
	}
	//异步获取链接内容
	public function linkcon(){
		$type = I('post.alt');
		$conid = I('post.conid');
		$typename = I('post.typename');
		$where['isshow'] = array('eq',1);
		$where['area'] = array('eq',I('post.area'));
		if($type==2){//商户列表
			$m = M('Merchant');
			$field = 'id,name';
			$order = 'sort desc';
		}elseif($type==3){//活动列表
			$m = M('Active');
			$field = 'id,name';
			$order = 'addtime desc';
		}else{ //资讯列表
			$m = M('Article');
			$field = 'id,title';
			$order = 'sort desc';
		}
		import('ORG.Util.Pages2');
		$count = $m->where($where)->count();
		$p = I('post.page');
		$p_num = 5;//每页显示条数
		$Page = new Pages2($count,$p_num);
		if(intval(I('post.page'))) $Page->nowPage=abs(intval($p))>$Page->totalPages?$Page->totalPages:abs(intval($p));
		$arr = $m->order($order)->where($where)->page($Page->nowPage.','.$Page->listRows)->select();
		if(I('post.jurl')){
			$str='<div  class="radio"><input name="zzz" type="radio" checked disabled><font color="#999999">'.I('post.jurl').'</font></div>';
		}else{
			$str='';
		}
		foreach($arr as $k=>$v){
			if($type==2){//商户列表
				$str.='<div  class="radio"><input name="curl" type="radio"';
				/*if($v['id']==$conid && $typename=='Merchant'){
					$str.='checked';
				}*/
				$str.=' value="'.U('Merchant/con',array("id"=>$v['id'])).'">'.$v['name'].'</div>';
			}elseif($type==3){//活动列表
				$str.='<div  class="radio"><input name="curl" type="radio"';
				/*if($v['id']==$conid && $typename=='Active'){
					$str.='checked';
				}*/
				$str.=' value="'.U('Active/con',array("id"=>$v['id'])).'" >'.$v['name'].'</div>';
			}elseif($type==4){ //资讯列表
				$str.='<div  class="radio"><input name="curl" type="radio"';
				/*if($v['id']==$conid && $typename=='Article'){
					$str.='checked';
				}*/
				$str.=' value="'.U('Article/con',array("id"=>$v['id'])).'" >'.$v['title'].'</div>';
			}
		}
		$list['arr'] = $str;	//数据列表
		$list['count'] = ceil($count/$p_num);	//总页数
		echo json_encode($list);	
	}
	/*public function linkcon(){
		$type = I('alt');
		$where['isshow'] = array('eq',1);
		if(cookie('AREA')){//地区
			$where['area'] = array('eq',cookie('AREA'));
		}
		if($type==2){//商户列表
			$arr = M('Merchant')->where($where)->field('id,name')->order('sort desc')->select();
		}elseif($type==3){//活动列表
			$arr = M('Active')->where($where)->field('id,name')->order('addtime desc')->select();
		}else{ //资讯列表
			$arr = M('Article')->where($where)->field('id,title')->order('sort desc')->select();
		}
		echo json_encode($arr);	
	}*/
/**
 * 修改文章状态 单个值修改
**/
	public function editattr()
	{
		$table=$this->_post('table');
		$m=M($table);
		$where['id']=array('eq',$this->_post('id'));
		$field=$this->_post('field');
		
		$data[$field]=$this->_post('value');
		$count=$m->where($where)->data($data)->save();
		
		if($count)
		{
			$msg['success']=true;
			$msg['msg']='修改成功';
			$msg['val']=$this->_post('value');
		}
		else
		{
			$msg['success']=false;
		}
		echo json_encode($msg);
	}
	public function isshow($arrid=0){
	
		$this->IsAuth(MODULE_NAME.'/isshow');
		
		$Adver=D($this->tab);
		if(I('id')){
			$map['id']=array('eq',I('id'));
			$ba=$Adver->where($map)->find();
			if($ba['isshow']==1){
				$mdata['isshow']=0;
			}else{
				$mdata['isshow']=1;
			}
			$mdata['updatetime']=time();
			$aa=$Adver->where($map)->save($mdata);
			if($aa){
				$this->success('修改成功！',U('index'),array('p'=>I('p')));
			}else{
				$this->error('修改失败！');
			}
		}else{
			$map['id']=array('in',$arrid);
			$mdata['isshow']=1;
			$mdata['updatetime']=time();
			$aa=$Adver->where($map)->save($mdata);
			return $aa;
		}
		
		
		
	}
	public function sorts($data){
		
		$this->IsAuth(MODULE_NAME.'/sorts');
		
		$Adver=D($this->tab);
		foreach($data as $k=>$v){
			$map['id']=array('eq',$k);
			$mdata['sort']=$v;
			$mdata['updatetime']=time();
			$aa=$Adver->where($map)->save($mdata);
			if(!$aa){
				return false;
			}
		}
		return true;
	}
	public function delete($allid=0){
		
		$this->IsAuth(MODULE_NAME.'/delete');
		
		$Adver=D($this->tab);
		
		if($this->isGet()){
			$map['id']=array('in',I('id'));
			//删除附件
			$art=$Adver->where($map)->field('logo')->find();	
			if($art['logo']){//单图
				$this->delFile2($art['logo']);
			}
			$aa=$Adver->where($map)->delete();
			if($aa){
				$this->success('删除成功！',U('index',array('p'=>I('p'))));
			}else{
				$this->error('删除失败！');
			}
		}else{
			$map['id']=array('in',$allid);
			$aa=$Adver->where($map)->delete();
			return $aa;
		}
	}
	
	
	public function add()
	{	
		$Adver=D($this->tab);
		$Advercat=M($this->tabcat);
		if(I('cid')){
			$map['cid']=array('cid',I('cid'));
		}
		$cat=$Advercat->select();
		if($this->isPost()){
			if($_POST['curl']){
				$_POST['url']=$_POST['curl'];
			}
			if($Adver->create(I('post.'))){
				$aa=$Adver->add();
				if($aa){
					$this->success('添加成功！',U('index',array('p'=>I('p'))));
				}else{
					$this->error('添加失败！');
				}
			}else{
				$this->error($Adver->getError());
			}
			
		}else{
			$maxsort=$Adver->where($map)->max('sort');			
			$this->assign('arealist',$this->getArea());
			$this->assign('cat',$cat);
			$this->assign('maxsort',$maxsort+1);
			$this->assign('imgsize',$this->imgsize);//图片尺寸
			$this->display();
		}
	}
	
	public function edit(){
		$Adver=D($this->tab);
		$Advercat=M($this->tabcat);
		$cat=$Advercat->select();
		if(I('cid')){
			$map['cid']=array('cid',I('cid'));
		}
		if($this->isPost()){

			if($_POST['url']!=''){
				unset($_POST['curl']);
			}
			if($_POST['curl']){
				$_POST['url']=$_POST['curl'];
			}
			if($Adver->create(I('post.'))){
				if(!$Adver->url){
					unset($Adver->url);
				}
				$aa=$Adver->where('id='.I('id'))->save();
				if($aa){
					$this->success('修改成功！',U('index',array('p'=>I('p'))));
				}else{
					$this->error('修改失败！');
				}
			}else{
				$this->error($Adver->getError());
			}
		}else{
			$data=$Adver->where('id='.I('id'))->find();
			$url=explode('/',$data['url']);
			if($url[3]=='Merchant'){
				$m = M('Merchant');
				$field = 'id,name';
				$order = 'sort desc';
				$checked='checked';
				$this->assign('checked',$checked);
				
			}elseif($url[3]=='Active'){
				$m = M('Active');
				$field = 'id,name';
				$order = 'addtime desc';
				$checkeda='checked';
				$this->assign('checkeda',$checkeda);
			}elseif($url[3]=='Article'){
				$m = M('Article');
				$field = 'id,title';
				$order = 'sort desc';
				$checkedb='checked';
				$this->assign('checkedb',$checkedb);
			}
			if($m){
				$conid=explode('.', $url[6]);
				$name = $m->field($field)->where('id='.$conid[0])->find();
			}else{
				$name = '';
			}
			$this->assign('linkname',$name);
			$this->assign('cat',$cat);

			$this->assign('conid',$conid[0]);
			$this->assign('typeurl',$url[3]);
			$this->assign('data',$data);
			$this->assign('arealist',$this->getArea());
			$this->assign('imgsize',$this->imgsize);//图片尺寸
			//dump($data);
			$this->display();
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