<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// |		 lanfengye <zibin_5257@163.com>
// +----------------------------------------------------------------------
	
class Pages {
	
	// 分页栏每页显示的页数
	protected $rollPage = 10;
	// 页数跳转时要带的参数
	protected $parameter  ;
	// 分页URL地址
	protected $url   =   '';
	// 默认列表每页显示行数
	public $listRows = 20;
	// 起始行数
	protected $firstRow ;
	// 分页总页面数
	public $totalPages  ;
	// 总行数
	public $totalRows  ;
	// 当前页数
	public $nowPage ;
	// 分页的栏的总页数
	protected $coolPages   ;
	protected $nowCoolPage;
	// 分页显示定制
	// public $config  =	array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>'第一页','last'=>'最后一页','theme'=>' %totalRow% %header% %nowPage%/%totalPage% 页 %prePage% %nextPage% %first%  %linkPage%  %nextPage% %end%');
	public $config = array(
		'header'=> '共 %totalRows% 条记录',
		'pages' => '共 %totalPages% 页',
		'first' => '首页',
		'prev'  => '上一页',
		'next'  => '下一页',
		'last'  => '尾页',
		'theme' => '%header% %nowPage% %first% %prePage% %linkPage% %nextPage% %end% %pages%',
		);
	// 默认分页变量名
	protected $varPage;

	/**
	 * 架构函数
	 * @access public
	 * @param array $totalRows  总的记录数
	 * @param array $listRows  每页显示记录数
	 * @param array $parameter  分页跳转的参数
	 */
	public function __construct($totalRows,$listRows='',$parameter='',$url='') {
		$this->totalRows	= $totalRows;
		$this->parameter	= $parameter;
		$this->varPage  = C('VAR_PAGE') ? C('VAR_PAGE') : 'p' ;
		if(!empty($listRows)) {
			$this->listRows = intval($listRows);
		}
		$this->totalPages   = ceil($this->totalRows/$this->listRows);   //总页数
		$this->coolPages	= ceil($this->totalPages/$this->rollPage);
		$this->nowPage  = !empty($_GET[$this->varPage])?intval($_GET[$this->varPage]):1;
		if($this->nowPage<1){   //页数不能小于1
			$this->nowPage  = 1;
		}elseif(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {	//页数不能大于总页数
			$this->nowPage  = $this->totalPages;
		}
		$this->firstRow  = $this->listRows*($this->nowPage-1);
		if(!empty($url)){
			$this->url  = $url;
		} 
	}

	/**
	 * 定制分页链接设置
	 * @param string $name  设置名称
	 * @param string $value 设置值
	 */
	public function setConfig($name,$value) {
		if(isset($this->config[$name])) {
			$this->config[$name]	=   $value;
		}
	}

	/**
	 * 分页显示输出
	 * @access public
	 */
	public function show() {
		if(0 == $this->totalRows) return '';
		$p	=   $this->varPage;
		$nowCoolPage	=   ceil($this->nowPage/$this->rollPage);
		$this->nowCoolPage = $nowCoolPage;

		// 分析分页参数
		if($this->url){
			$depr	 =   C('URL_PATHINFO_DEPR');
			$this->url  =   rtrim(U('/'.$this->url,'',false),$depr).$depr.'__PAGE__';
		}else{
			if($this->parameter && is_string($this->parameter)){
				parse_str($this->parameter,$parameter);
			}elseif(is_array($this->parameter)){
				$parameter  =   $this->parameter;
			}elseif(empty($this->parameter)){
				unset($_GET[C('VAR_URL_PARAMS')]);
				$var =  !empty($_POST)?$_POST:$_GET;
				if(empty($var)) {
					$parameter  =   array();
				}else{
					$parameter  =   $var;
				}
			}
			$parameter[$p]  =   '__PAGE__'; //url中的$p参数值处理为__PAGE__
			$this->url	=   U('',$parameter);
		}

		//上一页
		$upRow   =  $this->nowPage-1;
		/*$pp_upRow=  $upRow > 0 ? $upRow : 1;
		$prePage =  '<a href="'.str_replace('__PAGE__',$pp_upRow,$this->url).'">'.$this->config['prev'].'</a>';*/
		$prePage =  $upRow > 0 ? '<a href="'.str_replace('__PAGE__',$upRow,$this->url).'">'.$this->config['prev'].'</a>' : '';

		//下一页
		$downRow	=  $this->nowPage+1;
		/*$pp_downRow =  $downRow <= $this->totalPages ? $downRow : $this->totalPages;
		$nextPage   =   '<a href="'.str_replace('__PAGE__',$pp_downRow,$this->url).'">'.$this->config['next'].'</a>';*/
		$nextPage   =   $downRow <= $this->totalPages ? '<a href="'.str_replace('__PAGE__',$downRow,$this->url).'">'.$this->config['next'].'</a>' : '';

		/* 计算分页临时变量 */
		$now_cool_page  = $this->rollPage/2;
		$now_cool_page_ceil = ceil($now_cool_page);

		//第一页
		$theFirst = '';
		if($this->totalPages > $this->rollPage && ($this->nowPage - $now_cool_page) >= 1){
			$theFirst = '<a class="first" href="'.str_replace('__PAGE__',1,$this->url).'">' . $this->config['first'] . '</a>';
		}

		//最后一页
		$theEnd = '';
		if($this->totalPages > $this->rollPage && ($this->nowPage + $now_cool_page) < $this->totalPages){
			$theEnd = '<a class="end" href="' .str_replace('__PAGE__',$this->totalPages,$this->url). '">' . $this->config['last'] . '</a>';
		}
		
		//数字连接
		$linkPage = "";
		$linkPage = $this->pageList();
		$pageStr	 =   str_replace(
			array('%header%','%pages%','%totalRows%','%totalPages%','%nowPage%','%prePage%','%nextPage%','%first%','%linkPage%','%end%'),
			array($this->config['header'],$this->config['pages'],$this->totalRows,$this->totalPages,$this->nowPage,$prePage,$nextPage,$theFirst,$linkPage,$theEnd),$this->config['theme']);
		return $pageStr;
	}
	
	private function pageList(){
		if($this->totalPages <= $this->rollPage){	//总页面小于rollPage
			$linkPage = $this->forPage(1,$this->totalPages);
		}elseif($this->nowPage >= ($this->rollPage - 3) && $this->nowPage < ($this->totalPages-2)){	//当前页面大于rollpage-2，但小于总页面-2
			$star_page = $this->nowPage - ($this->rollPage - 7);
			$end_page = $this->nowPage+2;
			$linkPage = "<a href='".str_replace('__PAGE__','1',$this->url)."'>1</a>"; //第1页
			$linkPage .= "<a href='javascript:void(0);'>...</a>";
			$linkPage .= $this->forPage($star_page,$end_page);
			$linkPage .= "<a href='javascript:void(0);'>...</a>";
			$linkPage .= "<a href='".str_replace('__PAGE__',$this->totalPages,$this->url)."'>".$this->totalPages."</a>";
		}elseif($this->nowPage < ($this->rollPage-2)){	//当前页面小于rollpage-2
			$linkPage = $this->forPage(1,$this->rollPage-2);
			$linkPage .= "<a href='javascript:void(0);'>...</a>";
			$linkPage .= "<a href='".str_replace('__PAGE__',$this->totalPages,$this->url)."'>".$this->totalPages."</a>";
		}elseif($this->nowPage >= ($this->totalPages-2)){	//当前页面大于总页面-2
			$star_page = $this->totalPages - ($this->rollPage - 2);
			$linkPage = "<a href='".str_replace('__PAGE__','1',$this->url)."'>1</a>"; //第1页
			$linkPage .= "<a href='javascript:void(0);'>...</a>";
			$linkPage .= $this->forPage($star_page,$this->totalPages);
		}
		return $linkPage;
	}
	private function forPage($start = 1,$end){
		for($page=$start;$page<=$end;$page++){
			if($page<=$this->totalPages){
				if($page!=$this->nowPage){
					$linkPage .= "<a href='".str_replace('__PAGE__',$page,$this->url)."'>".$page."</a>";
				}else{
					$linkPage .= '<a href="javascript:void(0);" class="current">'.$page.'</a>';
				}
			}else{
				break;
			}
		}
		return $linkPage;
	}
}
