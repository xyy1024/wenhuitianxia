<?php
// 本类由系统自动生成，仅供测试用途
class BaseAction extends Action {
	protected function _initialize()
	{
		/*$this->assign('siteurl',__SITE__);
		$this->assign('pcsiteurl',__PCSITE__);*/
		$this->assign('website',C('website'));
	}
/**
 * 获取单个信息
 * @param $model 模型
 * @param $field 字段
 * @param $where 条件
**/
	protected function _getOne($model,$where='',$field='id')
	{
		if($where)
			$model = $model->where($where);
		$data=$model -> getField($field);
		return $data;
	}
/**
 * 获取单页信息
 * @param $model 模型
 * @param $field 字段
 * @param $where 条件
**/
	protected function _getRow($model,$where='',$field='')
	{
		if($field)
			$model = $model->field($field);
		if($where)
			$model = $model->where($where);
		$data=$model -> find();
		return $data;
	}
/**
 * 获取列表信息 分页
 * @param $model 模型
 * @param $field 字段
 * @param $where 条件
 * @param $order 排序
 * @param $listRows 单页信息数
 * theme:%header% %pages% %totalRows% %totalPages% %nowPage% %prePage% %nextPage% %first% %linkPage% %end%
**/
	protected function _getLists($model,$where,$order,$field='id,title,description,addtime',$listRows=10,$pageClass='Pages',$PageConfig=array('header'=>'共%totalRows%条记录','pages'=>'%nowPage%/%totalPages%页　','prev'=>'上一页','next'=>'下一页','first'=>'首页','last'=>'尾页','theme'=>'%header% %pages% %first% %prePage% %linkPage% %nextPage% %end%'))
	{
		$c_model=clone $model;
		$count = $c_model->where($where)->count('*');
		if ($count>0)
		{
			import('ORG.Util.'.$pageClass);
			$Page = new Pages($count,$listRows);
			$nowPage  = isset($_GET['p'])?$_GET['p']:1;
			if($where) $model = $model->where($where);
			if($order) $model = $model->order($order);
			// $list = $model->page($nowPage.','.$Page->listRows)->select();
			$list = $model->page($Page->nowPage.','.$Page->listRows)->select();
			//echo $model->getLastSql();
			if($PageConfig)
				$Page->config=$PageConfig;
			$page = $Page->show();
			return array('list'=>$list,'page'=>$page,'count'=>intval($count),'p'=>$p);
		}
		return null;
	}
/**
 * 获取列表信息 无分页
 * @param $model 模型
 * @param $field 字段
 * @param $where 条件
 * @param $order 排序
 * @param $limit_end
 * @param $limit_start
**/
	protected function _getList($model,$where,$order,$limit_end,$limit_start=0,$field='id,title,description,addtime')
	{
		$c_model=clone $model;
		$count = $c_model->where($where)->count('*');
		if ($count>0)
		{
			if($where) $model = $model->where($where);
			if($order) $model = $model->order($order);
			if($limit_end) $model = $model->limit($limit_start,$limit_end);
			$list = $model->field($field)->select();
			//echo $model->getLastSql();
			return array('list'=>$list,'count'=>intval($count));
		}
		return null;
	}
/**
 * 网站seo
 * @param $model 模型
 * @param $where 条件
**/
	protected function _seo($model,$where,$field='title,keywords,description')
	{
		$seo['title'] =C('title');
		$seo['keywords'] =C('keywords');
		$seo['description'] =C('description');
		
		if($model && $where)
		{
			$data=$model->field($field)->where($where)->find();
			$data=array_values($data);
			if($data[0]) $seo['title']=$data[0];
			if($data[1]) $seo['keywords']=$data[1];
			if($data[2]) $seo['description']=$data[2];
		}
		$this->assign('seo',$seo);
		return $seo;
	}
/**
 * 调出分类的顶部id(非0)
 * @param $id 分类id
 * @param $field 字段
 * @param $table 表名
 * @return int 分类的顶部id
**/
	protected function _topid($id,$field='id,upid',$table='Category')
	{
		$data=M($table)->where('id='.$id)->field($field)->find();
		if($data['upid']==0){
			$topid=$data['id'];
		}else{
			//$topid=reid($data['reid']);
			$topid=$this->_topid($data['upid'],$field,$table);
		}
		return $topid;
	}
/**
 * 调出分类下的所有分类
 * @param $id 分类id
 * @param $field 字段
 * @param $table 表名
 * @param $order 排序
 * @return array
**/
	protected function _allChildArray($id,$field='id,upid,catname,catename,showdate',$table='Category',$order='sort')
	{
		$arrid=M($table)->field($field)->where('upid='.$id)->order($order)->select();
		$arr=array();
		if($arrid){
			foreach($arrid as $k=>$v)
			{
				$arr[]=$v;
				$result=M($table)->field($field)->where('upid='.$v['id'])->order($order)->select();
				if($result) $arr=array_merge($arr,$this->_allChildArray($v['id'],$field,$table,$order));
				unset($result);
			}
		}
		return $arr;
	}
/**
 * 调出分类下的子分类
 * @param $id 分类id
 * @param $field 字段
 * @param $table 表名
 * @param $order 排序
 * @return array
**/
	protected function _childArray($id,$field='id,upid,catname,catename',$table='Category',$order='sort')
	{
		$arrid=M($table)->field($field)->where('upid='.$id)->order($order)->select();
		return $arrid;
	}
/**
 * 调出同父类下的所有子分类
 * @param $id 分类id
 * @param $field 字段
 * @param $table 表名
 * @param $order 排序
 * @return array
**/
	protected function _sameParent($id,$field='id,upid,catname,catename',$table='Category',$order='sort')
	{
		$arrid=M($table)->field($field)->where('id='.$id)->find();
		$a=array();
		if($arrid)	//查看分类信息是否存在
		{
			$arrid2=M($table)->field($field)->order($order)->where('upid='.$arrid['upid'])->select();	//同级分类
			$topdata=M($table)->field($field)->where('id='.$arrid['upid'])->find();	//父级分类
			$a['topname']=$topdata['catname'];	//父级分类名称
			$a['topid']=$topdata['id'];	//父级分类id
			if($arrid2)$a['s_class']=$arrid2;	//同级分类信息
		}
		return $a;
	}
/**
 * 调出子类的父类
 * @param $id 分类id
 * @param $field 字段
 * @param $table 表名
 * @return array
**/
	protected function _getParent($id,$field='id,upid,catname,catename',$table='Category')
	{
		$arrid=M($table)->field('upid')->where('id='.$id)->find();
		if($arrid) $arr=M($table)->field($field)->where('id='.$arrid['upid'])->find();
		return $arr;
	}
/**
 * 获取内容的分类id
 * @param $id 内容id
 * @param int 内容分类id
**/	
	protected function _getTid($id)
	{
		$data=M('Article')->field('tid')->where('id='.$id)->find();
		return $data['tid'];
	}
/**
 * 获取上，下一条信息
 * @param $tp 1下一条，2上一条
 * @param $model 模型
 * @param $order 排序
 * @param $field 字段
 * @param $where 条件
 * @param $id 当前信息id
**/
	public function _next($model,$where,$id,$tp=1,$order='sort asc',$field='id,title')
	{
		$data=$model->where($where)->field($field)->order($order)->select();
		foreach($data as $k => $v)
		{
			if($id == $v['id'])
			{
				if($tp == 1)
					$cont=$data[$k+1];
				elseif($tp ==2)
					$cont=$data[$k-1];
				break;
			}
			
		}
		return $cont;
	}
/**
 * 调出广告
 * @param $cid分类id
 * @param $tp 1多条，0 1条
 * @param $field
 * @param $table 表名
 * @param $order 排序
**/
	protected function _getAdver($cid,$tp=1,$field='id,cid,name,url,logo',$table='Adver',$order='sort')
	{
		$Img=M($table);
		$where['cid']=array('eq',$cid);
		$where['isshow']=array('eq',1);
		if($tp)
		{
			$data=$Img->field($field)->where($where)->order($order)->select();
		}
		else
		{
			$data=$Img->field($field)->where($where)->order($order)->find();
		}
		return $data;
	}
/**
 * 调出友情链接
 * @param $tp 1图文链接，0文字链接
 * @param $field
 * @param $table 表名
 * @param $order 排序
 * @return array
**/
	protected function _getLinks($tp=1,$field='id,name,url,note',$table='Links',$order='sort')
	{
		$Img=M($table);
		$where['isshow']=array('eq',1);
		if($tp == 1) {
			$where['isimg'] = array('eq',1);
			$field.=',logo';
		} else {
			$where['isimg'] = array('eq',0);
		}
		$data=$Img->field($field)->where($where)->order($order)->select();
		echo $Img->getLastSql();
		return $data;
	}
	
/**
 * 调出浮动窗口
 * @param  $type  漂浮类型  0全屏漂浮  1左侧浮动  2右侧浮动
 * @param  $field 所需字段
 * @param  $table 表名
 * @return array
 */
	protected function _getFloat($type=0,$field='id,left,top,img_path,img_wid,img_hei,url',$table='Floating')
	{
		$ftab=M($table);
		$where['type']=array('eq',$type);
		$where['isshow']=array('eq',1);
		$farr=$ftab->field($field)->where($where)->find();//仅获取一条
		return $farr;
	}

/**
 * 导出 Export Excel | 2013.08.23 
 * Author:HongPing <hongping626@qq.com>
 * @param $expTitle     string File name
 * @param $expCellName  array  Column name
 * @param $expTableData array  Table data
**/
	public function exportExcel($expTitle,$expCellName,$expTableData)
	{
		$xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
		$fileName = $_SESSION['loginAccount'].date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
		$cellNum = count($expCellName);
		$dataNum = count($expTableData);
		vendor("PHPExcel.PHPExcel");
		$objPHPExcel = new PHPExcel();
		$cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

		$objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));  
		for($i=0;$i<$cellNum;$i++)
		{
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]); 
		} 
		// Miscellaneous glyphs, UTF-8   
		for($i=0;$i<$dataNum;$i++)
		{
			for($j=0;$j<$cellNum;$j++)
			{
				$objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
			}             
		}  

		header('pragma:public');
		header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
		header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
		$objWriter->save('php://output'); 
		exit;   
	}
     
/**
 * 导入 Import Excel | 2013.08.23
 * Author:HongPing <hongping626@qq.com>
 * @param  $file   upload file $_FILES
 * @return array   array("error","message")    
**/   
	public function importExecl($file)
	{ 
		if(!file_exists($file))
		{ 
			return array("error"=>0,'message'=>'file not found!');
		} 
		Vendor("PHPExcel.PHPExcel.IOFactory"); 
		$objReader = PHPExcel_IOFactory::createReader('Excel5'); 
		try{
			$PHPReader = $objReader->load($file);
		}catch(Exception $e){}
		if(!isset($PHPReader)) return array("error"=>0,'message'=>'read error!');
		$allWorksheets = $PHPReader->getAllSheets();
		$i = 0;
        	foreach($allWorksheets as $objWorksheet)
        	{
			$sheetname=$objWorksheet->getTitle();
			$allRow = $objWorksheet->getHighestRow();//how many rows
			$highestColumn = $objWorksheet->getHighestColumn();//how many columns
			$allColumn = PHPExcel_Cell::columnIndexFromString($highestColumn);
			$array[$i]["Title"] = $sheetname; 
			$array[$i]["Cols"] = $allColumn; 
			$array[$i]["Rows"] = $allRow; 
			$arr = array();
			$isMergeCell = array();
			foreach ($objWorksheet->getMergeCells() as $cells) {//merge cells
				foreach (PHPExcel_Cell::extractAllCellReferencesInRange($cells) as $cellReference) {
					$isMergeCell[$cellReference] = true;
				}
			}
			for($currentRow = 1 ;$currentRow<=$allRow;$currentRow++)
			{ 
				$row = array(); 
				for($currentColumn=0;$currentColumn<$allColumn;$currentColumn++)
				{                
					$cell =$objWorksheet->getCellByColumnAndRow($currentColumn, $currentRow);
					$afCol = PHPExcel_Cell::stringFromColumnIndex($currentColumn+1);
					$bfCol = PHPExcel_Cell::stringFromColumnIndex($currentColumn-1);
					$col = PHPExcel_Cell::stringFromColumnIndex($currentColumn);
					$address = $col.$currentRow;
					$value = $objWorksheet->getCell($address)->getValue();
					if(substr($value,0,1)=='='){
						return array("error"=>0,'message'=>'can not use the formula!');
						exit;
					}
					if($cell->getDataType()==PHPExcel_Cell_DataType::TYPE_NUMERIC){
						$cellstyleformat=$cell->getParent()->getStyle( $cell->getCoordinate() )->getNumberFormat();
						$formatcode=$cellstyleformat->getFormatCode();
						if (preg_match('/^([$[A-Z]*-[0-9A-F]*])*[hmsdy]/i', $formatcode)) {
							$value=gmdate("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($value));
						}else{
							$value=PHPExcel_Style_NumberFormat::toFormattedString($value,$formatcode);
						}                
					}
					if($isMergeCell[$col.$currentRow]&&$isMergeCell[$afCol.$currentRow]&&!empty($value)){
						$temp = $value;
					}elseif($isMergeCell[$col.$currentRow]&&$isMergeCell[$col.($currentRow-1)]&&empty($value)){
						$value=$arr[$currentRow-1][$currentColumn];
					}elseif($isMergeCell[$col.$currentRow]&&$isMergeCell[$bfCol.$currentRow]&&empty($value)){
						$value=$temp;
					}
					$row[$currentColumn] = $value; 
				} 
				$arr[$currentRow] = $row; 
            	} 
            	$array[$i]["Content"] = $arr; 
            	$i++;
		} 
		spl_autoload_register(array('Think','autoload'));//must, resolve ThinkPHP and PHPExcel conflicts
		unset($objWorksheet); 
		unset($PHPReader); 
		unset($PHPExcel); 
		unlink($file); 
		return array("error"=>1,"data"=>$array); 
	}
}