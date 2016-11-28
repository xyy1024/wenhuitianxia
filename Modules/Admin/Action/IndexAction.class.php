<?php
class IndexAction extends CommonAction {
	public $tab_sys='';
	
	public function __construct() {
		parent::__construct();
		$this->tab_sys='System';
		R('Actlog/recorde');	//日志
	}
	public function index(){
		$f=M('Article');
		$map['isshow']=array('eq',0);
		$row=$f->where($map)->select();
		$rew=count($row);
		$this->assign('row',$row);
		$this->assign('rew',$rew);
		// $this->menu(cookie('ADMIN_CATES'));
		$this->assign('authlist',$this->getPower());
		$this->assign('arealist',$this->getArea());
		$this->display();
	}
	/**
	 * ajax存储cookie
	 * zjp
	 */
	public function changarea(){
		cookie('AREA',I('get.area'));
        cookie('AREA_NAME',I('get.area_name'));
        echo cookie('AREA_NAME');
	}
	
	public function main(){
		//读取最后一次访问栏目
		$lastcate=$this->lastCate();
		$cateinfo=M('Category')->field('id,catname')->where(array('id'=>array('eq',$lastcate)))->find();
		$authlist = $this->getPower();
		//文章审核权利
		if(in_array('article-shenhe',$authlist) || cookie('ADMIN_KEY') == 1){
			$shenhe = 3;	//管理员有审核权限
		}else{
			$shenhe = false;
			$rel_num=M('Article')->where(array('aid'=>array('eq',cookie('ADMIN_KEY'))))->count();
			$this->assign('rel_num',$rel_num);
		}
		$this->assign('shenhe',$shenhe);

		$m=M('Article');
		$where['isshow']=array('eq',0);
		$row=$m->count();
		$Articlewei=$m->where($where)->select();
		$rowwei=count($Articlewei);
		$this->assign('Articlewei',$Articlewei);
		$this->assign('rowwei',$rowwei);
		$this->assign('row',$row);
		$this->assign('cateinfo',$cateinfo);
		//系统运行时间
		$this->assign('system_start',floor((time()-C('SYSTEM_START')) / 86400));

		$this->display('main');
	}
	private function lastCate(){
		$configpath = C('CFG_CONf');
		$filename=$configpath."lastvist.cate.php";
		$f=fopen($filename, 'r');
		$cont=fread($f,filesize($filename));
		fclose($f);
		$arr=json_decode($cont,true);
		if(array_key_exists(cookie('ADMIN_KEY'), $arr)){
			return $arr[cookie('ADMIN_KEY')];
		}else{
			if(cookie('ADMIN_KEY') == 1){	//如果是超级管理员 返回分类的一个分类
				$cates=M('Category')->where(array('upid'=>array('eq',0)))->order('sort')->getField('id');
				return $cates;
			}else{
				$cates=explode(',', cookie('ADMIN_CATES'));
				return $cates[0];
			}
		}
	}
	

/**
 * 左侧导航
 * pp new
 * @param string $map 管理员可管理的分类
**/
	protected function menu($map=''){
		$addtype=array(1,2,3,0);	//0:外部链接 1:内容列表 2:内容单页 3:栏目主页
		$Category=M('Category');
		// $order['path']='asc';
		$order['sort']='asc';
		$order['id']='asc';
		// $field='id,upid,topid,path,catname,catename,thumb,banner,title,keywords,description,content,type,sort,picnum,linkurl,isshow,module_name,action_name,addtime,updatetime';
		$field='id,upid,topid,path,catname,type,sort,isshow,module_name,action_name';
		if(!$map && cookie('ADMIN_KEY') != 1){
			$catlist = null;
			$list = null;
		}else{
			if(cookie('ADMIN_KEY') != 1){
				$where['id']=array('in',$map);	
			}
			$where['upid']=array('eq',0);
			$catlist=$Category->field($field)->where($where)->order($order)->select();
			// echo $Category->getLastSql();
			if($catlist && is_array($catlist)){
				foreach($catlist as $k=>$v)
				{
					if(in_array($v['type'],$addtype))	//1:内容列表 2:内容单页 3:栏目主页
						$add=true;
					else
						$add=false;
					$list[$k]=$catlist[$k];
					$list[$k]['cmenulist']=$this->allChildArray($v['id'],$add,$addtype,$map);
				}
			}
		}

		$this->assign('catlist',$catlist);
		$this->assign('list',$list);
		// $this->display();	
	}
	public function system()
	{
		$System=D($this->tab_sys);
		if($this->isPost()){
			$_POST['cfg']=array2single($_POST['cfg'],true);
			foreach($_POST['cfg'] as $k=>$v){
				$arr_id=explode('_',$k);
				
				$lastid=$System->where('id='.$arr_id['1'])->setField('content',$v);
				if(I('sort_'.$arr_id['1'])){
					$lastid=$System->where('id='.$arr_id['1'])->setField('sort',I('sort_'.$arr_id['1']));
				}
	
			}
			$this->ReWriteConfig();
			$this->success('基本信息更改成功',U('Index/system'));
		}else{
			$list=$System->where('isshow=1')->order('sort asc,id asc')->select();
			$this->assign('lists',$list);
			$this->display();	
		}

	}	
	
	public function delcache(){
		header("Content-type: text/html; charset=utf-8");
		//清文件缓存
		$dirs = array('./Runtime/');
		@mkdir('Runtime',0777,true);
		//清理缓存
		foreach($dirs as $value) {
			$this->rmdirr($value);
		}
		echo '<script>alert("系统缓存清除成功");window.location="'.U('Index/main').'";</script>';
		// $this->success('系统缓存清除成功！',U('Index/main'));  
	} 
	public function rmdirr($dirname) {
		if (!file_exists($dirname)){
			return false;
		}
		if (is_file($dirname) || is_link($dirname)){
			return unlink($dirname);
		}
		$dir = dir($dirname);
		if($dir){
			while (false !== $entry = $dir->read()){
				if ($entry == '.' || $entry == '..') {
					continue;
				}
				//递归
				$this->rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
			}
		}
		$dir->close();
		if(basename($dirname) != 'Runtime'){
			$r = rmdir($dirname);
		}else{
			$r = true;
		}
		return $r;
	}
	//更新配置文件webconfig文件的内容
	private function ReWriteConfig(){
		$Config=D($this->tab_sys);
		$configpath = C('CFG_CONf');
		if(!is_writeable($configpath.'config.inc.php')){
			$this->error("配置文件'{$configpath}config.inc.php'不支持写入，无法修改系统配置参数！",'__ACTION__');
		}
		$datalists=$Config->order('id asc')->select();
		foreach($datalists as $datalist){
			$data[$datalist['cfg']]=$datalist['content'];
		}
		F('config.inc',$data,$configpath);
	}
	
	public function do_config()
	{
		if($this->isPost()){
			$Config=D('System');
			if($Config->create())
			{
				$lastid=$Config->add();
				if($lastid)
				{
					$this->ReWriteConfig();
					$this->success('变量添加成功',U('Index/system'));
				}
				else
					$this->error('变量添加失败');
			}
			else
			{
				$this->error($Config->getError());
			}
		}else{
			$this->display();
		}
	}
	public function edit(){
	
		
		$Admin=D("Admin");
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
					$this->success('系统用户修改成功',U('Index/main'));
				}else{
					$this->error('系统用户修改失败');
				}
			}
		}else{
			
			$this->assign('data',$data);
			$this->display(); 
		}
	
		
	}

/**
 * 图片裁切
 */
	public function imgCut(){
		$img = str_replace(array('$','@'), array('/','.'), I('img'));
		if((list($width, $height, $type, $attr) = getimagesize('.'.$img ) ) !== false ) {
			$this->assign('src_data',$width.'*'.$height);	//原图尺寸				
		}
		$this->assign('img',$img);
		if($img_w = I('img_w',0,'intval') && $img_h = I('img_h',0,'intval')){
			$this->assign('img_w',I('img_w'));
			$this->assign('img_h',I('img_h'));
		}
		$this->assign('browser',getBrowser());
		$this->display();
	}
	public function doCut(){
		// $_POST['avatar_src'] = '/Uploads/image/20160804/20160804143159_70545.png';
		// $_POST['avatar_data'] = '{"x":210,"y":37,"height":294,"width":147,"rotate":0,"scalex":1,"scaley":1}';
		import('ORG.Util.crop');
		$crop = new CropAvatar(
			isset($_POST['avatar_src']) ? '.'.$_POST['avatar_src'] : null,
			isset($_POST['avatar_data']) ? $_POST['avatar_data'] : null
		);
		$response = array(
			'state' => 200,
			'msg' => $crop -> getMsg(),
			'result' => ltrim($crop -> getResult(),'.')
		);
		// var_dump($response);
		// exit;
		echo json_encode($response);
	}
	/**
	 * 退出登录
	 */
	public function outlogin(){
		$this->clearCookie();
		$this->success('退出成功!',U('Public/index'));
	}
}