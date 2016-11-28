<?php
class CommonAction extends BaseAction {
	public $ADMIN_KEY='';
	public $ADMIN_NAME='';
	public $LOGIN_TIME='';
	public function __construct() {
		parent::__construct();
		$this->ADMIN_KEY=cookie('ADMIN_KEY');
		$this->ADMIN_NAME=cookie('ADMIN_NAME');
		$this->LOGIN_TIME=cookie('LOGIN_TIME');
		header('Content-Type:text/html; charset=utf-8');
		$this->ppFirewall();	//防火墙验证
		$this->checkAdminSession();
		//去除反斜梗
		if (get_magic_quotes_gpc())
		{
			$_POST = array_map('stripslashes_deep', $_POST);
			$_GET = array_map('stripslashes_deep', $_GET);
			$_COOKIE = array_map('stripslashes_deep', $_COOKIE);
		}
		
	}

	public function _initialize()
	{
		parent::_initialize();
		if(C('OPEN_POWER')){	//开启权限
			if(MODULE_NAME !="Index" && MODULE_NAME !=="Public" && MODULE_NAME !=="Wechat")
			{
				import('ORG.Util.Auth');//加载类库
				$auth=new Auth();
				// var_dump($auth->check(strtolower(MODULE_NAME).'-'.strtolower(ACTION_NAME),session('ADMIN_KEY')));
				// exit;
				if(!$auth->check(strtolower(MODULE_NAME).'-'.strtolower(ACTION_NAME),cookie('ADMIN_KEY'))){
					$this->error('你没有权限');
				}
			}
		}
	}

	public function checkAdminSession() {
		if (!isset($this->ADMIN_KEY) || !isset($this->ADMIN_NAME) || !isset($this->LOGIN_TIME)) {
			$this->clearCookie();
			exit('<script>alert("当前用户未登录或登录超时，请重新登录");top.location.href="'.U('Public/login').'";</script>');
		}
		if(!cookie('ADMIN_POWER') && !cookie('AREA')){
			$this->clearCookie();
			exit('<script>alert("用户信息有误，请重新登录");top.location.href="'.U('Public/login').'";</script>');
		}
	}
	protected function clearCookie(){
		cookie('ADMIN_KEY',null);
		cookie('ADMIN_NAME',null);
		cookie('LOGIN_TIME',null);
		cookie('ADMIN_CATES',null);
		cookie('ADMIN_POWER',null);
		cookie('AREA',null);
		cookie('AREA_NAME',null);
		// $this->dFirewall();
	}
	//防火墙验证
	protected function ppFirewall(){
		if(!$_COOKIE['wyQiantai_ppfirewall']){
			header('Content-Type:text/html; charset=utf-8');
			exit('您无权限操作');
		}
	}
	//清除//防火墙验证
	protected function dFirewall(){
		setcookie('wyQiantai_ppfirewall',null,time()-1000);
	}
	public function IsAuth($action) {
		import('ORG.Util.Auth');//加载类库
		$auth=new Auth();
		/*if(!$auth->check(strtolower($action),$_SESSION['ADMIN_KEY']['id'])){
			$this->error('你没有权限');
		}*/		
	}

	public function AllCatid($id,$type='1',$tab='Category',$upid='upid'){
		$table=M($tab);
		
		if($type==1){
			$return[]=$id;
		}else{
			$return =$id;
		}
		$map[$upid]=array('eq',$id);
		$lists=$table->where($map)->select();
		foreach($lists as $k=>$v){
			$listarr=$this->AllCatid($v['id'],$type,$tab,$upid);
			if($type==1){
				$return=array_merge($return,$listarr);
			}else{
				$return .=','.$listarr;
			}
		}
		return $return;
	}
	public function topId($id){
		$table=M('Category');
		$da=$table->where('id='.$id)->find();
		if(!$da){
			return false;
		}
		if($da['topid']==0){
			return $da['id'];
		}else{
			return $da['topid'];
		}
	}
/**
 * 导入excel
**/
	function impExcel()
	{
		if(isset($_FILES["import"]) && ($_FILES["import"]["error"] == 0))
		{
			$result = $this->importExecl($_FILES["import"]["tmp_name"]);
			if($result["error"] == 1)
			{          
				$execl_data = $result["data"][0]["Content"];
				foreach($execl_data as $k=>$v)
				{

				// 这里写你的业务代码

				}
			}
		}
	}
/**
 * 获取用户权限
 * 防止 authlist 走出cookie限制
 * @return array
 */
	protected function getPower(){
		//获取用户权限
		import('ORG.Util.Auth');//加载类库
		$auth=new Auth();
		$authlist=$auth->getAuthList(cookie('ADMIN_KEY'));
		return $authlist;
	}

/**
 * 导出Excel
**/	
	function expExcel()
	{
		$xlsName  = "Authrule";
		$xlsCell  = array(
			array('id','序列'),
			array('name','规则'),
			array('title','规则说明')
		);
		$xlsModel = M('Authrule');
		$xlsData  = $xlsModel->Field('id,name,title')->select();
		$this->exportExcel($xlsName,$xlsCell,$xlsData);
    }
    /**
     * 获取数据：行业，商户（id => value）
     * @param  string $tab   数据表
     * @param  string $field 字段
     * @param array|string $order 排序
     * @return array
     */
    protected function getData($tab='Industry',$field='id,name',$order=array('sort'=>'asc'),$where=array()){
    	$m = M($tab)->order($order);
    	if($where) $m = $m->where($where);
    	$list = $m->getField($field);
    	return $list;
    }
    /**
 * 删除文件
**/
	public function delFile()
	{
		$file=$this->_param('file');
		if($file)
		{
			$file2='.'.$file;
			if(file_exists($file2))
			{
				if(unlink($file2))
					echo 'yes';
				else
					echo 'no';
			}else{
				echo 'yes';
			}
		}
		else
		{
			echo 'no';
		}
	}
	public function delFile2($file)
	{
		if(!$file && I('imgUrl')){
			$file = I('imgUrl');
		}
		if($file)
		{
			$file2='.'.$file;
			if(file_exists($file2))
			{
				unlink($file2);
			}
		}
	}
	/**
	 * 获取地区列表
	 * @return array
	 */
	public function getArea($map=array()){
		$m=M('Area');
		$order = array('ar_ename'=>'asc');
		$field = array('ar_id,ar_name,ar_ename,ar_ctm,ar_od');
		$m = $m->field($field)->order($order);
		if($map) $m = $m->where($map);
		$list = $m->select();
		return $list;
	}
}