<?php

namespace app\appserver\controller;

use think\Controller;
use think\Request;
use app\common\model\Userlogin;

/**
 * 父控制器
 */
class BaseController extends Controller
{
	protected $user=array();
	protected $token;


	function __construct()
	{
		parent::__construct();
		$request = Request::instance();
		//获取token信息
        $this->token = $request->header("token");
        $this->getUser();

		$controller_name = $request->Controller();
        $allow_controller = ['index','callback'];
        if (!in_array(strtolower($controller_name), $allow_controller) && empty($this->user)) {
            $this->output("100404","你还未登录");
        }
	}

	/**
	 * 获取用户登录信息
	 * @return [type] [description]
	 */
	public function getUser(){
		if (!empty($this->token)) {
			$userlogin_model = model("userlogin");
			$r = $userlogin_model->getUser($this->token);
			if ($r) {
				$this->user = $r;
			}
		}
	}

	/**
	 * 输出
	 * @param  [type] $status [description]
	 * @param  [type] $mes    [description]
	 * @return [type]         [description]
	 */
	public function output($status,$mes){
		$result = json_encode(["code"=>$status,"result"=>$mes]);
        echo $result;
        exit();
	}

	/*
		生成加密密码
	 */
	//生成加密密码
    protected function makePass($str = '')
    {
        if (!$str)
            return '';
        $pass = sha1(md5($str));
        return $pass;
    }
}