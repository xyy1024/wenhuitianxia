<?php
//商户管理
class MerchantAction extends CommonAction 
{	
	public $tab='Merchant';
	public $intab='Industry';
	protected $imgsize = '217*217';
	public function __construct() {
		parent::__construct();
		R('Actlog/recorde');	//日志
	}
	public function index()
	{
		$Links=D($this->tab);
		//dump($cat);
		import('ORG.Util.Pages2');
		if(I('contentt')=='0')
		{
			$where['name']=array('like','%'.I('title').'%');
		}if(I('contentt')=='1')
		{
			$where['son_tel']=array('like','%'.I('title').'%');
		}
		if(I('ins'))
		{
			if(I('ins')=='all'){
				$where['ins_id']=array('eq','all');
			}else{
				$where['ins_id']=array('like','%,'.I('ins').',%');
			}
		}
		if(I('stp') == 'all'){
			$num=$count;
		}else{
			$num=20;
		}
		if(cookie('AREA')){//地区
			$where['area'] = array('eq',cookie('AREA'));
		}
		$count = $Links->where($where)->count();
		$Page = new Pages2($count,$num);
		if(intval(I('post.page'))) $Page->nowPage=abs(intval(I('post.page')))>$Page->totalPages?$Page->totalPages:abs(intval(I('post.page')));
		$orderby['sort']='desc';
		$orderby['id']='desc';
		$list = $Links->where($where)->order($orderby)->page($Page->nowPage.','.$Page->listRows)->select();
		$PageConfig=array('prev'=>'<i class="fa fa-chevron-left"></i>','next'=>'<i class="fa fa-chevron-right"></i>','theme'=>'%prePage% %linkPage% %nextPage%');
		$Page->config=$PageConfig;
		$show=$Page->show();
		$this->assign('page',$show);
		$this->assign('totalRows',$Page->totalRows);
		$this->assign('totalPages',$Page->totalPages);
		$this->assign('nowPage',$Page->nowPage);
		$this->assign('list',$list);
		$this->assign('area',$this->getData('Area',$field='ar_id,ar_name',$order=array('ar_ename'=>'asc')));
		//获取行业信息
		$ins_arr = M($this->intab)->field('id,name')->order('id asc,sort desc')->select();
		$this->assign('ins_arr',$ins_arr);
		$this->assign('search',I(''));
		$this->display();
	}
	
	
	public function isshow($arrid=0){
		$Links=D($this->tab);
		if(I('id')){
			$map['id']=array('eq',I('id'));
			$ba=$Links->where($map)->find();
			if($ba['isshow']==1){
				$mdata['isshow']=0;
			}else{
				$mdata['isshow']=1;
			}
			$mdata['updatetime']=time();
			$aa=$Links->where($map)->save($mdata);
			if($aa){
				$this->success('审核成功！',U('index'),array('cid'=>I('cid'),'p'=>I('p')));
			}else{
				$this->error('审核失败！');
			}
		}else{
			$map['id']=array('in',$arrid);
			$mdata['isshow']=1;
			$mdata['updatetime']=time();
			$aa=$Links->where($map)->save($mdata);
			return $aa;
		}		
	}
	
	public function delete($allid=0){
		$Links=D($this->tab);
		if($this->isGet()){
			$map['id']=array('in',I('id'));			
			$aa=$Links->where($map)->delete();
			if($aa){
				$this->success('删除成功！',U('index',array('p'=>I('p'))));
			}else{
				$this->error('删除失败！');
			}
		}else{
			$map['id']=array('in',$allid);
			$aa=$Links->where($map)->delete();
			return $aa;
		}
	}
	
	
	public function add(){
		$Links=D($this->tab);		
		if($this->isPost()){		
			if($Links->create(I('post.'))){
				$aa=$Links->add();
				if($aa){
					$this->success('添加商户成功！',U('index'));
				}else{
					$this->error('添加商户失败！');
				}
			}else{
				$this->error($Links->getError());
			}
			
		}else{
			//获取行业信息
			$ins_arr = M($this->intab)->field('id,name')->order('id asc,sort desc')->select();
			$this->assign('ins_arr',$ins_arr);
			//地区
			$this->assign('arealist',$this->getArea());
			$this->assign('imgsize',$this->imgsize);//图片尺寸
			$this->display();
		}
	}
	
	public function edit(){
		$Links=D($this->tab);
		if($this->isPost()){
			if($Links->create(I('post.'))){
				//删除附件
				if($_POST['logo'] != $_POST['oldlogo'])
				{
					del_attach($_POST['oldlogo']);//将attachments中的数据和本地文件删除
				}
				$aa=$Links->where('id='.I('id'))->save();
				if($aa){
					$this->success('修改成功！',U('index',array('cid'=>I('cid'),'p'=>I('p'))));
				}else{
					$this->error('修改失败！');
				}
			}else{
				$this->error($Links->getError());
			}
		}else{
			//获取行业信息
			$ins_arr = M($this->intab)->field('id,name')->order('id asc,sort desc')->select();
			$this->assign('ins_arr',$ins_arr);
			//地区
			$this->assign('arealist',$this->getArea());
			$maxsort=$Links->where($map)->max('sort');
			$data=$Links->where('id='.I('id'))->find();
			$this->assign('data',$data);
			$this->assign('imgsize',$this->imgsize);//图片尺寸
			$this->display();
		}
	}
	/**
	 * 查看商户下的优惠券
	 */
	public function look(){
		$m=M('Chit');
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
		
		$where['merchant'] = array('like','%'.I('id').'%');
		// 获取总的记录数
		
		$count = $m->where($where)->count();
		if(I('ye') == 'all'){	//显示全部
			$p_num = $count;
		}elseif(I('ye')){
			$p_num = I('ye');
		}else{
			$p_num = 20;
		}
		$s['ye'] = I('ye');
		$this->assign('s',$s);
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
		$pp_title = M($this->tab)->where('id='.I('id'))->getField('name'); //面包屑商户名称
		$this->assign('pp_title',$pp_title);
		$this->assign('cate',C('CHIT_CATE'));	//优惠券分类
		$this->assign('industry',$this->getData('Industry','id,name',array('sort'=>'asc')));	//行业
		$this->display();
	}
	/**
	 * 异步判断商户下是否存在优惠券
	 * @return true/false
	 */
	public function isChit(){
		$m=M('Chit');	
		$where['merchant'] = array('like','%'.I('post.id').'%');
		$count = $m->where($where)->count();
		if($count>0){
			echo $count;
		}else{
			echo false;
		}
	}
	/**
	 * 批量上传
	 * @return [type] [description]
	 */
	public function importAdd(){
		$m=D($this->tab);
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
		$this->display();
	}
	private function importexcel($file){
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
		//require '/Public/PHPExcel/PHPExcel/IOFactory.php';
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
		//获取地区信息
		$ar_arr = M('Area')->getField('ar_name,ar_id');
		//获取行业信息
		$ins_arr = M('Industry')->getField('name,id'); 
		$un_arr = array();
		for ($row = 2;$row <= $highestRow;$row++) 
		{
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
				// echo $strs[$col].' &nbsp;';
			}
			/*dump($strs);	
			echo '<br/>';	*/
			$e_data=array();
			$e_data['name'] = str_replace(array(' ',' ','　'),'',$strs[0]);
			$strs[1] = str_replace(array(' ',' ','　'),'',$strs[1]);
			$e_data['area'] = $ar_arr[$strs[1]];//地区id
			$strs[2] = str_replace(array(' ',' ','　','，','行业'),array('','','',',',''),$strs[2]);
			$insStr = explode(',',$strs[2]);
			$ins_id = '';
			foreach($insStr as $vi){
				$ins_id.= $ins_id?$ins_arr[$vi].',':','.$ins_arr[$vi].',';//行业id
			}
			$e_data['ins_id'] = $ins_id;//行业id
			$e_data['shop_sta'] = str_replace(array(' ',' ','　'),'',$strs[3]);
			$e_data['shop_end'] = str_replace(array(' ',' ','　'),'',$strs[4]);
			$e_data['address'] = str_replace(array(' ',' ','　'),'',$strs[5]);
			$e_data['postlong'] = str_replace(array(' ',' ','　'),'',$strs[6]);
			$e_data['postlat'] = str_replace(array(' ',' ','　'),'',$strs[7]);
			$e_data['per_money'] = str_replace(array(' ',' ','　'),'',$strs[8]);
			$e_data['cus_tel'] = str_replace(array(' ',' ','　'),'',$strs[9]);
			$e_data['contacts'] = str_replace(array(' ',' ','　'),'',$strs[10]);
			$e_data['son_tel'] = str_replace(array(' ',' ','　'),'',$strs[11]);
			$e_data['description'] = str_replace(array(' ',' ','　'),'',$strs[12]);
			$e_data['content'] = str_replace(array(' ',' ','　'),'',$strs[13]);
			$e_data['isshow']=1;
			$e_data['addtime']=time();
			// 检测商户是否存在
			$m = M('Merchant');
			$map['name'] = array('eq',$e_data['name']);
			$map['ins_id'] = array('eq',$e_data['ins_id']);
			$map['area'] = array('eq',$e_data['area']);
			$info_arr = $m->where($map)->field('id,name,son_tel,ins_id,shop_sta,shop_end')->find();
			if($info_arr){//数据信息已存在
				$un_arr[] = $info_arr;//已存在商户
			}else{
				$re_row = $m->add($e_data);
			}
		 }
		ob_end_clean();
		if(file_exists($file2))
		{
			unlink($file2);
		}
		if($un_arr){ // 部分导入成功
			$this->assign('un_arr',$un_arr);
			$this->assign('ins_arr',M('Industry')->getField('id,name'));
			$this->display('unindex');
			exit;
		}else if($re_row){
			$this->success('批量导入成功',U('index'));
		}else{
			$this->error('批量导入失败');
		}
	}
	public function form(){
		$Article=D($this->tab);
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
	public function Shenhe(){
		$Links=D($this->tab);
		$is=$Links->where('id='.$this->_param('id'))->getField('isshow');
		if($is==1){
			$isnew=0;
		}else{
			$isnew=1;
		}
		$aa=$Links->where('id='.$this->_param('id'))->setField('isshow',$isnew);
		if($aa){
			$msg['success']=true;
			$msg['msg']='修改成功';
			$msg['val']=$isnew;
		}else{
			$msg['success']=false;
		}
		echo json_encode($msg);
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