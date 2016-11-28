<?php
class EvaluateAction extends CommonAction {
	public $tab='Evaluate';
	public $setab='Servuser';
	public $metab='Merchant';
	public $tabView='EvaluateView';
	public function __construct() {
		parent::__construct();
		R('Actlog/recorde');	//日志
	}
    public function index(){
		$Evaluate=D($this->tabView);
		import('ORG.Util.Pages2');
		if(cookie('ADMIN_CATES')){
			$map_cates['id'] = array('in',cookie('ADMIN_CATES'));
		}
		if($this->_post('ser')){
			$where['Servuser.name']=array('like','%'.I('ser').'%');
			$s['ser']=$this->_post('ser');
		}
		if($this->_post('mer')){
			$where['Merchant.name']=array('like','%'.I('mer').'%');
			$s['mer']=$this->_post('mer');
		}
		if($this->_post('mobile')){
			$where['Servuser.mobile']=array('like','%'.I('mobile').'%');
			$s['mobile']=$this->_post('mobile');
		}
		if($this->_post('addtime')){
			$where['Evaluate.addtime']=array('eq',strtotime(I('addtime')));
			$s['addtime']=$this->_post('addtime');
		}
		if($this->_post('content')){
			$where['Evaluate.content']=array('like','%'.I('content').'%');
			$s['content']=$this->_post('content');
		}
		$this->assign('s',$s);
		if(cookie('ADMIN_POWER')!=1){//非超级用户
			$where['Merchant.area'] = cookie('AREA');
		}
		if(cookie('AREA')){//地区
			$where['Merchant.area'] = array('eq',cookie('AREA'));
		}
		$count = $Evaluate->where($where)->count();
		if(I('stp') == 'all'){	//显示全部
			$p_num = $count;
		}else{
			$p_num = 20;
		}
		$Page = new Pages2($count,$p_num);
		if(intval(I('post.page'))) $Page->nowPage=abs(intval(I('post.page')))>$Page->totalPages?$Page->totalPages:abs(intval(I('post.page')));
		
		$orderby='sort desc,addtime desc,id desc';
		$list = $Evaluate->order($orderby)->where($where)->page($Page->nowPage.','.$Page->listRows)->select();
		//echo $Evaluate->getLastSql();
		$PageConfig=array('prev'=>'<i class="fa fa-chevron-left"></i>','next'=>'<i class="fa fa-chevron-right"></i>','theme'=>'%prePage% %linkPage% %nextPage%');
		$Page->config=$PageConfig;
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('totalRows',$Page->totalRows);
		$this->assign('totalPages',$Page->totalPages);
		$this->assign('nowPage',$Page->nowPage);
		$this->assign('list',$list);
		$this->assign('search',I(''));
		//获取用户信息
		$ser_arr = M($this->setab)->field('id,name')->select();
		$this->assign('serarr',$ser_arr);
		//获取商户信息
		$mer_arr = M($this->metab)->field('id,name')->select();
		$this->assign('merarr',$mer_arr);
		$this->assign('area',$this->getData('Area',$field='ar_id,ar_name',$order=array('ar_ename'=>'asc')));
		$this->display();
	}
	
 /**
  * 查看评论
  */
	public function look(){
		$Evaluate=D($this->tabView);
		$data=$Evaluate->where('Evaluate.id='.$this->_param('id'))->find();
		$this->assign('data',$data);
		$this->display();		
	}

	public function add(){
		$Evaluate = D($this->tab);
		if($this->isPost()){
			$_POST['addtime'] = strtotime(date('Y-m-d'));
			if($Evaluate->create()){
				$lastid=$Evaluate->add();
				 //echo $Evaluate->getLastSql();
				// exit;
				if($lastid){
					$this->success('评论添加成功',U('Evaluate/index'));
				}else{
					$this->error('评论添加失败');
				}
			}else{
				$this->error($Evaluate->getError());
			}
		}else{
			//获取用户信息
			$ser_arr = M($this->setab)->field('id,name')->select();
			$this->assign('serarr',$ser_arr);
			//获取商户信息
			$mer_arr = M($this->metab)->field('id,name')->select();
			$this->assign('merarr',$mer_arr);
			$this->display();
		}
	}
	//查看图片评论
	public function imgLook(){
		$img = str_replace('-','/',I('id'));
		$this->assign('img',$img);	
		$this->display();
	}
	//删除评论
	public function delete(){
		$Evaluate=D($this->tab);
		if($this->isGet()){
			$map['id']=array('eq',$this->_param('id'));
			//删除附件
			$art=M('Evaluate')->where($map)->field('img,content')->find();
			/*$pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";//获取图片的src*/
			/*$pattern='/<a .*?href="(.*?)".*?>/is'; //超链接*/
			/*$pattern="/src=[\'\"]?([^\'\"]*)[\'\"]?/i";//获取所有的src*/			
			if($art['img']){//单图
				$this->delFile2($art['img']);
			}if($art['content']){//正则匹配删除内容中的附件
				preg_match_all("/src=[\'\"]?([^\'\"]*)[\'\"]?/i",$art['content'],$match);//获取所有的src
				foreach($match['1'] as $sk=>$sv){
					$this->delFile2($sv);
				}
				preg_match_all('/<a .*?href="(.*?)".*?>/is',$art['content'],$match);////超链接
				foreach($match['1'] as $hk=>$hv){
					$this->delFile2($hv);
				}
			}
			$aa=$Evaluate->where($map)->delete();
			if($aa){
				$this->success('删除成功！',U('index'));
			}else{
				$this->error('删除失败！');
			}
		}
	}

	public function form(){
		$Evaluate=D($this->tab);
		if($this->isPost()){
			$map['id']=array('in',$this->_param('check'));
			if($this->_param('Delete')){
				//删除附件
				$art_arr=M('Evaluate')->where($map)->field('img,content')->select();
				foreach ($art_arr as $artk => $artv) {
					if($artv['img']){//单图
						del_attach($artv['img']);//将attachments中的数据删除
						$this->delFile2($artv['img']);
					}if($artv['content']){//正则匹配删除内容中的附件
						preg_match_all("/src=[\'\"]?([^\'\"]*)[\'\"]?/i",$artv['content'],$match);//获取所有的src
						foreach($match['1'] as $sk=>$sv){
							del_attach($sv);//将attachments中的数据删除
							$this->delFile2($sv);
						}
						preg_match_all('/<a .*?href="(.*?)".*?>/is',$artv['content'],$match);////超链接
						foreach($match['1'] as $hk=>$hv){
							del_attach($hv);//将attachments中的数据删除
							$this->delFile2($hv);
						}
					}		
				}		
				$aa=$Evaluate->where($map)->delete();
				if($aa){
					$this->success('删除成功！',U('index',array('p'=>$this->_param('p'))));
				}else{
					$this->error('删除失败！');
				}
			}
		}
	}

/**
 * 获取图片 多图片尺寸
 */
	public function getchicun()
	{
		$str='200*300';
		$data = $str?$str:'未定义';
		$data = $str?$str:'未定义';
		echo json_encode($data);
	}
/**
 * 获取分类附件类型信息
**/
	public function getPicnum()
	{
		$Category=M("Category");
		$tid=$this->_param('tid');
		$cate_data=$Category->field('picnum')->find($tid);
		echo $cate_data['picnum'];
	}	
/**
 * 图片裁剪
**/
	public function modifyFace()
	{
		//if ($_SERVER['REQUEST_METHOD'] == 'POST'){
			//删除会员以前的头像
			if(file_exists($MemberFace)) {
				unlink($MemberFace);
			}
			$MemberFace = $this->sliceBanner();
			echo $MemberFace;
			//此处根据自己的程序代码自行调整		
			//$table = "member"; //数据表名称
			//$MemberUser = "cuteboy"; //会员名字
			//mysql_query("UPDATE ".$table." SET MemberFace='".$MemberFace."' Where MemberUser = '".$MemberUser."'");
			//echo "<script>alert('头像修改成功!');location.href='index.php';<\/script>");
			
			exit;
		//}
	}
/**
 * 初始化图片
 * @param $url 原始图片路径
**/
	private function getImageHander ($url) {
		$pp_file=dirname(__FILE__);
		$pp_file=realpath($pp_file.'/../../..'.$url);
		$size=@getimagesize($pp_file);
		switch($size['mime']){
			case 'image/jpeg': $im = imagecreatefromjpeg($pp_file);break;
			case 'image/gif' : $im = imagecreatefromgif($pp_file);break;
			case 'image/png' : $im = imagecreatefrompng($pp_file);break;
			default: $im=false;
		}
		return $im;
	}
/**
 * 生成裁剪后的图片
 * @param $picpre
**/
	private function sliceBanner($picpre='pp'){
			$x = (int)$_POST['x'];
			$y = (int)$_POST['y'];
			$w = (int)$_POST['w'];
			$h = (int)$_POST['h'];
			$pic = $_POST['src'];
			
			//剪切后小图片的名字
			$str = explode(".",$pic);//图片的格式
			$type = $str[1]; //图片的格式
			$filename = $picpre."_".date("YmdHis").".". $type; //重新生成图片的名字
			$uploadBanner = $pic;
			$sliceBanner = "Uploads/img/".$filename;//剪切后的图片存放的位置
			
			//创建图片
			$src_pic = $this->getImageHander($uploadBanner);
			$dst_pic = imagecreatetruecolor($w, $h);
			imagecopyresampled($dst_pic,$src_pic,0,0,$x,$y,$w,$h,$w,$h);
			imagejpeg($dst_pic, $sliceBanner);
			imagedestroy($src_pic);
			imagedestroy($dst_pic);
			
			//删除已上传未裁切的图片
			if(file_exists($uploadBanner)) {
				unlink($uploadBanner);
			}
			//返回新图片的位置
			return '/'.$sliceBanner;
	}
/**
 * zjp
 * ajax 评论审核
 */
	public function Shenhe(){
		$Evaluate=D($this->tab);
		$is=$Evaluate->where('id='.$this->_param('id'))->getField('isshow');
		if($is==1){
			$isnew=0;
		}else{
			$isnew=1;
		}
		$aa=$Evaluate->where('id='.$this->_param('id'))->setField('isshow',$isnew);
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
}