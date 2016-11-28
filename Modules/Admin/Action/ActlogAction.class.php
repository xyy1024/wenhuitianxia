<?php
class ActlogAction extends CommonAction
{
	protected $tab='Actionlog';
	protected $tabview='ActlogView';
	protected $pp_title='日志管理';
	protected $tab_field='';	//表内字段名
	public function __construct() {
		parent::__construct();
		$this->assign('pp_title',$this->pp_title);
		$this->tab_field = array('id','aid','done','addtime');
	}
	/**
	 * 日志入库
	 * @return [type] [description]
	 */
	public function recorde(){
		$done_r = json_decode($this->getDone(),true);	//日志是否记录
		// dump($done_r['jilu']);
		switch($done_r['jilu']){
			case '1':
				$r = R('Actlog/logAdd',array(cookie('AREA'),$done_r['done'],time(),get_client_ip()));
				break;
			case '2':
				if($this->isPost()){
					if($done = I('post.ar_name','','trim')){	//修改时加上标题
						$done_r['done'] .= '：'.$done;
					}elseif($done = I('post.uid')){
						$done_r['done'] .= '：'.$done;
					}elseif($done = I('post.username','','trim')){
						$done_r['done'] .= '：'.$done;
					}elseif($done = I('post.mobile','','trim')){
						$done_r['done'] .= '：'.$done;
					}elseif($done = I('post.name','','trim')){
						$done_r['done'] .= '：'.$done;
					}elseif($done = I('post.title','','trim')){
						$done_r['done'] .= '：'.$done;
					}elseif($done = I('post.area')){
						if(is_numeric($done)){
							$area_list = $this->getData('Area',$field='ar_id,ar_name',$order=array('ar_ename'=>'asc'),array('ar_id'=>array('eq',$done)));
							if($area_list){
								$done = $area_list[$done];
							}
						}
						$done_r['done'] .= '：'.$done;
					}
					$r = R('Actlog/logAdd',array(cookie('AREA'),$done_r['done'],time(),get_client_ip()));
				}
				break;
			case '0':
			default:;
		}
	}
	/**
	 * 返回操作说明列表
	 * @return json
	 */
	private function getDone(){
		$done_list = S('done_list');
		if(!$done_list){
			$m = M('Authrule');
			$order['catid'] = 'asc';
			$order['sort'] = 'asc';
			$field = 'nameback,jilu,done';
			$list = $m->order($order)->getField($field);
			if($list){
				$done_list = json_encode($list);
				S('done_list',$done_list,86400);
			}
		}
		if(!$done_list){
			$r['jilu'] = 0;
			return json_encode($r);
		}
		$arr_done = json_decode($done_list,true);
		if($arr_done){
			$action = strtolower(MODULE_NAME).'-'.strtolower(ACTION_NAME);
			if(array_key_exists($action,$arr_done)){
				$r = $arr_done[$action];
			}else{
				$r['jilu'] = 0;
			}
		}else{
			$r['jilu'] = 0;
		}
		return json_encode($r);
	}
	/**
	 * 日志管理
	*/
	public function index(){
		$m=D($this->tabview);
		//导入tp的分页类
		import('ORG.Util.Pages2');

		$where = array();
		$s=array();	//搜索条件 area aid addtime
		//地区
		if($this->_post('area')){	
			$where['area'] = array('eq',I('post.area'));
			$s['area'] = $this->_post('area');
		}elseif(cookie('AREA')){
			$where['area'] = array('eq',cookie('AREA'));
		}

		if($this->_post('aid')){
			$where['aid']=array('eq',I('post.aid'));
			$s['aid']=$this->_post('aid');
		}
		if($this->_post('addtime')){
			$addtime = strtotime($this->_post('addtime'));
			$where['addtime']=array(array('gt',$addtime),array('lt',$addtime+86399));
			$s['addtime']=$this->_post('addtime');
		}
		$this->assign('s',$s);

		// 获取总的记录数
		$count = $m->where($where)->count();
		$count = $count===null?0:$count;

		if(I('stp') == 'all'){	//显示全部
			$p_num = $count;
		}else{
			$p_num = 20;
		}
		$nowPage = isset($_GET['p']) ? $_GET['p'] : 1;
		// page类
		$page = new Pages2($count,$p_num);
		$order['id'] = 'desc';
		$data=$m->where($where)->page($nowPage.','.$page->listRows)->order($order)->select();
		// echo $m->getLastSql();
		// exit;
		$PageConfig=array('prev'=>'<i class="fa fa-chevron-left"></i>','next'=>'<i class="fa fa-chevron-right"></i>','theme'=>'%prePage% %linkPage% %nextPage%');
		$page->config=$PageConfig;
		$show = $page->show();
		$this->assign('page',$show);
		$this->assign('totalRows',$page->totalRows);
		$this->assign('totalPages',$page->totalPages);
		$this->assign('nowPage',$page->nowPage);
		$this->assign('data', $data);

		if(!cookie('ADMIN_POWER')){
			$area_map[]= '`ar_id`='.cookie('AREA');
		}else{
			$area_map = array();
		}
		//$this->assign('area',$this->getArea($area_map));	//地区
		$this->assign('area',$this->getData('Area','ar_id,ar_name',array('ar_od'=>'asc'),$area_map));	//地区
		$this->display();
	}
	/**
	 * ajax获取地区管理员
	 * @return json
	 */
	public function getAdminuser(){
		if(!cookie('ADMIN_POWER')){
			$mer_map['area'] = array('eq',cookie('AREA'));
		}elseif($area = I('get.area')){
			$mer_map['area'] = array('eq',$area);
		}else{
			$mer_map = array();
		}
		$adminuser = $this->getData('Admin','id,username',array(),$mer_map);	//（所属地区）所有管理员
		if($adminuser){
			$data['suc'] = true;
			$data['list'] = $adminuser;
		}else{
			$data['suc'] = false;
		}
		echo json_encode($data);
	}
	/**
	 * 添加日志
	 * @param  id $area    地区id
	 * @param  text $done    操作内容
	 * @param  int $addtime 操作时间
	 */
	public function logAdd($area,$done,$addtime,$ip='')
	{
		if(!cookie('ADMIN_KEY') || !cookie('ADMIN_NAME') || !$done){
			$this->error('actlog信息有误，请确认');
			exit;
		}
		$data['aid'] = cookie('ADMIN_KEY');
		$data['area'] = $area?$area:0;
		$data['done'] = cookie('ADMIN_NAME').'于'.date('Y-m-d H:i:s',$addtime).$done;
		$data['addtime'] = $addtime;
		$data['ip'] = $ip;
		$m = M($this->tab);
		$lastid = $m->data($data)->add();
		return $lastid;
	}
	/**
	 * 删除日志分类
	 */
	public function delete()
	{
		$id = I('id','intval');
		if(!$id){
			$this->error('信息有误');
		}
		$m = M($this->tab);
		$r = $m->where('`id`='.$id)->delete();
		if ($r) {
			$this->success('删除日志成功');
		} else {
			$this->error('删除日志失败');
		}
	}
	/**
	 * 批量操作
	 */
	public function batch(){
		$Article=M($this->tab);
		if($this->isPost()){
			$map['id']=array('in',$this->_param('check'));
			if($this->_param('Delete')){		
				$aa=$Article->where($map)->delete();
				if($aa){
					$this->success('删除成功！',U('index',array('p'=>$this->_param('p'))));
				}else{
					$this->error('删除失败！');
				}
			}
		}
	}
	
}