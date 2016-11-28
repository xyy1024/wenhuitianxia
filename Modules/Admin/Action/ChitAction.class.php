<?php
class ChitAction extends CommonAction
{
	protected $tab='Chit';
	protected $ctab='Chitlist';
	protected $ctabView='ChitlistView';
	protected $pp_title='优惠券管理';
	protected $cate='';	//优惠券分类
	protected $tabField='';	//表内字段名
	public function __construct() {
		parent::__construct();
		$this->cate=C('CHIT_CATE');	//优惠券分类
		$this->assign('pp_title',$this->pp_title);
		$this->tabField = array('id','title','num','u_num','start_time','end_time','cate','price','price2','rule','industry','merchant','status','addtime','edittime');
		R('Actlog/recorde');	//日志
	}
	/**
	 * 优惠券管理
	*/
	public function chit(){
		header('Content-Type:text/html;charset=utf-8');
		$m=M($this->tab);
		//导入tp的分页类
		import('ORG.Util.Pages2');

		$where = array();
		$s=array();	//搜索条件 name cate industry merchant overdue
		if($this->_post('name')){
			$where['title']=array('like','%'.$_POST['name'].'%');
			$s['name']=$this->_post('name');
		}
		if($this->_post('cate')){
			$where['cate']=array('eq',I('post.cate'));
			$s['cate']=$this->_post('cate');
		}
		if($this->_post('industry')){
			$where['industry']=array('like','%,'.I('post.industry').',%');
			$s['industry']=$this->_post('industry');
		}
		if($this->_post('merchant') && $mer_where = $this->getSearchMerchant(I('post.merchant'))){
			$where['merchant'] = $mer_where;
			$s['merchant']=$this->_post('merchant');
		}
		if($overdue=$this->_post('overdue')){
			switch($overdue){
				case '1':
					$where['end_time'] = array('egt',time());
					break;
				case '2':
					$where['end_time'] = array('lt',time());
					break;
				default:
			}
			$s['overdue']=$overdue;
		}
		$this->assign('s',$s);

		//地区
		if(cookie('AREA')){
			$where['area'] = array('eq',cookie('AREA'));
		}

		// 获取总的记录数
		$count = $m->where($where)->count();

		if(I('stp') == 'all'){	//显示全部
			$p_num = $count;
		}else{
			$p_num = 20;
		}
		$nowPage = isset($_GET['p']) ? $_GET['p'] : 1;
		// page类
		$page = new Pages2($count,$p_num);
		$field = array('id','title','num','r_num','u_num','cate','start_time','end_time','cate','industry','merchant','status','addtime','edittime');

		$data=$m->where($where)->page($nowPage.','.$page->listRows)->select();
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

		$this->assign('cate',$this->cate);	//优惠券分类
		$this->assign('industry',$this->getData('Industry','id,name',array('sort'=>'asc')));	//行业

		if(cookie('AREA')){
			$mer_map['area'] = array('eq',cookie('AREA'));
		}else{
			$mer_map = array();
		}
		$this->assign('merchant',$this->getData('Merchant','id,name',array('sort'=>'asc'),$mer_map));	//（所属地区）所有商户

		$this->display();
	}
	/**
	 * 领取优惠券管理
	*/
	public function chitlist(){
		$m = D($this->ctabView);
		import('ORG.Util.Pages2');
		if(I('name'))
		{
			$where['Servuser.name']=array('like','%'.I('name').'%');
		}
		if(I('isuse'))
		{
			$where['Chitlist.is_use']=array('eq',I('isuse'));
		}
		
		if(I('stp') == 'all'){	//显示全部
			$p_num = $count;
		}else{
			$p_num = 20;
		}
		$where['Chitlist.c_id'] = array('eq',I('cid'));
		$count = $m->where($where)->count();
		$list = $m->order($orderby)->where($where)->field('id,is_use,end_time')->select();
		$w_num=0;
		$y_num=0;
		$g_num=0;
		foreach($list as $kk=>$vv){
			if($vv['is_use']==1){//未使用数量
				$w_num++;
			}else{
				$y_num++;
			}if($vv['end_time']<time()){
				$g_num++;
			}
		}
		$carr['count'] = $count;//已领取数
		$carr['w_num'] = $w_num;//未使用数
		$carr['y_num'] = $y_num;//已使用数
		$carr['g_num'] = $g_num;//过期数
		$Page = new Pages2($count,$p_num);
		if(intval(I('post.page'))) $Page->nowPage=abs(intval(I('post.page')))>$Page->totalPages?$Page->totalPages:abs(intval(I('post.page')));
		$orderby['Chitlist.id']='desc';
		$data = $m->order($orderby)->where($where)->page($Page->nowPage.','.$Page->listRows)->select();
		$PageConfig=array('prev'=>'<i class="fa fa-chevron-left"></i>','next'=>'<i class="fa fa-chevron-right"></i>','theme'=>'%prePage% %linkPage% %nextPage%');
		$Page->config=$PageConfig;
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('totalRows',$Page->totalRows);
		$this->assign('totalPages',$Page->totalPages);
		$this->assign('nowPage',$Page->nowPage);
		$this->assign('data',$data);
		$this->assign('carr',$carr);
		$this->assign('search',I(''));
		$this->display();
	}
	/**
	 * 优惠券查询商户
	 * @param  string $mer 商户名称
	 * @return array
	 */
	private function getSearchMerchant($mer){
		if(cookie('AREA')){
			$where['area'] = array('eq',cookie('AREA'));
		}
		$where['name'] = array('like','%'.$mer.'%');
		$list = M('Merchant')->field('id')->where($where)->select();
		$map=array();
		if($list){
			foreach($list as $v){
				$map[] = array('like','%,'.$v['id'].',%');
			}
			$map[] = 'or';
		}
		return $map;
	}
	/**
	 * 添加优惠券
	*/
	public function chit_add()
	{
		if ($this->isPost()){
			$m = D($this->tab);
			if ($m->create()){
				$m->industry = ','.implode(',',$m->industry).',';	//行业
				$m->merchant = ','.implode(',',$m->merchant).',';	//商户
				if($m->add()) {
					$this->success('添加优惠券成功',U('chit'));
				} else {
					$this->error('添加优惠券失败');
				}
			}else {
				$this->error($m->getError());
			}
			exit;
		}
		$this->assign('cate',$this->cate);	//优惠券类型
		$this->assign('industry',$this->getData('Industry','id,name',array('sort'=>'asc')));	//行业
		$this->assign('area',$this->getArea());	//地区
		$this->display();
	}
	/**
	 * 修改优惠券
	*/
	public function chit_edit()
	{
		$id=$this->_param('id');
		if ($this->isPost()){
			$m = D($this->tab);
			if(!$_POST['price2']){
				$_POST['price2']=0;
			}
			if ($m->create()){
				$m->industry = ','.implode(',',$m->industry).',';	//行业
				$m->merchant = ','.implode(',',$m->merchant).',';	//商户
				$_POST['merchant'] = $m->merchant;	//商户
				$r = $m->where('`id`='.$id)->save();
				if ($r !== false) {
					//判断并修改chitlist表中的相关信息
					if($_POST['oldtitle']!=$_POST['title'] || $_POST['oldcate']!=$_POST['cate'] || $_POST['oldprice']!=$_POST['price'] || $_POST['oldprice2']!=$_POST['price2'] || $_POST['oldlink_id']!=$_POST['link_id'] || $_POST['oldstart_time']!=strtotime($_POST['start_time']) || $_POST['oldend_time']!=strtotime($_POST['end_time']) || $_POST['oldmerchant']!=$_POST['merchant']){
						$c_data['c_title'] = $_POST['title'];
						$c_data['c_cate'] = $_POST['cate'];
						$c_data['price'] = $_POST['price'];
						$c_data['price2'] = $_POST['price2'];
						$c_data['link_id'] = $_POST['link_id'];
						$c_data['merchant'] = $_POST['merchant'];
						$c_data['start_time'] = strtotime($_POST['start_time']);
						$c_data['end_time'] = strtotime($_POST['end_time']);
						M($this->ctab)->where('c_id='.$id)->save($c_data);
					}
					$this->success('优惠券修改成功',U('chit'));
				} else {
					$this->error('优惠券修改失败');
					exit;
				}
			}else{
				$this->error($m->getError());
				exit;
			}
			exit;
		}
		$m = M($this->tab);

		$info = $m->where('`id`='.$id)->find();
		$this->assign('info', $info);
		$this->assign('cate',$this->cate);	//使用范围
		$this->assign('industry',$this->getData('Industry','id,name',array('sort'=>'asc')));	//行业
		$this->assign('area',$this->getArea());	//地区
		$this->display();
		
	}

	/**
	 * ajax 异步获取商户
	 * @return json
	 */
	public function chit_merchant(){
		if(!I('post.area')){
			$r['suc'] = false;
			$r['msg'] = '<font color="#b94a48">地区信息有误，请确认所属地区</font>';
			$r['data'] = '';
			echo json_encode($r);
			exit;
		}
		if(I('post.merchant')){	//默认值商户
			$def_mer = array_filter(explode(',',I('post.merchant')));
		}else{
			$def_mer = array();
		}

		$id = I('post.id');	//选中的行业
		$id_arr = array_filter(explode(',',$id));
		$where_log = array();
		if($id_arr){
			foreach($id_arr as $k =>$v){
				$where_log[] = '%,'.$v.',%';
			}
		}
		if($where_log){
			$where['area'] = array('eq',I('post.area'));
			$where['ins_id'] = array('like',$where_log,'or');
			$merchant = $this->getData('Merchant','id,name',array('sort'=>'asc'),$where);
			if($merchant){
				$str='';
				foreach($merchant as $k => $v){
					$str.='<label class="checkbox-inline chit_1"><input type="checkbox" name="merchant[]" value="'.$k.'"';
					if($def_mer && in_array($k, $def_mer)){
						$str.=' checked="checked"';
					}
					//if($k == count($merchant)){
						$str.=' datatype="*" nullmsg="请选择商户" ';
					//}
					$str.='>'.$v.'</label>';
				}
				$r['suc'] = true;
				$r['msg'] = '';
				$r['data'] = $str;
			}else{
				$r['suc'] = false;
				$r['msg'] = '无相关商户信息';
				$r['data'] = '';
			}
		}else{
			$r['suc'] = false;
			$r['msg'] = '请先选择行业';
			$r['data'] = '';
		}
		echo json_encode($r);
	}
	public function linkslist(){
		if(!I('get.area')){
			$r['suc'] = false;
			$r['msg'] = '<font color="#b94a48">地区信息有误，请确认所属地区</font>';
			$r['data'] = '';
			echo json_encode($r);
			exit;
		}
		$where['isshow'] = array('eq',1);//状态开启
		$where['ins_id'] = array('eq','all');
		$where['area'] = array('eq',I('get.area'));
		$where2['isshow'] = array('eq',1);//状态开启
		$where2['area'] = array('eq',I('get.area'));
		$where2['ins_id'] = array('neq','all');
		$all_data = M('Links')->where($where)->field('id,logo')->order('sort desc ,id desc')->find();
		$data = M('Links')->where($where2)->field('id,logo')->order('sort desc ,id desc')->select();
		$str='';
		if($all_data){
			$str.='<label class="radio-inline chit_1" style=" width: 200px;"><input type="radio" name="link_id" value="'.$all_data['id'].'"';
				if(I('lid')==$all_data['id']){
					$str.=' checked="checked"';
				}
			$str.='  datatype="*" nullmsg="请选择背景模版" > <a href="javascript:void(0);" onclick="addImgMessage('.$all_data['id'].')"><img height="30px;" src="'.$all_data['logo'].'"></a>(通用)</label>';
		}
		if($data){
			foreach($data as $k => $v){
				$str.='<label class="radio-inline chit_1" style=" width: 200px;"><input type="radio" name="link_id" value="'.$v['id'].'"';
				if(I('lid')==$v['id']){
					$str.=' checked="checked"';
				}
				$str.= ' > <a href="javascript:void(0);" onclick="addImgMessage('.$v['id'].')"><img height="30px;" src="'.$v['logo'].'"></a></label>';
			}
		}
		if($str){
			$r['suc'] = true;
			$r['msg'] = '';
			$r['data'] = $str;
			$r['js'] = '<script>$(document).ready(function(){$("#all").click(function () {  
				alert(222);
	        if (this.checked) {

	            $("#merchant :checkbox").attr("checked", true);  
	        } else {  
	            $("#merchant :checkbox").attr("checked", false);  
	        }  
	    });}); </script> ';
		}else{
			$r['suc'] = false;
			$r['msg'] = '无相关模版信息';
			$r['data'] = '';
		}
		echo json_encode($r);
		exit;
	}
	public function imgLook(){

		if(I('id')){
			$data = M('Links')->where('id='.I('id'))->getField('logo');
			$this->assign('img',$data);
			
		}else{
			$this->assign('info','信息有误');
		}
		$this->display();
	}
	/**
	 * 删除优惠券分类
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
			//删除Chitlist 优惠券
			$list_map['c_id'] = array('eq',$id);
			$r = $this->delChitlist($list_map);	

			$this->success('删除优惠券成功');
		} else {
			$this->error('删除优惠券失败');
		}
	}
	//批量上传
	public function  chitShenhe(){
		if($this->isPost()){
			if($_POST['file']){
				$info = $this->importexcel($_POST['file']);
				if($info['success']==true){
					$this->success($info['msg'],U('index'));
					exit;
				}else{
					$this->error($info['msg']);
				}
			}
		}
		//获取附件中的超过一个月多余附件并删除
		$arr = get_files('Uploads/excel/');
		foreach($arr as $k=>$v){
			if(filectime($v) <= time()-2592000){ //文件创建日期超过一个月
				if(file_exists($v))
				{
					unlink($v);
				}
			}
		}
		$this->display();
	}
	private function importexcel($file){
		header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
		@ini_set("max_execution_time", 0);
		@set_time_limit(0);
		$f_arr=explode('.',$file);
		if(!$file || ($f_arr[1] != 'xls' && $f_arr[1] != 'xlsx' && $f_arr[1] != 'xlsm')){
			$arr['success'] = false;
			$arr['msg'] = '请上传正确格式的excel文件';
			return $arr;
			exit;
		}
		$dir=dirname(__FILE__);
		$file2=realpath($dir.'/../../../'.$file);
		vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new PHPExcel();
        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_wincache;
		$cacheSettings = array( ’cacheTime’  => 600 );
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod,$cacheSettings); 		
		$fileType = PHPExcel_IOFactory::identify($file2); //文件名自动判断文件类型
		$objReader = PHPExcel_IOFactory::createReader($fileType);
		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($file2);
		$sheet = $objPHPExcel->getSheet(0); 
		$highestRow = $sheet->getHighestRow();           //取得总行数 
		$highestColumn = $sheet->getHighestColumn(); //取得总列数	
		$objWorksheet = $objPHPExcel->getActiveSheet();
		$highestRow = $objWorksheet->getHighestRow(); 
		$highestColumn = $objWorksheet->getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);//总列数
		
		$headtitle=array();
		$m = M($this->ctab);
		$m->startTrans(); // 启动事务
		$num=0;
		$cg_num=0;
		$sb_num=0;
		$exc_str='';
		$mer_arr = M('Merchant')->getField('name,id'); //商户
		//创建文件并写入
		$filename = iconv('utf-8', 'gb2312', '优惠券审核失败列表');//文件名称
        $name='./Uploads/excel/'.$filename.time(). '.xls';  
    	$myfile = fopen($name, "w") or die("Unable to open file!");
		$tab_str='<table width="100%" border="1" cellpadding="0" cellspacing="1"><tr><td>'.iconv("UTF-8", "GBK", "序号").'</td><td >'.iconv("UTF-8", "GBK", "优惠券串码").'</td><td >'.iconv("UTF-8", "GBK", "商户名称").'</td><td>'.iconv("UTF-8", "GBK", "使用时间").'</td><td >'.iconv("UTF-8", "GBK", "优惠券状态").'</td></tr>';
		fwrite($myfile,$tab_str); 
		unset($tab_str);
		for ($row = 2;$row <= $highestRow;$row++) 
		{
			$num++;
			 //echo $row.'<br/>';
			$strs=array();
			//注意highestColumnIndex的列数索引从0开始
			for ($col = 0;$col < $highestColumnIndex;$col++)
			{
				$strs[$col] =$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
				if(substr($strs[$col],0,1) == "=")	//判断是不是公式
				{
    					$strs[$col] = $objWorksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
				}
			}
			$time = ($strs[3]-25569)*24*60*60; //获得秒数 （日期格式转换）
			$e_data=array();
			$n_map['number'] = str_replace(array(' ',' ','　'),'',$strs[0]);
			if($n_map['number']){
				$mer_name = str_replace(array(' ',' ','　'),'',$strs[1]);
				$mer_id = $mer_arr[$mer_name]; //商户id
				if(!$mer_id){
					$tab_str='<tr><td>'.$num.'</td><td>'."'".$n_map['number'].'</td><td>'.iconv("UTF-8", "GBK", $mer_name).'</td><td>'.date('Y-m-d', $time).'</td><td>'.iconv("UTF-8", "GBK", '商户不存在').'</td></tr>';
					$exc_str.='<tr><td>'.$num.'</td><td>'.$n_map['number'].'</td><td>'.$mer_name.'</td><td>'.date('Y-m-d', $time).'</td><td>商户不存在</td></tr>';
					$sb_num++;
				}else{
					$ch_info = $m->where($n_map)->field('id,number,merchant,is_use,use_time,use_price,use_merid')->find();//优惠券信息
					if(!$ch_info){
						$tab_str='<tr><td>'.$num.'</td><td>'."'".$n_map['number'].'</td><td>'.iconv("UTF-8", "GBK", $mer_name).'</td><td>'.date('Y-m-d', $time).'</td><td>'.iconv("UTF-8", "GBK", '优惠券串码有误').'</td></tr>';
						$exc_str.='<tr><td>'.$num.'</td><td>'.$n_map['number'].'</td><td>'.$mer_name.'</td><td>'.date('Y-m-d', $time).'</td><td>优惠券串码有误</td></tr>';
						$sb_num++;
					}elseif($ch_info['is_use']==2 && $ch_info['use_price'] && $ch_info['use_merid']){
						$tab_str='<tr><td>'.$num.'</td><td>'."'".$n_map['number'].'</td><td>'.iconv("UTF-8", "GBK", $mer_name).'</td><td>'.date('Y-m-d', $time).'</td><td>'.iconv("UTF-8", "GBK", '优惠券已审核').'</td></tr>';
						$exc_str.='<tr><td>'.$num.'</td><td>'.$n_map['number'].'</td><td>'.$mer_name.'</td><td>'.date('Y-m-d', $time).'</td><td>优惠券已审核</td></tr>';
						$sb_num++;
					}else{
						if($ch_info['is_use']==1){
							$tab_str='<tr><td>'.$num.'</td><td>'."'".$n_map['number'].'</td><td>'.iconv("UTF-8", "GBK", $mer_name).'</td><td>'.date('Y-m-d', $time).'</td><td>'.iconv("UTF-8", "GBK", '优惠券未被使用').'</td></tr>';
							$exc_str.='<tr><td>'.$num.'</td><td>'.$n_map['number'].'</td><td>'.$mer_name.'</td><td>'.date('Y-m-d', $time).'</td><td>优惠券未被使用</td></tr>';
							$sb_num++;
						}else{	//审核成功，将商户id和消费金额入库
								$e_data['use_merid'] = $mer_id;
								$e_data['use_price'] = str_replace(array(' ',' ','　'),'',$strs[2]);
								$info_arr = $m->where($n_map)->data($e_data)->save();
								$cg_num++;
						}
					}
				}
				fwrite($myfile,$tab_str); 
				unset($tab_str); 
			}else{
				continue;
			}	
		}
		ob_end_clean();
		if(file_exists($file2))
		{
			unlink($file2);
		}
		if($sb_num > 0){
			$tab_str='</table>';//失败excel
			fwrite($myfile,$tab_str); 
			unset($tab_str);
			$name=iconv('gb2312', 'utf-8', $name);
	       	$but_excel = ' <a class="btn btn-sm btn-default" href="'.$name.'" ><i class="fa fa-th"> 下载审核失败的优惠券</i></a>';
			$this->assign('sb_num',$sb_num);//失败数量
			$this->assign('cg_num',$cg_num);//成功数量
			$this->assign('exc_str',$exc_str);//审核失败数据
			$this->assign('but_excel',$but_excel);//下载按钮
			$this->display('unindex');
			$m->rollback();//事务回滚
			exit;
		}else{
			$m->commit(); //提交事务
			$this->success('优惠券审核成功',U('chit'));
		}
	}
	/**
	 * 批量操作
	 */
	public function batch(){
		$Article=D($this->tab);
		if($this->isPost()){
			$map['id']=array('in',$this->_param('check'));
			if($this->_param('Delete')){		
				$aa=$Article->where($map)->delete();
				if($aa){
					//删除Chitlist 优惠券
					$list_map['c_id'] = $map['id'];
					$r = $this->delChitlist($list_map);

					$this->success('删除成功！',U('chit',array('p'=>$this->_param('p'))));
				}else{
					$this->error('删除失败！');
				}
			}
		}
	}
	/**
	 * 删除优惠券分类下已领取的优惠券
	 * @param  array  $map [description]
	 * @return [type]	  [description]
	 */
	private function delChitList($map = array())
	{
		$r = M('Chitlist')->where($map)->delete();
		if($r){
			return true;
		}else{
			return false;
		}
	}
	/**
	 * ajax 优惠券状态修改
	 */
	public function Shenhe(){
		$Article=D($this->tab);
		$is=$Article->where('id='.$this->_param('id'))->getField('status');
		if($is==1){
			$isnew=0;
		}else{
			$isnew=1;
		}
		$aa=$Article->where('id='.$this->_param('id'))->setField('status',$isnew);
		if($aa){
			$msg['success']=true;
			$msg['msg']='修改成功';
			$msg['val']=$isnew;
		}else{
			$msg['success']=false;
		}
		echo json_encode($msg);
	}
	/*public function editattr()
	{
		$data = array();
		$_id = I('id');
		$_field = I('f');
		$_status  = I('status');
		$m = M($this->tab);
		$d[$_field] = $_status;
		$affected = $m->where('`id`='.$_id)->data($d)->save();
		if ($affected != false) {
			$data['success'] = 1;
			$data['msg'] = '操作成功';
		} else {
			$data['success'] = 0;
			$data['msg'] = '操作失败';
		}
		echo json_encode($data);
	}*/
}