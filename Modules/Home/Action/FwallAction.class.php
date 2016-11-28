<?php
class FwallAction extends Action {
    public function authVerify(){
		$this->ppauth();
	}
	private function ppauth(){
		cookie('ppfirewall','333');
		header("Location:http://".$_SERVER['SERVER_NAME']."/index.php?s=/Admin");
	}
}