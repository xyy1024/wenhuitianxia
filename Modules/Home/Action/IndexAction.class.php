<?php
//app端接口 注：APP 前缀URL在http://www.soogee.com/appUrl.php中定义（echo json_encode('http://wenhui.case.shengxingshidai.com/');）
class IndexAction extends AppAction
{
	protected $type = '';
	public function _initialize() {
		parent::_initialize();		
	}

	/**
	 * [首页]
	 * @param  ar_id 地区id
	 */
	public function index(){
		/*$city_lat = 39.979364; //兴发大厦
		$city_lng = 116.324481;
		$ar_id = 1;*/
		$city_lat = I('city_lat');
		$ar_id = I('ar_id'); 
		$city_lng = I('city_lng');
		$m=M('Adver');
		$where['isshow'] = array('eq',1);
		if(I('ar_id')){
			$where['area'] = array('eq',$ar_id);
		}
		$info['adverlist']=$m->where($where)->field('id,name,url,logo')->order('sort desc')->select();
		$info['industry'] = $this->getIndustry();
		$info['chitlist'] = $this->Coupon('',6);//优惠券列表
		$info['merList'] = $this->getMerchant($city_lat,$city_lng,' area='.$ar_id,'id,name,address,per_money,description,img,uscore','distance asc, uscore desc',4); // 商户列表
		$this->setSuccess(true);
		$this->SetInfo($info);
        $this->show($this->type);
	}

	/**
	 * 搜索页面
	 * @param  ar_id 地区id
	 * @param  name 商户名称
	 */
	public function merSearch(){
		/*$city_lat = 39.979364; //兴发大厦
		$city_lng = 116.324481;
		$ar_id = 1;
		$name = '北京';
		$ins_id = '5';
		$uscore = '1';
		$per_money = '1';
		$distance = '1';*/
		$ar_id = I('ar_id');
		$city_lng = I('city_lng');//经度
		$city_lat = I('city_lat');//纬度
		$name = trim(I('name'));
		$ins_id = I('ins_id');//行业id
		$uscore = I('uscore');
		$per_money = I('per_money');
		$distance = I('distance');
		$where = ' area='.$ar_id;
		if($name){
			$where.=' AND name LIKE "%'.$name.'%" ';
		}
		$info  = $this->senYanzheng($name);
		if($info['suc']==false){
			$this->setSuccess(false);
	        $this->setMsg('搜索内容中包含敏感词汇['.$info['msg'].'],请确认后再搜索');
	        $this->show($this->type);
		}else{
			$order='';
			if($ins_id){
				$where.=' AND ins_id LIKE "%,'.$ins_id.',%" ';
			}if($uscore){
				$order.=$order?',uscore desc ':'uscore desc ';
			}if($per_money){
				$order.=$order?',per_money asc ':'per_money asc ';
			}if($distance){
				$order.=$order?',distance asc ':'distance asc ';
			}
			$arr = $this->getMerchant($city_lat,$city_lng,$where,'id,name,address,per_money,description,img,uscore',$order,''); // 商户列表
			if($arr){
				$arr['industry'] = $this->getIndustry();//行业信息
				$this->setSuccess(true);
				$this->SetInfo($arr);
		        $this->setMsg('商户列表');
		        $this->show($this->type);
			}else{
				$this->setSuccess(false);
		        $this->setMsg('暂无相关商户');
		        $this->show($this->type);
			}	
		}	
	}

	/**
	 * 发表评论
	 * @param ser_id  用户id
	 * @param mer_id  商户id
	 * @return true/false   msg:提示信息
	 */
	public function evaluateAdd(){
		$ser_id = I('ser_id');
		$mer_id = I('mer_id');
		$city_lng = I('city_lng');//经度
		$city_lat = I('city_lat');//纬度
		if($this->isPost()){
            $m = M('Evaluate');
            if (empty($_POST['score'])) {
                $this->setSuccess(false);
                $this->setMsg('评论分数不能为空');
                $this->show($this->type);
            }
            $sen_info  = $this->senYanzheng(trim(I('content')));
			if($sen_info['suc']==false){
				$this->setSuccess(false);
		        $this->setMsg('评论内容中存在敏感词汇['.$sen_info['msg'].'],请确认后再提交');
		        $this->show($this->type);
			}
            if($_POST['img'] && is_array($_POST['img'])){
            	if(count($_POST['img'])>3){
            		$this->setSuccess(false);
			        $this->setMsg('评论最多上传三张照片');
			        $this->show($this->type);
            	}else{
            		$_POST['img'] = $img_arr;
	            	$_POST['img'] = '';
	            	foreach($img_arr as $k=>$v){
	            		if(strpos($v,"Uploads") === false){
							if($v)//评论照片
							{
								$arr=R('Upimage/upImg',array($v,array(34,66)));//将接受的图片转换并写入后台
								$_POST['img']=$_POST['img']?','.$arr['img']:$arr['img'];//将接受的图片转换并写入后台
							}
						}
	            	}
            	}
            }
            $_POST['addtime'] = time();
            if($m->create()){
            	$arr = $m->add();
            	if($arr){
            		$this->saveScore($mer_id);//更改商户的综合评分
            		$this->setSuccess(true);
			        $this->setMsg('评论成功');
			        $this->show($this->type);
            	}else{
            		$this->setSuccess(false);
			        $this->setMsg('评论失败');
			        $this->show($this->type);
            	}
            }
		}else{
			$info = $this->getMerchant($city_lat,$city_lng,'id='.$mer_id,'id,name,address,per_money,img,uscore','','1'); // 商户列表
			if($info){
				$this->setSuccess(true);
				$this->SetInfo($info);
		        $this->setMsg('商户评价');
		        $this->show($this->type);
			}else{
				$this->setSuccess(false);
		        $this->setMsg('该商户不存在');
		        $this->show($this->type);
			}
		}
	}
	/**
	 * 修改评论
	 * @param eva_id  评论id
	 * @return true/false   msg:提示信息
	 */
	public function evaluateEdit(){
		$eva_id = I('eva_id');
		$city_lng = I('city_lng');//经度
		$city_lat = I('city_lat');//纬度
		$where['id'] = array('eq',$eva_id);
		$info = M('Evaluate')->field('id,mer_id,score,img,content')->where($where)->find();
		if(!$info){
			$this->setSuccess(false);
	        $this->setMsg('该评论信息不存在');
	        $this->show($this->type);
		}else{
			if($this->isPost()){
	            $m = M('Evaluate');
	            if (empty($_POST['score'])) {
	                $this->setSuccess(false);
	                $this->setMsg('评论分数不能为空');
	                $this->show($this->type);
	            }
	            $sen_info  = $this->senYanzheng(trim(I('content')));
				if($sen_info['suc']==false){
					$this->setSuccess(false);
			        $this->setMsg('评论内容中存在敏感词汇['.$sen_info['msg'].'],请确认后再提交');
			        $this->show($this->type);
				}
	            if($_POST['img'] && is_array($_POST['img'])){
	            	$_POST['img'] = $img_arr;
	            	$_POST['img'] = '';
	            	foreach($img_arr as $k=>$v){
	            		if(strpos($v,"Uploads") === false){
							if($v)//评论照片
							{
								$arr=R('Upimage/upImg',array($v,array(34,66)));//将接受的图片转换并写入后台
								$_POST['img']=$_POST['img']?','.$arr['img']:$arr['img'];//将接受的图片转换并写入后台
							}
						}
	            	}
	            }
	            //$_POST['addtime'] = time();
	            if($m->create()){
	            	$arr = $m->where($where)->save();
	            	if($arr){
	            		$this->saveScore($info['mer_id']);//更改商户的综合评分
	            		$this->setSuccess(true);
				        $this->setMsg('成功修改评论');
				        $this->show($this->type);
	            	}else{
	            		$this->setSuccess(false);
				        $this->setMsg('修改失败');
				        $this->show($this->type);
	            	}
	            }
			}else{ 
				$info['merlist'] = $this->getMerchant($city_lat,$city_lng,'id='.$info['mer_id'],'name,address,per_money,img','','1'); // 商户列表
				$this->setSuccess(true);
				$this->SetInfo($info);
		        $this->setMsg('修改评论');
		        $this->show($this->type);
			}
		}
	}
	/**
	 * [delEvaluate 删除评价]
	 * @param ser_id  用户id
	 * @param eva_id  评价id
	 * @return [success] 删除状态true/false  [msg] 提示信息
	 */
	public function delEvaluate(){
		$ser_id = I('ser_id');
		$eva_id = I('eva_id');
		$where['ser_id'] = array('eq',$ser_id);
		$where['id'] = array('eq',$eva_id);
		$info = M('Evaluate')->where($where)->getField('mer_id');//评价商户的id
		if($info){
			$arr = M('Evaluate')->where($where)->delete();
			if($arr){
				$this->saveScore($info);//更改商户的综合评分
				$this->setSuccess(true);
		        $this->setMsg('成功删除评论');
		        $this->show($this->type);
			}else{
				$this->setSuccess(false);
		        $this->setMsg('删除失败');
		        $this->show($this->type);
			}
		}else{
			$this->setSuccess(false);
	        $this->setMsg('删除失败,该评价信息不存在');
	        $this->show($this->type);
		}
	}

	/**
	 * 收藏商户
	 * @param u_id  用户id
	 * @param mer_id  商户id
	 * @return true/false   msg:提示信息
	 */
	public function addColl(){
		$m = M('Servuser');
		$u_id = I('u_id');
		$mer_id = I('mer_id');
		$where['id'] = array('eq',$u_id);
		$info = $m->where($where)->getField('mer_id');
		$mid = ','.$mer_id.',';
		if($info){
			if(strpos($info,$mid)===false){
				$data['mer_id'] = $info.$mer_id.',';
			}else{
				$this->setSuccess(false);
		        $this->setMsg('该商户已经收藏，不能重复收藏');
		        $this->show($this->type);
			}
		}else{
			$data['mer_id'] = $mid;
		}
		$re = $m->where($where)->save($data);
		if($re){
			$this->setSuccess(true);
	        $this->setMsg('商户收藏成功');
	        $this->show($this->type);
		}else{
			$this->setSuccess(false);
	        $this->setMsg('商户收藏失败');
	        $this->show($this->type);
		}
	}
	/**
	 * 我的收藏
	 * @param u_id  用户id
	 * @param city_lng 经度
	 * @param city_lat 纬度
	 * @return array：merlist商户信息，area地区信息，Industry行业信息
	 */
	public function merList(){
		$m = M('Merchant');
		/*$u_id = 1;//用户id
		$city_lat = 39.979364; //兴发大厦
		$city_lng = 116.324481;*/
		$u_id = I('u_id');//用户id
		$city_lat = I('city_lat'); 
		$city_lng = I('city_lng');
		if(!$u_id){
			$this->setSuccess(false);
	        $this->setMsg('请登录之后再查看');
	        $this->show($this->type);
		}
		if(!$city_lat || !$city_lng){
			$this->setSuccess(false);
	        $this->setMsg('请提供当前地区的经纬度');
	        $this->show($this->type);
		}
		$mer_id = M('Servuser')->where('id='.$u_id)->getField('mer_id');
		/*//分页
		$pagestare=0;
		$pagesize = 10;
		$nowPage  = isset($_GET['page'])?(intval($_GET['page'])?intval($_GET['page']):1):1;
		$pagestare = ($nowPage-1)*$pagesize;
		$pageend = $pagestare+$pagesize;
		$sql = 'select ACOS(SIN(('.$city_lat.' * 3.1415) / 180 ) *SIN((postlat * 3.1415) / 180 ) +COS(('.$city_lat.' * 3.1415) / 180 ) * COS((postlat * 3.1415) / 180 ) *COS(('.$city_lng.' * 3.1415) / 180 - (postlong * 3.1415) / 180 ) ) * 6380 as distance,id,name,address,per_money,description,img,uscore from wa_merchant where id in ('.substr($mer_id,1,-1).') order by distance asc, uscore desc,id desc limit '.$pagestare.','.$pageend;
		*/
		$sql = 'select ACOS(SIN(('.$city_lat.' * 3.1415) / 180 ) *SIN((postlat * 3.1415) / 180 ) +COS(('.$city_lat.' * 3.1415) / 180 ) * COS((postlat * 3.1415) / 180 ) *COS(('.$city_lng.' * 3.1415) / 180 - (postlong * 3.1415) / 180 ) ) * 6380 as distance,id,name,address,per_money,description,img,uscore from wa_merchant where id in ('.substr($mer_id,1,-1).') order by distance asc, uscore desc,id desc ';
		$list = $m->query($sql);
		if($list){
			$this->setSuccess(true);
			$this->SetInfo($list);
	        $this->setMsg('收藏商户列表');
	        $this->show($this->type);
		}else{
			$this->setSuccess(false);
	        $this->setMsg('暂无相关收藏');
	        $this->show($this->type);
		}	
	}
	/**
	 * 取消收藏
	 * @param u_id  用户id
	 * @param mer_id  多个商户id，用“,”连接 
	 * @return true/false   msg:提示信息
	 */
	public function delColl(){
		$m = M('Servuser');
		$u_id = I('u_id');
		$mer_id = ','.trim(I('mer_id')).',';
		$where['id'] = array('eq',$u_id);
		$info = $m->where($where)->getField('mer_id');
		if($info){
			$arr = array_filter(explode(',',$info));
			foreach($arr as $k=>$v){
				if(strstr($mer_id,','.$v.',')){//存在要删除的id
					unset($arr[$k]);
				}
			}
			if($arr){
				$data['mer_id'] = ','.implode(',',$arr).',';
			}else{
				$data['mer_id'] = '';
			}
			$row = $m->where($where)->save($data);
			if($row){
				$this->setSuccess(true);
		        $this->setMsg('收藏商户删除成功');
		        $this->show($this->type);
			}else{
				$this->setSuccess(false);
		        $this->setMsg('收藏商户删除失败');
		        $this->show($this->type);
			}	
		}else{
			$this->setSuccess(false);
	        $this->setMsg('该用户无收藏商户');
	        $this->show($this->type);
		}		
	}
	/**
	 * 修改个人信息（昵称，头像） 
	 * @param  u_id  用户id
	 * @param  name 昵称
	 * @param  img 用户头像
	 */
	public function saveAbout()
	{
		$m=M('Servuser');
		if($this->isPost()){
			$data['name'] = trim(I('name'));
			$arr = R('Upimage/upImg',array(trim(I('Img')),array(1,33)));//将接受的图片转换并写入后台
			$data['Img'] = $arr['img'];//将接受的图片转换并写入后台
			$lastid=$m->where('id='.I('u_id'))->save($data);
			if($lastid){
				$this->setSuccess(true);
	            $this->setMsg('修改成功');
	            $this->show($this->type);
			}else{
				$this->setSuccess(false);
	            $this->setMsg('修改失败');
	            $this->show($this->type);
			}		
		}else{
			$this->setSuccess(false);
            $this->setMsg('参数错误');
            $this->show($this->type);
		}	
	}
	/**
	 * [getMerchant 获取商户的基本信息]
	 * @param  string $lng   经度
	 * @param  string $lat   纬度
	 * @param  string $where 条件 注：原生态的sql
	 * @param  string $field 所需字段
	 * @param  string $order 排序
	 * @param  string $limit 获取条数
	 * @return [type]        array
	 */
	private function getMerchant($lng='',$lat='',$where='',$field='',$order='distance asc, uscore desc',$limit=''){
		$sql = 'select ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((postlat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((postlat * 3.1415) / 180 ) *COS(('.$lng.' * 3.1415) / 180 - (postlong * 3.1415) / 180 ) ) * 6380 as distance,2 ';
		if($field){
			$sql.=' ,'.$field.' from wa_merchant ';
		}else{
			$sql.=' from wa_merchant ';
		}
		if($where){
			$sql.=' WHERE '.$where;
		}if($order){
			$sql.=' ORDER BY '.$order;
		}
		if($limit){
			$sql.=' Limit 0,'.$limit;
		}
		$list = M('Merchant')->query($sql);
		if($list){
			foreach ($list as $k => $v) {
				$list[$k]['distance'] = round($v['distance'],2);
			}
		}
		if(count($list)==1){
			$list = $list[0];
		}
		//echo M('Merchant')->getLastsql();
		if($list){
			return $list;
		}else{
			return array();
		}
	}
	/**
	 * [saveScore 计算并更改商户的星级评价]
	 * @param string  $mer_id 商户id
	 */
	private function saveScore($mer_id=''){
		$n = M('Merchant');
		$m = M('Evaluate');
		$sql = 'SELECT sum(`score`) as `scor` FROM `wa_evaluate` WHERE `mer_id`='.$mer_id;
		$list = $m->query($sql);
		$num = $m->where('mer_id='.$mer_id)->count();
		if($num){
			$zfen = round($list[0]['scor']/$num,2); //用户综合评分
			$gfen = $n->where('id='.$mer_id)->getField('score');//管理员评分
			$data['uscore'] = round(($zfen+$gfen)/2,2); //综合评分
			$info = $n->where('id='.$mer_id)->save($data);
			if($info){
				return $info;
			}else{
				return '';
			}
		}else{
			return '';
		}	
	}
	/**
	 * 优惠券
	 * @param  map 条件
	 * @param  limit 获取条数
	 * @return  array [优惠券列表]
	 */
	private function Coupon($map='',$limit='')
	{
		$list = array();
		$where['Chit.status'] = array('eq',1);
		$where['Chit.end_time'] = array('egt',time());//优惠券未过期
		if($map){ 
			$where=array_merge($where,$map);
		}
		if($limit){
			$list=D('ChitView')->where($where)->order('Chit.start_time asc')->limit($limit)->select();
		}else{
			$list=D('ChitView')->where($where)->order('Chit.start_time asc')->select();
		}
		/*echo D('ChitView')->getLastsql();
		exit;*/
		return $list;
	}
	/**
	 * [senYanzheng 敏感词汇验证]
	 * @return [type] true/false
	 */
	private function senYanzheng($tit=''){
		$sen_arr = M('Sensitive')->getField('id,name');
		foreach($sen_arr as $k=>$v){
			if(strpos($tit,$v)!==false){
				$arr['suc'] = false;
				$arr['msg'] = $v;
				return $arr;
			}else{
				$arr['suc'] = true;	
			}
		}
		return $arr;
	}
	/**
	 * 行业列表
	**/
	private function getIndustry($map=array())
	{
		$list = array();
		$m=M('Industry');
		$order = array('sort'=>'desc');
		$field = array('id,name,img');
		$m = $m->field($field)->order($order);
		if($map) $m = $m->where($map);
		$list = $m->select();
		return $list;
	}
	/**
	 * 地区列表
	**/
	public function getArea($map=array())
	{
		$list = array();
		$m=M('Area');
		$order = array('ar_ename'=>'asc');
		$field = array('ar_id,ar_name');
		$m = $m->field($field)->order($order);
		if($map) $m = $m->where($map);
		$list = $m->select();
		if($list){
			$this->setSuccess(true);
			$this->SetInfo($list);
	        $this->setMsg('地区列表');
	        $this->show($this->type);
		}else{
			$this->setSuccess(false);
	        $this->setMsg('暂无地区信息');
	        $this->show($this->type);
		}
	}
}