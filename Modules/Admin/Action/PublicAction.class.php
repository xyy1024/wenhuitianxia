<?php
// 本类由系统自动生成，仅供测试用途
class PublicAction extends Action {
	public $ADMIN_KEY='';
	public $ADMIN_NAME='';
	public function __construct() {
		parent::__construct();
		$this->ppFirewall();	//防火墙验证
		$this->ADMIN_KEY=cookie('ADMIN_KEY');
		$this->ADMIN_NAME=cookie('ADMIN_NAME');
	}
     public function login(){
		$ADMIN_KEY=$this->ADMIN_KEY;
		if (isset($ADMIN_KEY) && isset($this->ADMIN_NAME)) {
			$this->redirect('Index/index');
		}
		if($this->isPost()){
			if (empty($_POST['username'])) {
				$this->error("帐号不可以为空！");
			}else if (empty($_POST['password'])) {
				$this->error("密码不可以为空！");
			}else if(session('verify') != md5($_POST["verify"])) {
				$this->error('验证码错误！');
			}
			$map['username']=$_POST["username"];
			$map['password']=md5($_POST["password"]);
			$admin = M('Admin')->where($map)->find();
			if(empty($admin)){
				$this->error("帐号不存在或者密码错误！");
			}elseif($admin['isshow']==0){
				$this->error("账号已被禁用！");
			}else{
				$pp_life_time=0;
				cookie('ADMIN_KEY',$admin['id'],$pp_life_time);
				cookie('ADMIN_NAME',$admin['username'],$pp_life_time);
				cookie('LOGIN_TIME',time(),$pp_life_time);
				cookie('ADMIN_CATES',$admin['cates'],$pp_life_time);

				//获取用户权限
                import('ORG.Util.Auth');//加载类库
                $auth=new Auth();
                $authlist=$auth->getAuthList($admin['id']);
                //cookie('ADMIN_AUTH',$authlist,$pp_life_time);
                
                //获取地区名称
                if($admin['id'] != 1)   //id为1，超级管理员
                {
                    cookie('AREA',$admin['area'],$pp_life_time);
                    $area=M('Area')->where(array('ar_id'=>array('eq',$admin['area'])))->getField('ar_name');
                    cookie('AREA_NAME',$area,$pp_life_time);
                    cookie('ADMIN_POWER',0);
                }else{
                	cookie('ADMIN_POWER',1);
                }

				$Ad=M('Admin');
				$Ad->ltime = time();
				$Ad->lip = $_SERVER['REMOTE_ADDR'];
				$Ad->where('id='.$admin['id'])->save(); // 根据条件保存修改的数据
				$this->clearTop();	//置顶超时的文章 取消置顶
				R('Actlog/recorde');	//日志
				$this->success("登录成功!",U('Index/index'));
				//echo '<script>alert("当前用户未登录或登录超时，请重新登录");top.location.href="'.U('Public/login').'";</script>';
			}
		}else{
			$this->display();
		}
	}
	
	public function index(){
		$this->redirect('Public/login');
	}
	//验证码类
	public function verify() {
		//ob_clean();
		import ( "ORG.Util.Image" );                 
		Image::buildImageVerify (4);    
	}
/**
 * 置顶超时的文章 取消置顶
 * @return [type] [description]
 */
	protected function clearTop(){
		$m=M('Article');
		$data['dotop'] = 0;
		$data['dotopday'] = 0;
		$data['dotopend'] = 0;
		$where['dotop'] = array('gt',0);
		$where['dotopday'] = array('gt',0);
		$where['dotopend'] = array('lt',time());
		$m->where($where)->data($data)->save();
	}
	//防火墙验证
	protected function ppFirewall(){
		if($_COOKIE['wyQiantai_ppfirewall'] != '333'){
			header('Content-Type:text/html; charset=utf-8');
			exit('您无权限操作');
		}
	}
}