<?php
Class EmptyAction extends Action
{
	public $url='';
	public $time_back='';
	public $show='';
	public function _initialize(){
		$this->url='http://'.$_SERVER['SERVER_NAME'];
		$this->time_back=5;
		$this->show='页面不存在，'.$this->time_back.'秒钟后自动跳转到<a href="'.$this->url.'">首页</a>......';
	}
	public function index()
	{
		$this->pp_assign();
		$this->display('./Public/error.html');
	}
	
	public function _empty($name)
	{
		$this->pp_assign();
		$this->display('./Public/error.html');
        }
	
	public function pp_assign()
	{
		$this->assign('url',$this->url);
		$this->assign('time_back',$this->time_back.'000');
		$this->assign('show',$this->show);
	}
}
?>