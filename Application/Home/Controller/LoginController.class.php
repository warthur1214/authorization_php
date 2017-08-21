<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller 
{
    function __construct()
    {
        parent::__construct();
    }
	/**
	*登录页面或者首页
	*/
    public function index()
    {
    	if(session('auth_account_id'))
    	{
        	$this->display('Index/index');
    	}
    	else
    	{
        	$this->display('Index/login');	
    	}
    }
	/**
	*登录数据处理
	*/
    public function loginAjax()
    {
    	$accountDB = D('Account');
    	$account = $accountDB->field('account_id,password')->where(array('is_available' => 1,'account_name' => I('post.account_name')))->find();
    	if(!empty($account))
    	{
            $msg = (I('post.password') == $account['password']) ? '登录成功' : '账号或密码错误';
            $status = (I('post.password') == $account['password']) ? 1 : 0;
            if(I('post.password') == $account['password'])
            {
                session('auth_account_id',$account['account_id']);
                //登录成功更新登录时间
                $accountDB->data(array('last_login_time' => date("Y-m-d H:i:s")))->where(array('account_id' => $account['account_id']))->save();
            }
    	}
    	else
    	{
    		$msg = '账号不存在或被冻结，请联系管理员';
    		$status = 0;
    	}
    	echo json_encode(array('msg' => $msg,'status' => $status));
    	exit;
    }
	/**
	*退出
	*/
    public function loginOut()
    {
    	header("Location:/");
    	session('auth_account_id',null);
        exit(); 
    }
}