<?php

namespace app\appserver\controller;

/**
 * index控制器
 */
class Index extends BaseController
{
	
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * 登录
	 */
	public function login(){
		if(Request()->isPOST())
        {
            $mobile = input('mobile');
            $yzm = input('yzm');
            if(!checkMobile($mobile))
            {
                $this->output("100004",'请输入正确的手机号');
            }

            $user_model = model("User");
            $r = $user_model->LoginByMobile($mobile);
            if(!$r)
            {
                $this->output("100004",'手机号输入有误');
            }
            $token = create_uuid_token();
        	$userlogin = model("userlogin");
        	$ulr = $userlogin->addUserLogin($token,$r['id']);
        	if (!$ulr) {
        		$this->output("100007",'手机号输入有误');
        	}
        	$this->output("100006",$token);
        }
        $this->output("100004",'手机号输入有误');
	}

	/**
	 * 保存注册
	 */
	public function reg(){
		if(Request()->isPOST())
        {

            $mobile = input("mobile");
            $code   = input("code");
            $passwd = input("passwd");
			$decade = config('loan');
			
            if(!checkMobile($mobile))
            {
                $this->output("100004","请输入正确的手机号");
            }
            if( strlen($passwd) < 6 || strlen($passwd) > 18)
            {
                $this->output("100004",'密码长度必须大于6位且小于18位');
            }
            $smscode_model = model("Smscode");
            $s = $smscode_model->checkCode($mobile,$code);
            if(!$s)
            {
                $this->output("100004","短信验证码输入有误");
            }
            
            $user_model = model("User");
            //判断用户是否存在
            if($user_model->ifExist($mobile))
            {
                $this->output("100007",'此手机号已注册');
            }else{
                $r = $user_model->addUser($mobile,$this->makePass($passwd));
            }
            if(!$r)
            {
                $this->output("100004",'注册失败');
            }

            $r = $user_model->Login($mobile,$this->makePass($passwd));
            if($r) {
            	$token = create_uuid_token();
            	$userlogin = model("userlogin");
            	$ulr = $userlogin->addUserLogin($token,$r['id']);
            	if (!$ulr) {
            		$this->output("100007",'重新登录');
            	}
            	$this->output("100006",$token);
            }
        }
        $this->output("100004",'注册失败');
	}

	/**
	 * 发送注册验证码
	 */
	public function sendcode(){
		$mobile = input("mobile");
        $type    = input("type");
        if(!checkMobile($mobile))
        {
            $this->output("100004","请输入正确的手机号");
        }
       
        $smscode_model = model("Smscode");
        //检查是否频繁
        if(!$smscode_model->checkNum($mobile))
        {
            $this->output("100004",'验证码发送过于频繁,请稍候');
        }
        //检查今日是否频繁
        if(!$smscode_model->checkTodaynum($mobile))
        {
            $this->output("100004",'今日短信发送已达限额,请明天再试');
        }
        $code = $smscode_model->makeCode($mobile,$type,4);
        if(!$code['status'])
        {
            $this->output("100004",'短信验证码发送失败');
        }
        $api = config('api');
        switch ($type) {
            case '找回密码':
                $tplid = $api['findtpl'];
                break;
            default:
                $tplid = $api['regtpl'];
                break;
        }
        //发送短信验证码
        $status = $smscode_model->sendSms($mobile,$code['code'],$tplid,$code['status']);
        if(!$status)
        {
            $this->output("100004",'短信发送失败');
        }
        $this->output("100006","短信发送成功");
	}

	/**
	 * 获取用户配置信息
	 */
	public function getUserSetMsg(){
		$loan = config("loan");

		$data = [
			'time'=>'7',
			'time_n'=>'day',
			'rate'=>'0.25',
			'money'=>$loan["money"][(count($loan["money"])-1)],
            "status"=>0
		];
		if (!empty($this->user)) {
			// 已登录
            if ($this->user["status"] == 3){
                $data["money"] = $this->user["money"];
            }elseif ($this->user["status"] == 4){
                //查询数据
                $loan = model("loan");
                $l_data = $loan->getOrder($this->user["id"]);
                if (!$l_data){
                    $this->output("100004","系统错误");
                }
                $result_data =[
                    "borrowmoney"=>$l_data["borrowmoney"],
                    "overdue"=>$l_data["overdue"],
                    "lasttime"=>$l_data["lasttime"],
                    "odrstatus"=>$l_data["status"],
                    "status"=>4
                ];

                $this->output("100006",$result_data);
            }else{
                $data["money"] = $loan["money"][$this->user["vip"]];
            }

            $data["status"] = $this->user["status"];
		}
		$this->output("100006",$data);
	}
}