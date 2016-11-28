<?php
class FileAction extends CommonAction {
	public function __construct() {
		parent::__construct();
		R('Actlog/recorde');	//日志
	}
	public function index(){
		$m = D('FileView');
		// $where['Article.id'] = array('neq','');
		$arr = $m->where($where)->select();
		echo $m->getlastsql();
		dump($arr);
		exit;
		$this->display();
	}
	
//文件管理列表
	public function fileList(){
		set_time_limit(0);
		//ppStartTime();
		$attach=M('Attachments');
		import('ORG.Util.Pages2');
		$count = $attach->count();
		if(I('stp') == 'all'){
			$num=$count;
		}else{
			$num=50;
		}
		$Page = new Pages2($count,$num);
		if(intval(I('post.page'))) $Page->nowPage=abs(intval(I('post.page')))>$Page->totalPages?$Page->totalPages:abs(intval(I('post.page')));
		$orderby['addtime']='desc';
		$list = $attach->order($orderby)->page($Page->nowPage.','.$Page->listRows)->select();
		$type_arr = array(
			'1'=>'扩展模块->焦点图管理',
			'2'=>'扩展模块->友情链接',
			'3'=>'栏目设置',
			'4'=>'扩展模块->浮动窗口',
			'5'=>'教师管理->教师简历管理',
			'6'=>'扩展模块->留言列表',
			'7'=>'微信管理->图文管理'
		);
		$row=array();
		foreach($list as $v){
			$where1['thumb'] = array('eq',$v['file']);
			$where1['file'] = array('eq',$v['file']);
			$where1['vedio'] = array('eq',$v['file']);
			$where1['img'] = array('LIKE','%'.$v['file'].'%');//多图
			$where1['content'] = array('LIKE','%'.$v['file'].'%');
			//$where1['_string'] = 'MATCH (Article.`content`) AGAINST ("'.$v['file'].'" IN BOOLEAN MODE)';
			$where1['_logic'] = 'OR';	
			$arr1 = D('FileView')->where($where1)->select();

			$where2['logo'] = array('eq',$v['file']);
			$where2['content'] = array('LIKE','%'.$v['file'].'%');
			//$where2['_string'] = 'MATCH (Adver.`content`) AGAINST ("'.$v['file'].'")';
			$where2['_logic'] = 'OR';
			$arr2 = M('Adver')->where($where2)->field('id,name as title')->select();

			$where3['logo'] = array('eq',$v['file']);
			$where3['banner'] = array('eq',$v['file']);	//Category
			$where3['_logic'] = 'OR';
			$arr3 = M('Links')->where(array('logo'=>array('eq',$v['file'])))->field('id,name as title')->select();
			
			$where4=array_merge($where1,$where3);
			$arr4 = M('Category')->where($where4)->field('id,catname as title')->select();

			$where5['img_path'] = array('eq',$v['file']);	//Floating
			$arr5 = M('Floating')->where($where5)->field('id')->select();

			$where6['Img'] = array('eq',$v['file']);	//Teacher
			$arr6 = M('Teacher')->where($where6)->field('id,name as title')->select();

			$where8['pic'] = array('eq',$v['file']); //wx_img
			$where8['content'] = array('LIKE','%'.$v['file'].'%');
			//$where8['_string'] = 'MATCH (Wx_img.`content`) AGAINST ("'.$v['file'].'")';//索引查询
			$where8['_logic'] = 'OR';
			$arr8 = M('Wx_img')->where($where8)->field('id,title')->select();

			if($arr1 || $arr2 || $arr3 || $arr4 || $arr5 || $arr6 || $arr8){
				if($arr1){
					foreach($arr1 as $k1=>$v1){
						$row[$v['file']][] = '【文章管理->'.$v1['catname'].'】'.$v1['title'];
					}
				}if($arr2){
					foreach($arr2 as $k2=>$v2){
						$row[$v['file']][] = '【'.$type_arr[1].'】'.$v2['title'];
					}
				}if($arr3){
					foreach($arr3 as $k3=>$v3){
						$row[$v['file']][] = '【'.$type_arr[2].'】'.$v3['title'];
					}
				}if($arr4){
					foreach($arr4 as $k4=>$v4){
						$row[$v['file']][] = '【'.$type_arr[3].'】'.$v4['title'];
					}
				}if($arr5){
					foreach($arr5 as $k5=>$v5){
						$row[$v['file']][] = '【'.$type_arr[4].'】';
					}
				}if($arr6){
					foreach($arr6 as $k6=>$v6){
						$row[$v['file']][] = '【'.$type_arr[5].'】'.$v6['title'];
					}
				}if($arr8){
					foreach($arr8 as $k8=>$v8){
						$row[$v['file']][] = '【'.$type_arr[7].'】'.$v8['title'];
					}
				}
			}else{
				$row[$v['file']][] = ''; //未匹配到的附件绝对路径
			}
		}
		//ppEndTime();
		$this->assign('list',$row);
		$PageConfig=array('prev'=>'<i class="fa fa-chevron-left"></i>','next'=>'<i class="fa fa-chevron-right"></i>','theme'=>'%prePage% %linkPage% %nextPage%');
		$Page->config=$PageConfig;
		$show=$Page->show();
		$this->assign('page',$show);
		$this->assign('search',I('title'));
		$this->assign('totalRows',$Page->totalRows);
		$this->assign('totalPages',$Page->totalPages);
		$this->assign('nowPage',$Page->nowPage);
		//$this->assign('list',$list);
		$this->display();

	}
//异步删除单个附件
	public function file_ajax(){
		$id = iconv('utf-8', 'gbk', I('url'));
		if($id){
			del_attach($id);//将attachments中的数据删除
			$this->delFile2($id);//删除附件
			$data=true;
		}else{
			$data=false;
		}
		echo json_encode($data);
	}
//删除勾选附件
	public function filedel(){
		set_time_limit(0);
		$d_result=true;
		$Article=D($this->tab);
		if($this->isPost()){
			$arr=$this->_param('check');
			foreach($arr as $v){
				$v = iconv('utf-8', 'gbk', $v);
				if($v){
					del_attach($v);//将attachments中的数据删除
					$r = $this->delFile2($v);//删除附件
					if(!$r){
						$d_result = false;
					}
				}
			}
			if($d_result){
				$this->success('文件删除成功！',U('fileList'));
			}else{
				$this->success('部分文件删除失败，请确认！',U('fileList'));
			}
		}
	}


/** 删除所有空目录 
* @param String $path 目录路径 
*/ 
	public function rm_empty_dir($path){ 
		if(is_dir($path) && ($handle = opendir($path))!==false){ 
			while(($file=readdir($handle))!==false){// 遍历文件夹 
				if($file!='.' && $file!='..'){ 
					$curfile = $path.'/'.$file;// 当前目录 
					if(is_dir($curfile)){// 目录 
						$this->rm_empty_dir($curfile);// 如果是目录则继续遍历 递归
						if(count(scandir($curfile))==2){//目录为空,=2是因为.和..存在
							rmdir($curfile);// 删除空目录 
						} 
					} 
				} 
			} 
			closedir($handle); 
		} 
	} 
	protected function ppSphinx($keyword){
		ini_set('display_errors','on');
		error_reporting(E_ALL);
		set_time_limit(0);
		//第一步：
		require_once "E:/coreseek-4.1/api/sphinxapi.php";//我的coreseek安装在E:/coreseek目录下，你需要根据你的coreseek的位置进行调整
		//第二步：
		$sphinx = new SphinxClient();
		$sphinx->SetServer ( 'localhost', 9312 );//coreseek的主机名和端口
		$sphinx->SetArrayResult ( true );//设置返回结果集为php数组格式
		$sphinx->SetLimits(0, 100, 1000);//匹配结果的偏移量，参数的意义依次为：起始位置，返回结果条数，最大匹配条数
		$sphinx->SetMaxQueryTime(10);//最大搜索时间
		// $sphinx->SetSortMode("SPH_SORT_ATTR_ASC", 'updatetime');
		// $sphinx->SetSortMode("SPH_SORT_EXTENDED", '@id desc');
		//第三步：
		$source = "article";
		$result = $sphinx->Query ($keyword, $source);//xxxx是查询的内容，mysql是测试的配置文件中系统默认的类名
		//结果：
		/*echo '<pre>';
		// print_r($sphinx);//显示所有SphinxClient()类的内容，如果发生查询错误，也可以在这里找到。
		var_dump($result);//显示查询结果信息
		// var_dump($result['matches']);
		echo '</pre>';*/
		return (int)$result['total'];
	}
}