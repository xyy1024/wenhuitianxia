<?php
class ArticleAction extends CommonAction {
	public $tab='Article';
	public $catab='Category';
	public $tabView='ArticleView';
	public function __construct() {
		parent::__construct();
		R('Actlog/recorde');	//日志
	}
    public function index(){
		$Article=M($this->tab);
		import('ORG.Util.Pages2');
		$Category=D($this->catab);
		if(cookie('ADMIN_CATES')){
			$map_cates['id'] = array('in',cookie('ADMIN_CATES'));
		}
		if(I('contentt')=='0')
		{
			$where['title']=array('like','%'.I('title').'%');
		}
		if(I('contentt')!='')
		{
			$this->assign('contentt',I('contentt'));
		}
		if(cookie('AREA')){//地区
			$where['area'] = array('eq',cookie('AREA'));
		}
		$count = $Article->where($where)->count();
		if(I('stp') == 'all'){	//显示全部
			$p_num = $count;
		}else{
			$p_num = 20;
		}
		$Page = new Pages2($count,$p_num);
		if(intval(I('post.page'))) $Page->nowPage=abs(intval(I('post.page')))>$Page->totalPages?$Page->totalPages:abs(intval(I('post.page')));
		// $nowPage  = isset($_GET['p'])?$_GET['p']:0;
		// $orderby['_string']='sort=0,sort';
		// $orderby='Article.sort=0,sort asc,id desc';	//注意排序
		$orderby='dotop desc,sort desc,addtime desc,id desc';
		$list = $Article->order($orderby)->where($where)->page($Page->nowPage.','.$Page->listRows)->select();
		//echo $Article->getLastSql();
		$PageConfig=array('prev'=>'<i class="fa fa-chevron-left"></i>','next'=>'<i class="fa fa-chevron-right"></i>','theme'=>'%prePage% %linkPage% %nextPage%');
		$Page->config=$PageConfig;
		$show = $Page->show();
		$this->assign('catename',C('CATE_NAME'));//特殊模式中栏目名
		$this->assign('page',$show);
		$this->assign('totalRows',$Page->totalRows);
		$this->assign('totalPages',$Page->totalPages);
		$this->assign('nowPage',$Page->nowPage);
		$this->assign('list',$list);
		$this->assign('search',I('title'));
		$this->assign('area',$this->getData('Area',$field='ar_id,ar_name',$order=array('ar_ename'=>'asc')));
		$this->display();
	}
	
 /**
  * 修改文章
  */
	public function edit(){
		$Article=D($this->tab);
		if($this->isPost()){
				//热门 推荐
				$flag='';
				foreach($this->_param('flag') as $k=>$v){
					$flag .= ','.$v;
				}
				$_POST['flag']=substr($flag,1);
				//缩略图处理
				if($this->_param('thumb') != $this->_param('oldthumb'))
				{
					del_attach($this->_param('oldthumb'));//将attachments中的数据和本地文件删除
				}
				//附件
				if($this->_param('file') != $this->_param('oldfile'))
				{
					del_attach($this->_param('oldfile'));//将attachments中的数据和本地文件删除
				}
				//视频
				if($this->_param('vedio') != $this->_param('oldvedio'))
				{
					del_attach($this->_param('oldvedio'));//将attachments中的数据和本地文件删除
				}		
				if($Article->create()){
					$lastid=$Article->where('id='.$this->_param('id'))->save();
					
					if($lastid){
						$this->success('文章修改成功',U('Article/index',array('tid'=>$tid)));
					}else{
						$this->error('文章修改失败');
					}
				}else{
					$this->error($Article->getError());
				}
		}else{
			$data=$Article->where('id='.$this->_param('id'))->find();
			//热门 推荐
			$flag=explode(',',$data['flag']);
			$this->assign('flag',$flag);
			//缩略图尺寸
			$imgsize = json_decode($this->getImgsize($data['tid'],'img_size'),true);
			$this->assign('imgsize',$imgsize);
			$this->assign('data',$data);
			$this->assign('arealist',$this->getArea());
			$this->display();
		}		
	}

	public function add(){
		$Article = D($this->tab);
		if($this->isPost()){
			if($Article->create()){
				$lastid=$Article->add();
				 //echo $Article->getLastSql();
				// exit;
				if($lastid){
					$this->success('文章添加成功',U('Article/index',array('tid'=>$this->_param('tid'))));
				}else{
					$this->error('文章添加失败');
				}
			}else{
				$this->error($Article->getError());
			}
		}else{
			$this->assign('arealist',$this->getArea());
			$maxsort=$Article->max('sort');			
			$this->assign('maxsort',$maxsort+1);
			$this->display();
		}
	}
	//删除文章
	public function delete(){
		$Article=D($this->tab);
		if($this->isGet()){
			$map['id']=array('eq',$this->_param('id'));
			//删除附件
			$art=M('Article')->where($map)->field('thumb,img,file,vedio,content')->find();
			/*$pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/";//获取图片的src*/
			/*$pattern='/<a .*?href="(.*?)".*?>/is'; //超链接*/
			/*$pattern="/src=[\'\"]?([^\'\"]*)[\'\"]?/i";//获取所有的src*/			
			if($art['thumb']){//单图
				$this->delFile2($art['thumb']);
			}if($art['file']){//附件
				$this->delFile2($art['file']);
			}
			if($art['vedio']){//视频
				$this->delFile2($art['vedio']);
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
			$aa=$Article->where($map)->delete();
			if($aa){
				$this->success('删除成功！',U('index',array('tid'=>$this->_param('tid'))));
			}else{
				$this->error('删除失败！');
			}
		}
	}

	public function form(){
		$Article=D($this->tab);
		if($this->isPost()){
			$map['id']=array('in',$this->_param('check'));
			if($this->_param('Delete')){
				//删除附件
				$art_arr=M('Article')->where($map)->field('thumb,img,file,vedio,content')->select();
				foreach ($art_arr as $artk => $artv) {
					if($artv['thumb']){//单图
						del_attach($artv['thumb']);//将attachments中的数据删除
						$this->delFile2($artv['thumb']);
					}if($artv['file']){//附件
						del_attach($artv['file']);//将attachments中的数据删除
						$this->delFile2($artv['file']);
					}
					if($artv['vedio']){//视频
						del_attach($artv['vedio']);//将attachments中的数据删除
						$this->delFile2($artv['vedio']);
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
				$aa=$Article->where($map)->delete();
				if($aa){
					$this->success('删除成功！',U('index',array('p'=>$this->_param('p'))));
				}else{
					$this->error('删除失败！');
				}
			}
		}
	}

/**
 * 返回图片尺寸
 * @param  [string] $str [图片尺寸]
 * @param string $field 单图或者多图字段 img_size  imgs_size
 * @return [array or null]
 */
	private function getImgsize($tid,$field='img_size'){
		//$str=M($this->catab)->where('id='.I('tid'))->getField($field);
		$str='200*300';
		if($str && strpos($str,'*') !== false){
			$arr = explode('*',$str);
			return json_encode($arr);
		}else{
			return null;
		}
	}
/**
 * 获取图片 多图片尺寸
 */
	public function getchicun()
	{
		//$data=M($this->catab)->field('img_size,imgs_size')->where('id='.I('tid'))->find();
		$str='200*300';
		$data['img_size'] = $str?$str:'未定义';
		$data['imgs_size'] = $str?$str:'未定义';
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
			$sliceBanner = "Uploads/Thumb/".$filename;//剪切后的图片存放的位置
			
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
 * ajax 文章审核
 */
	public function Shenhe(){
		$Article=D($this->tab);
		$is=$Article->where('id='.$this->_param('id'))->getField('isshow');
		if($is==1){
			$isnew=0;
		}else{
			$isnew=1;
		}
		$aa=$Article->where('id='.$this->_param('id'))->setField('isshow',$isnew);
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