<?php
class AppAction extends BaseAction{
	protected $data = array();
	protected $url_pre = '';

	public function __construct() {
		parent::__construct();
		$this->url_pre='http://'.$_SERVER['SERVER_NAME'];
	}
	/**
	 * 接口返回$data数据
	 * @param boolean $type false:返回json数据
	*/
	public function show($type=''){
		if (empty($type)) {
			exit(json_encode($this->data));
		} else {
			dump($this->data);
			exit;
		}
	}
	/**
	 * 处理结果 boolean
	 * @param boolean $flag
	*/
	protected function setSuccess($flag){
		$this->data['success'] = $flag;
	}
	/**
	 * 提示信息
	 * @param string $msg
	*/
	protected function setMsg($msg){
		$this->data['msg'] = $msg;
	}
	/**
	 * 返回前端的数据
	 * @param $info
	*/
	protected function setInfo($info){
		$this->data['info'] = $info;
	}
	/**
	 * 给手机端发送验证码
	 * @param $mobile 手机号
	*/
	protected function send_verify($mobile)
	{
		$verify=getVerifyCode();	//生成随机验证码
		$uarr=$this->getUcpaas();	//创建Ucpaas对象，并返回配置参数
		$yzx=$uarr['yzx'];
		$ucpass = $uarr['ucpaas'];//初始化 $options必填
		$appId = $yzx['appId'];
		$to = $mobile;
		$templateId = "12265";
		$param=$verify.','.C('VERIFY_LIFE_TIME');
		$result=$ucpass->templateSMS($appId,$to,$templateId,$param);	//短信发送结果
        $result=json_decode($result,true);
		if($result['resp']['respCode'] != '000000')
		{
			$this->setSuccess(false);
//			$this->setMsg('messge error');
			$this->setMsg($result['resp']['respCode']);
			$this->show();
		}
		return $verify;
	}
	/**
	 * [s_qrdd 服务端 确认服务人员]
	 * @param  [type] $templateId [模板id]
	 * @param  [type] $mobile     [服务端人员手机号]
	 * @param  [type] $param      [短信模板参数，使用','分开]
	 * @return [type]             [description]
	 */
	protected function sendMessage($templateId,$mobile,$param){
		$uarr=$this->getUcpaas();	//创建Ucpaas对象，并返回配置参数
		$yzx=$uarr['yzx'];
		$ucpass = $uarr['ucpaas'];//初始化 $options必填
		$appId = $yzx['appId'];
		$to = $mobile;
		// $templateId = "14260";	//模板id
		// $param=$verify.','.C('VERIFY_LIFE_TIME');	//模板所需参数用','分开
		$result=$ucpass->templateSMS($appId,$to,$templateId,$param);	//短信发送结果
        $result=json_decode($result,true);
        return $result;
		// if($result['resp']['respCode'] != '000000')
		// {
		// 	$this->setSuccess(false);
		// 	//$this->setMsg('messge error');
		// 	$this->setMsg($result['resp']['respCode']);
		// 	$this->show();
		// }
	}
	/**
	 * 创建Ucpaas对象
	 * @return [array] [yzx短信配置参数，ucpaas对象]
	 */
	protected function getUcpaas(){
		import('ORG.Util.Ucpaas');	//短信接口
		$yzx=C('YZX');	//接口配置文件
		$options['accountsid']=$yzx['accountsid'];
		$options['token']=$yzx['token'];
		$ucpaas = new Ucpaas($options);//初始化 $options必填
		$arr['yzx']=$yzx;
		$arr['ucpaas']=$ucpaas;
		return $arr;
	}
	/**
	 * 获取优惠券类型
	 * @param $index1优惠券的触发条件: 1=>'注册',2=>'订单分享', 3=>'邀请好友', 4=>'完成订单'
	 * @param $index2优惠券的使用范围: '通用','专业护理', '生活照料小时制', '生活照料全天制', '健康关护'
	 * @param $index3 订单类型
	 */
	protected function getChitType($index1, $index2 = '', $index3 = '')
	{
	    if($index2 !== '')
	    {
	        $ct = C('CHIT_CATE');	//优惠券类别
	        $type= $ct[$index2];
	        $where['cate']=array('eq',$type);
	    }
	    if($index1==4 && $index3)	//完成订单 根据订单类型不同 发送的优惠券不同
	    {
	        $s_type = C('SER_TYPE');	//订单类型
	        $type= $s_type[$index3];
	        $where['source']=array('eq',$type);
	    }
	    /* $SQL = 'SELECT * FROM `wa_chit` where `cate`='."'".$type."'".' AND `trigger`='.$index2.' ORDER BY `id` DESC';
	     $r = M('Reward')->query($SQL); */
	    $where['trigger']=array('eq',$index1);
	    $where['status']=array('eq',1);	//优惠券状态 1正常，2禁止
	    $order['id']='desc';
	    $r = M('Chit')->where($where)->order($order)->SELECT();
	    return $r;
	}

	/**
	 *  获取优惠券添加到客户个人优惠券（userchit表）
	 *  @param $u_id 用户的id
	 *  @param $id 优惠券的id
	 */
	protected function getChit($u_id, $id) {
	    //判断优惠券种类是否存在
	    $m = M('Chit');
	    $map['id'] = $id;
	    $info = $m->where($map)->find();
	    if ( ! $info) {
	        $this->setSuccess(false);
	        $this->setMsg('该优惠不存在');
	        $this->show($this->type);
	    }

	    //判断优惠券的状态
	    if ($info['status'] == 2) {
	        /*$this->setSuccess(false);
	        $this->setMsg('该优惠券已关闭');
	        $this->show($this->type);*/
	        return;
	    }
	    
	    //判断优惠券是否有限制，是否达到了优惠券的总金额数限制
	    if($info['total_price']!='0.00')
	    {
	    	$l_price=$info['l_price']+$info['price'];
		    if ($l_price >= $info['total_price']) {
		        /*$this->setSuccess(false);
		        $this->setMsg('该优惠券总金额不足');
		        $this->show($this->type);*/
		        return;
		    }
	    }
	    

	    //录入用户的优惠券表
	    $data['addtime'] = time();
	    $data['u_id'] = $u_id;
	    $data['c_id'] = $id;
	    $data['price'] = $info['price'];
	    $data['c_days'] = $data['addtime'] + $info['days'] * 24 * 3600;
	    $data['c_type'] = $info['cate'];
	    $data['c_trigger']=$info['trigger'];
	    $data['is_use'] = 0;
	    /*if (! M('UserChit')->add($data)) {
	        $this->setSuccess(false);
	        $this->setMsg('优惠券获取失败');
	        $this->show($this->type);
	    }*/
	    if(M('UserChit')->add($data)){
	        //刷新优惠券的已使用发放金额和数量
	        $d_chit['l_num'] = $info['l_num'] + 1;
	        $d_chit['l_price'] = $info['l_price'] + $info['price'];
	        $d_chit['u_price']=$info['l_price'] + $info['price'];
	        M('Chit')->where('`id`='.$info['id'])->save($d_chit);
	    }
	}
	/**
	 * [getPersonInfo 获取用户个人信息]
	 * @param  [type] $table [表格]
	 * @param  [type] $where [查询条件]
	 * @param  string $field [字段]
	 * @return [type]        [description]
	 */
	protected function getPersonInfo($table,$where,$field=''){
		$m=D($table);
		$list=$m->field($field)->where($where)->find();
		return $list;
	}
	/**
	 * [checkTuijian 判断手机号是否已接受过推荐]
	 * @param  [type] $mobile [受推荐人手机号]
	 * @return [bool]
	 */
	protected function ppCheckTuijian($mobile){
		$r_kehu=M('Invitation')->field('r_id')->where(array('r_mobile'=>array('eq',$mobile)))->find();
		$r_fuwu=M('Recommend')->field('r_id')->where(array('r_mobile'=>array('eq',$mobile)))->find();
		if($r_kehu || $r_fuwu) //手机号已接受客户端或者服务端的推荐
			return false;
		else
			return true;
	}
	/**
	* 接口测试方法
	*/
	public function post_test($url, $data) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url_pre.$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$info = curl_exec($ch);
		curl_close($ch);
		exit($info);
	}

	public function get_test($url, $data=''){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url_pre.$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$info = curl_exec($ch);
		curl_close($ch);
		exit($info);
	}
	
}