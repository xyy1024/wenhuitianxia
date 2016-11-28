<?php
/*
*友情链接分类名称
*/
function LinkCatName($id){
	$data=M('Linkscat')->where('id='.$id)->find();
	return $data['name'];
}
function AdminNum($role){
	$count=M('Admin')->where('role='.$role)->count();
	return $count;
}
function GetTableName($id,$name='catname',$tab='Goodscat'){
	$table=M($tab);
	$data=$table->where('id='.$id)->find();
	return $data[$name];
}
function isflag($arr){
	$return='';
	if($arr['thumb'])$return .='<span style="color:red">&nbsp;[图]&nbsp;</span>';
	if($arr['flag']){
		$flag=String2Array($arr['flag']);
		if(in_array('h',$flag)) $return .='<span style="color:red">&nbsp;[热门]&nbsp;</span>';
		if(in_array('c',$flag)) $return .='<span style="color:red">&nbsp;[推荐]&nbsp;</span>';
	}
	return $return;
}
/*
1、内容列表、2	内容单页、3	栏目主页、4	列表主页、5	单页主页、0	外部链接
*/
function catType($id){
	switch ($id){
		case 1:
			$name='内容列表';break;  
		case 2:
			$name='内容单页';break;
		case 3:
			$name='栏目主页';break;
		case 4:
			$name='列表主页';break;
		case 5:
			$name='单页主页';break;
		case 0:
			$name='外部链接';break;
		default:
			$name='';
	}
	return '<span style="color: #ccc;">'.$name.'</span>';
}

/**
 * 左侧栏目导航
 * @$v 列表数据
 * @$first ul class 值变化（true false）
**/
	function leftmenu($v,$first=false)
	{
		if($v)
		{
			echo '<ul class="nav';
			if($first)	//true 一级分类 ul class为lt
				echo ' lt';
			else
				echo ' bg';
			echo '">';
			foreach($v as $k => $v)
			{
				echo '<li > ';
				if($v['cmenulist'])	//含有子分类
					echo '<a href="#form">';
				else
					echo '<a href="'.$v['url'].'" target="if">';
				if($v['cmenulist']){	//含有子分类
					echo ' <i class="fa fa-angle-down text"></i> <i class="fa fa-angle-up text-active"></i> ';
				}else{	//没有子分类
					echo ' <i class="fa fa-angle-right"></i> ';
				}
				echo '<span>'.$v['catname'].'</span> </a>';
				// 如果有子类
				if($v['cmenulist'])
				{
					leftmenu($v['cmenulist'],false);
				}
				echo '</li>';
			}
			echo '</ul>';

		}
	}
/**
 * 删除数据中键名为id的值(array_filter)
 * @param  [type] $arr   [description]
 * @param  string $param [description]
 * @return boolean
 */
	/*function clearId($arr,$param='id'){
		if(key($arr) == $param){
			return false;
		}else{
			return true;
		}
	}*/
?>