<?php
//APP用户登录注册 注：APP 前缀URL在http://www.soogee.com/appUrl.php中定义（echo json_encode('http://wenhui.case.shengxingshidai.com/');）
class PublicAction extends AppAction{
	protected $tab = 'Servuser';
	protected $type = '';
	public function _initialize(){
		parent::_initialize();		
	}
     /**
     * 获取验证码
     * @param  ar_id 地区id
     * @param  mobile 手机号
     */
    public function getVerify() {
        $m = M($this->tab);
        $mobile = I('mobile');
        $ar_id = I('ar_id');
        if(!$ar_id){
        	$this->setSuccess(false);
            $this->setMsg('请选择所在地区,再进行注册登录');
            $this->show($this->type);
        }
        // 手机号验证
        if ( ! $mobile || ! isTel($mobile)) {
            $this->setSuccess(false);
            $this->setMsg('用户电话号码为空或电话号码格式不对');
            $this->show($this->type);
        }
        /*$v_data=$this->send_verify($mobile);//获取验证码
        $data['verify'] = $v_data;*/
        $data['verify'] = '123456';
        $data['verify_exists'] = time() + C('VERIFY_LIFE_TIME')*60;   //验证码有效时间
        $uid=$m->where('mobile='.$mobile)->getField('id');
        $info = '';
        if($uid)
        {
            $info = $m->where('id='.$uid)->save($data);
        }else{ //第一次登录
        	$data['mobile'] = $mobile;
        	$data['area'] = $ar_id;
        	$data['addtime'] = time();
        	$info = $m->data($data)->add();
        }
        if($info){
        	$this->setSuccess(true);
            $this->setMsg('返回登录验证码');
            $this->setInfo(md5($data['verify']));
            $this->show($this->type);
        }else{
        	$this->setSuccess(false);
            $this->setMsg('数据信息有误');
            $this->show($this->type);
        }
    }
    /**
     * 用户登录/注册
     **/
    public function login()
    {
        /**
         * 登录注册过程
         * 首先判断用户电话号码是否注册，若没有注册，就录入数据库，并调用接口发送短信验证码,
         * 若以注册就不录入数据库，直接发送短信验证码，记录短信验证码
         */
        $m = M($this->tab);
        $mobile = I('mobile');
        $verify = I('verify');        
        //手机号验证
        if ( ! $mobile || ! isTel($mobile)) {
            $this->setSuccess(false);
            $this->setMsg('用户电话号码为空或电话号码格式不对');
            $this->show($this->type);
        }
        // 验证码验证
        if ( ! $verify) {
            $this->setSuccess(false);
            $this->setMsg('验证码不能为空');
            $this->show($this->type);
        }
        // 登陆
        $map['mobile'] = $mobile;
		$map['verify'] = $verify;
        $info = $m->where($map)->field('id,verify,verify_exists,isshow')->find();
        if(empty($info)){
            $this->setSuccess(false);
            $this->setMsg('手机号或验证码错误');
            $this->show($this->type);
        }
        if($info['isshow']=='0') {
            $this->setSuccess(false);
            $this->setMsg('帐号已禁用');
            $this->show($this->type);
        }
        //判断验证码是否超时
        if($info['verify_exists'] < time())
        {
            $this->setSuccess(false);
            $this->setMsg('验证码已超时');
            $this->show($this->type);
        }
        $n_data['verify'] = '';
        $n_data['verify_exists'] = 0;
        $n_data['updatetime'] = time();
        $m->where('`mobile`='.$mobile)->save($n_data); //验证跟有效期清空

        $this->setSuccess(true);
        $this->setMsg('登录成功');
        $this->setInfo($info['id']);
        $this->show($this->type);
    }	
}