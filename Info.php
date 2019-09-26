<?php
namespace app\appserver\controller;

/**
 * 个人信息
 */
class Info extends BaseController
{
	
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * 上传图片
	 */
	public function uploadphoto(){
		if(Request()->isPOST())
        {
        	$pic = input("pic");
        	if (empty($pic)) {
        		$this->output("100004","请上传图片");
        	}

        	$img = $this->base64_image_content($pic);
        	if (!$img) {
        		$this->output("100004","请上传图片");
        	}
        	$siteurl = Request()->root(true);

        	$this->output("100006",$siteurl."/".$img);
        }
	}

	/**
	 * [将Base64图片转换为本地图片并保存]
	 * @E-mial wuliqiang_aa@163.com
	 * @TIME   2017-04-07
	 * @WEB    http://blog.iinu.com.cn
	 * @param  [Base64] $base64_image_content [要保存的Base64]
	 * @param  [目录] $path [要保存的路径]
	 */
	function base64_image_content($base64_image_content){
	    //匹配出图片的格式
	    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
	        $type = $result[2];
	        $new_file = ROOT_PATH . 'public' . DS . 'uploads' . DS .date('Ymd',time())."/";
	        if(!file_exists($new_file)){
	            //检查是否有该文件夹，如果没有就创建，并给予最高权限
	            mkdir($new_file, 0700);
	        }
	        $filename = time().".{$type}";
	        if (file_put_contents($new_file.$filename, base64_decode(str_replace($result[1], '', $base64_image_content)))){
	            return 'public' . DS . 'uploads' . DS .date('Ymd',time()).'/'.$filename;
	        }else{
	            return false;
	        }
	    }else{
	        return false;
	    }
	}

	/**
	 * 获取用户认证信息
	 * @return [type] [description]
	 */
	public function getAuth(){
		$info_model = model("info");
		$info_data = $info_model->getInfo($this->user["id"]);
		$result_data = [];
		if (!$info_data) {
			$this->output("100006",$result_data);
		}

		$result_data = [
			"is_real_name_auth"=>$info_data["is_real_name_auth"],
			"is_operator_auth"=>$info_data["is_operator_auth"],
			"is_idcard_auth"=>$info_data["is_idcard_auth"],
			"is_bank_auth"=>$info_data["is_bank_auth"],
			"is_taobao_auth"=>$info_data["is_taobao_auth"],
			"is_alipay_auth"=>$info_data["is_alipay_auth"]
		];
		$this->output("100006",$result_data);
	}
}