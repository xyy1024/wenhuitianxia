<?php
Class CommonModel extends Model
{
	protected $pp_m='';	//M('table')
	public function _initialize()
	{
		$this->pp_m=M($this->getModelName());
	}
	private $icon = array('&nbsp;&nbsp;│', '&nbsp;&nbsp;├ ', '&nbsp;&nbsp;└ ');  //格式化的字符
	
	/**
	 * $upid 上级id
	 * $is 是否显示当前id
	 * $condition  筛选条件 必须是数组
	 * $orderby	排序字符串
	 * $space	默认为空	
	**/
	public function getList( $upid = 0, $space = "",$orderby = 'sort asc,id asc',$condition = NULL,$is=0,$field='*') {
		if($is==0 && $upid!=0){
			$a=$this->pp_m->field($field)->where('id='.$upid)->select();
			foreach($a as $k=>$v){
				$a[$k]['tname']=$v['catname'];
			}
			$all=$this->search_List($upid,$space,$orderby,$condition,$field);
			$all=array_merge($a,$all);
		}else{
			$all=$this->search_List($upid,$space,$orderby,$condition,$field);
		}
		return $all;
	}
	
	/**
	 * $condition 查询条件（数组）
	 * orderby 排序方式
	 * topid 顶级id
	**/
	private function search_List($topid = 0, $space = "",$orderby = 'sort asc,id asc',$condition = NULL,$field='*')
	{
		$childs = $this->getChild($topid,$orderby,$condition,$field);//查询该栏目下面的所有子栏目
		$n = count($childs);
		if($n){//有子栏目
			$m=1;
			for($i=0;$i<$n;$i++){
				if($n==$m){//只有一个子栏目/最后一个栏目
					$pre = $this->icon[2];
				}else{
					$pre = $this->icon[1];
					$pad = $space ? $this->icon[0] : "";
				}
				$childs[$i]['tname']=($space ? $space.$pre : "").$childs[$i]['catname'];
				
				$cat_all[]=$childs[$i];
				$cat_bbb=$this->search_List($childs[$i]['id'], $space.$pad."&nbsp;&nbsp;",$orderby,$condition,$field); //递归下一级分类
				//return $cat_bbb;
				if(count($cat_bbb)){//包含子栏目 合并
					$cat_all=array_merge($cat_all,$cat_bbb);
				}
				
				$m++;
			}
			return $cat_all;
		}else{
			return array();;
		}
		
	}
	/**
	 * 获取$upid的所有子分类
	 * @param $condition 条件
	 * @param $upid 父级id
	 * @param $orderby 排序
	**/
	public function getChild($upid = 0,$orderby = 'sort asc,id asc',$condition = null,$field='*')
	{
		$condition['upid']=array('eq',$upid);
		$childs=$this->pp_m->field($field)->where($condition)->order($orderby)->select();
		return $childs;
	}
	
}

?>