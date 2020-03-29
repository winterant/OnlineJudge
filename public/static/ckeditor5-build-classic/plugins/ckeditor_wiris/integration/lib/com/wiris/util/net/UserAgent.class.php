<?php

class com_wiris_util_net_UserAgent {
	public function __construct($request) {
		if(!php_Boot::$skip_constructor) {
		$this->request = $request;
	}}
	public function isIe() {
		if(_hx_index_of($this->request->getHeader("User-Agent"), "Trident", null) !== -1) {
			return true;
		} else {
			return false;
		}
	}
	public $request;
	public function __call($m, $a) {
		if(isset($this->$m) && is_callable($this->$m))
			return call_user_func_array($this->$m, $a);
		else if(isset($this->»dynamics[$m]) && is_callable($this->»dynamics[$m]))
			return call_user_func_array($this->»dynamics[$m], $a);
		else if('toString' == $m)
			return $this->__toString();
		else
			throw new HException('Unable to call «'.$m.'»');
	}
	function __toString() { return 'com.wiris.util.net.UserAgent'; }
}
