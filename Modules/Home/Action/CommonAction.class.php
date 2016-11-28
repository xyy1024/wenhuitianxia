<?php
class CommonAction extends BaseAction {
	public function _initialize() {
		parent::_initialize();
		//$this->navList();
	}
	public function _empty()
	{
		$url='http://'.$_SERVER['SERVER_NAME'];
		$time_back=5;
		$show='没找到您要访问的网页原因可能如下：可能网速过慢，或网络中断；可能页面已删除；这个页面太受欢迎了，太多人访问。';
		// $show='页面不存在，'.$time_back.'秒钟后自动跳转到<a href="'.$url.'">首页</a>......';
		$this->assign('url',$url);
		// $this->assign('time_back',$time_back.'000');
		$this->assign('show',$show);
		$this->display('./Public/error.html');
    }
   	/**
   	 * 获取当前页面 面包屑导航
   	 * @param  int $art_id 文章id
   	 * @param  int $cid 栏目分类id
   	 * @param  string $param 链接附
   	 */
	protected function bread($art_id=0,$cid=0,$param='>>'){	
		if($art_id){	//id:文章id
			$cid=$this->_getOne(M('Article'),array('id'=>array('eq',$art_id)),'tid');	//获取文章的栏目分类id
		}
		$bread=$this->breadNav($cid,$param);	//获取topid，面包屑导航
		// $this->assign('topid',$bread['topid']);	//当前栏目的父栏目，如果没有父栏目则值为当前栏目id值
		// $this->assign('bread',$bread['where']);	//面包屑导航
		return $bread;

	}
	/**
	 * pp
	 * 根据栏目id 获取topid，面包屑导航
	 * (为bread方法提供)
	 * @param  [type] $cid   [description]
	 * @param  string $param [description]
	 * @return array topid,where
	 */
	protected function breadNav($cid,$param=">>"){
		$model=M('Category');
		$map['id']=array('eq',$cid);
		$data=$this->_getRow($model,$map,'id,upid,catname');
		if($data['upid']==0){
			$bread['topid']=$data['id'];
			$bread['where']=' '.$param.' <a href="'.$this->listurl($data['id']).'">'.$data['catname'].'</a>';
		}else{
			$upInfo=$this->breadNav($data['upid'],$param);
			$bread['topid']=$upInfo['topid'];
			$bread['where']=$upInfo['where'].' '.$param.' <a href="'.$this->listurl($data['upid']).'">'.$data['catname'].'</a>';
		}
		return $bread;
	}
/**
 * pp
 * 获取导航信息
 * @param  integer $limit_end 获取导航栏目数
 * @param  boolean $child     是否调取二级分类
 * @return array
 */
	protected function navList($limit_end=7,$child=false){
		$model=M('Category');
		$map['upid']=array('eq',0);
		$map['isshow']=array('eq',1);
		$sort='sort asc';
		$field='id,catname,catename,type';
		$nav=$this->_getList($model,$map,$sort,$limit_end,0,$field);
		$navlist=array();
		foreach($nav['list'] as $k=>$v){
			$navlist[$v['id']]=$v;
			$navlist[$v['id']]['url']=$this->listurl($v['id']);
			if($child){	//调用子类
				$navl=$this->_getList($model,array('upid'=>array('eq',$v['id']),'isshow'=>array('eq',1)),$sort,0,0,$field);
				$navllist=array();
				foreach($navl['list'] as $kk=>$vv){
					$navllist[$vv['id']]=$vv;
					$navllist[$vv['id']]['url']=$this->listurl($vv['id']);
				}
				$navlist[$v['id']]['child']=$navllist;
			}
		}
		 //$this->assign('navlist',$navlist);
		 //dump($navlist);
		return $navlist;
	}	
	/**
	 * pp
	 * 获取栏目的链接地址
	 * 1内容列表、2内容单页、3栏目主页、4列表主页、5单页主页、0外部链接
	 * @param  int    $id        栏目id
	 * @param  string $map_field id或者upid(获取子类的信息，根据子类来定义url type=4)
	 * @param  string $field     字段
	 * @return string            url地址
	 */
	protected function listurl($id,$map_field='id',$field='id,type,module_name,action_name,linkurl,blank'){
		$where[$map_field]=array('eq',$id);
		$nav=M('Category')->field($field)->where($where);
		if($map_field=='upid'){	//获取栏目子分类 type=4
			$nav=$nav->order('sort asc,id asc');
		}
		$nav=$nav->find();
		if($map_field=='upid'){
			$id=$nav['id'];
		}
		if($nav['module_name'] && $nav['action_name']){	//后台已定义 module/action
			$act=$nav['module_name'].'/'.$nav['action_name'];
		}
		switch ($nav['type']){
			case 1:
				if(!$act){
					$act='Index/news';
				}
				$url=U($act,array('cid'=>$id));
				break;  
			case 2:
				if(!$act){
					$act='Index/page';
				}
				$url=U($act,array('cid'=>$id));
				break;
			case 3:
				if(!$act){
					$act='Index/cate';
				}
				$url=U($act,array('cid'=>$id));
				break;
			case 4:
				if(!$act){
					$act='Index/lists';
				}
				$url=U($act,array('cid'=>$id));
				break;
			case 5:
				if($act){
					$url=U($act,array('cid'=>$id));
				}else{
					$url=$this->listurl($id,'upid',$field);
				}
				break;
			case 0:
				$url=$nav['linkurl']?$nav['linkurl']:'javascript:void(0);';
				if($nav['blank'] == 1) $url.='" target="_blank';
				break;
			default:
				$url=U('/');
		}
		return $url;
	}
	/**
	 * pp
	 * 分类生成缓存 S
	 * 调出分类下的所有分类(生成url)
	 * 所有分类处于同一等级
	 * @param $id 分类id
	 * @param $field 字段
	 * @param $table 表名
	 * @param $order 排序
	 * @return array
	**/
	protected function AllChildArray($id=0,$field='id,upid,catname,catename,showdate',$table='Category',$order='sort')
	{
		if($arr = S('allCategory')){
			return $arr;
		}
		$arr=$this->getAllChildArray($id,$field,$table,$order);
		S('allCategory',$arr,7200);	//保2个小时
		return $arr;
	}
	/**
	 * pp 为AllChildArray 提供方法
	 * 调出分类下的所有分类(生成url)
	 * 所有分类处于同一等级
	 * @param $id 分类id
	 * @param $field 字段
	 * @param $table 表名
	 * @param $order 排序
	 * @return array
	**/
	protected function getAllChildArray($id=0,$field='id,upid,catname,catename,showdate',$table='Category',$order='sort'){
		$arrid=M($table)->field($field)->where('upid='.$id)->order($order)->select();
		$arr=array();
		if($arrid){
			foreach($arrid as $k=>$v)
			{
				$v['url']=$this->listurl($v['id'],'id','id,type,module_name,action_name,linkurl,blank');
				$arr['pp_'.$v['id']]=$v;
				$result=M($table)->field($field)->where('upid='.$v['id'])->order($order)->select();
				if($result) $arr=array_merge($arr,$this->getAllChildArray($v['id'],$field,$table,$order));
				unset($result);
			}
		}
		return $arr;
	}
	/**
	 * pp
	 * 调出分类下的所有分类(生成url)
	 * 分类下的子分类在child中 多维数组
	 * @param $id 分类id
	 * @param $field 字段
	 * @param $table 表名
	 * @param $order 排序
	 * @return array
	**/
	protected function _leftMenu($id=0,$field='id,upid,catname,catename,showdate',$table='Category',$order='sort'){
		$m=M($table);
		$arr=array();
		$r=$m->field($field)->where('id='.$id)->order($order)->find();
		if($r){
			$r['url']=$this->listurl($r['id'],'id','id,type,module_name,action_name,linkurl,blank');
			$arr['pp_'.$r['id']]=$r;
			$child=$m->field($field)->where('upid='.$id)->order($order)->select();	//分类子类
			if($child){
				foreach($child as $k=>$v)
				{
					$result=$m->field($field)->where('upid='.$v['id'])->order($order)->select();
					if($result){
						if(!$arr['pp_'.$r['id']]['child']) $arr['pp_'.$r['id']]['child']=array();
						$arr['pp_'.$r['id']]['child']=array_merge($arr['pp_'.$r['id']]['child'],$this->_leftMenu($v['id'],$field,$table,$order));
					}else{
						$v['url']=$this->listurl($v['id'],'id','id,type,module_name,action_name,linkurl,blank');
						$arr['pp_'.$r['id']]['child']['pp_'.$v['id']]=$v;
					}
				}
			}
		}
		return $arr;
	}
	/**
	 * pp
	 * 获取分类下的所有子类id(包括本分类)
	 * 一维数组或者字符串
	 * @param int $id   本分类id
	 * @param string $type 1数组 0字符串
	 * @param string $tab  表格名称
	 * @param string $upid map
	 */
	protected function AllCatid($id,$type='1',$tab='Category',$upid='upid'){
		$table=M($tab);
		if($type==1){
			$return[]=$id;
		}else{
			$return =$id;
		}
		$map[$upid]=array('eq',$id);
		$lists=$table->where($map)->select();
		foreach($lists as $k=>$v){
			$listarr=$this->AllCatid($v['id'],$type,$tab,$upid);
			if($type==1){
				$return=array_merge($return,$listarr);
			}else{
				$return .=','.$listarr;
			}
		}
		return $return;
	}

	/**
	 * 返回分类下最低层分类的第一个分类
	 * @param $id 分类id
	 * @param $field
	*/
	protected function getFirst($id,$field='id')
	{
		$where['upid']=array('eq',$id);
		$data=M('Category')->field($field)->where($where)->order('sort asc')->find();
		if($data){
			$a=$this->getFirst($data['id'],$field);
		}else{
			$a=$id;
		}
		return $a;
	}
	protected function _getLists($model,$where,$order,$field='id,title,description,addtime',$listRows=10,$pageClass='Pages',$PageConfig=array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>'首页','last'=>'尾页','theme'=>'%first% %upPage% %linkPage% %downPage% %end%'),$url='')
    {
        $c_model=clone $model;
        $count = $c_model->where($where)->count('*');
        // echo $c_model->getLastSql();
        if ($count>0){
            import('ORG.Util.'.$pageClass);
            $Page = new Page($count,$listRows,'',$url);
            $nowPage  = isset($_GET['p'])?$_GET['p']:0;
            if($where) $model = $model->where($where);
            if($order) $model = $model->order($order);
            if($field) $model = $model->field($field);
            // $list = $model->page($nowPage.','.$Page->listRows)->select();
            $list = $model->page($Page->nowPage.','.$Page->listRows)->select();
            // echo $model->getLastSql();
            if($PageConfig)
                $Page->config=$PageConfig;
            $page = $Page->show();
            return array('listdata'=>$list,'page'=>$page,'pagenum'=>$Page->totalPages,'result_count'=>intval($count),'p'=>$p);
        }
        return null;
    }
}