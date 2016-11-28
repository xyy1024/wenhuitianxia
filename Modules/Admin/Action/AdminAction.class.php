<?php
Class AdminAction extends CommonAction{

	private $tab = 'Admin';
	private $tabView = 'AdminView';
	public function __construct() {
		parent::__construct();
		$this->tab='Admin';
		//$this->tab='AdminView';
		// $this->rotab='Role';
		R('Actlog/recorde');	//日志
	}
	
	public function index()
	{
		$Admin=D($this->tabView);
		import('ORG.Util.Pages2');
		$map['id']=array('neq',1);
		$order['id']='desc';
		if(I('name')){//查询登录帐号
            $map['username']=array("like", I('name').'%');
            $this->assign('search',I('name'));
        }
        if(cookie('AREA')){//地区
			$map['area'] = array('eq',cookie('AREA'));
		}
		$count = $Admin->where($map)->count();
		if(I('stp') == 'all'){	//显示全部
			$ye = $count;
		}else{
			$ye = 20;
		}
		$Page = new Pages2($count,$ye);
		if(intval(I('post.page'))) $Page->nowPage=abs(intval(I('post.page')))>$Page->totalPages?$Page->totalPages:abs(intval(I('post.page')));
		// $nowPage = isset($_GET['p'])?$_GET['p']:0;
		$lists = $Admin->where($map)->page($Page->nowPage.','.$Page->listRows)->order($order)->select();
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
	public function  edit()
	{
		$Admin=D($this->tab);
		$data=$Admin->where('id='.I('id'))->find();
		if($this->isPost()){
			
			if(!I('password')){
				unset($_POST['password']);
				unset($_POST['password2']);
			}else{
				$_POST['password']=md5(I('password'));
				$_POST['password2']=md5(I('password2'));
			}
		
			//添加用户
			if(!$Admin->create()){
				$this->error($Admin->getError());
			}else{
				$lastid=$Admin->where('id='.I('id'))->save();
				if($lastid>0){
					$this->success('系统用户修改成功',U('Admin/index'));
				}else{
					$this->error('系统用户修改失败');
				}
			}
		}else{
			$this->assign('data',$data);
			$user_cates=array_filter(explode(',',$data['cates']));
			$this->assign('cates',$this->showCategory('',$user_cates));
			$this->assign('area',$this->getArea());
			$this->display(); 
		}
	}
	
	public function add()
	{
		$Admin=D($this->tab);
		if($this->isPost()){
			//添加用户
			if(!$Admin->create()){
				$this->error($Admin->getError());
			}else{
				$lastid=$Admin->add();
				if($lastid>0){
					$this->success('系统用户添加成功',U('Admin/index'));
				}else{
					$this->error('系统用户添加失败');
				}
			}
		}else{
			$this->assign('cates',$this->showCategory(''));
			$this->assign('area',$this->getArea());
			$this->display(); 
		}
	}
	
	public function delete()
	{
		$where['id']=I('id');
		$Admin=D($this->tab);
		$data=$Admin->where($where)->find();
		if($data['id']==1 || (cookie('ADMIN_KEY') != 1 && $data['id']!=cookie('ADMIN_KEY'))){
			$this->error('不允许删除当前使用的管理员帐号');
		}else{
			$count=$Admin->where($where)->delete();
			if($count){
				$this->success('管理员删除成功',U('Admin/index'));
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
/**
 * showCategory 调出文章分类
 * @param  int $upid  [description]
 * @param  array  $data  [description]
 * @param  string $field [description]
 * @return string        [description]
 */
	private function showCategory($upid,$data=array(),$field='id,catname,path,upid'){
		$str='';	//返回的html结构
		$m=M('Category');
		$where['upid']=array('eq',$upid);
		$cate=$m->where($where)->order('sort')->select();
		if(!$cate) return '';
		foreach($cate as $k => $v){
			$pre='';	//前辍
			$pre.=$this->getSuoji($v['path']);	//子分类添加缩进
			if($childnum = $this->getChildNum($m,$v['id']))	//判断添加折叠图标
				$pre .= '<span class="zd_'.$v['id'].'"><img src="'.__PUB__.'img/1.gif" prop1="1" /></span>';
			else
				$pre .= '<span>&nbsp;</span>';
			$str .= '<div class="pp_1">'.$pre.'<input type="checkbox" name="cates[]" value="'.$v['id'].'"';
			if(in_array($v['id'],$data)) $str.=' checked="checked"';
			$str .= ' />'.$v['catname'].'</div>';
			if($childnum){//判断子分类进行递归
				if($child = $this->showCategory($v['id'],$data,$field))
					$str .= '<div class="pp_2" id="zd_'.$v['id'].'">'.$child.'</div>';
			}
		}
		return $str;
		// echo '<pre>';
		// var_dump($cate);
		// echo '</pre>';
	}
/**
 * 子分类添加缩进
 * @param [string] $[path] [path路径]
 * @return [string] [缩进]
 */
	private function getSuoJi($path){
		$num=count(explode(',', $path)) - 1;
		$str='';
		if($num){
			for($i = $num; $i >0; $i--){
				$str.='　　';
			}
		}
		return $str;
	}
/**
 * [getChildNum 返回分类的子分类数量]
 * @param [string] $[path] [path路径]
 * @return [int] [子分类数量]
 */
	private function getChildNum($m,$upid){
		$map['upid']=array('eq',$upid);
		$data=$m->where($map)->count();
		if($data)
			return $data;
		else
			return 0;
	}
}