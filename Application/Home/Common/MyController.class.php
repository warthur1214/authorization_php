<?php
namespace Home\Common;
use Think\Controller;
class MyController extends Controller 
{
    public $organDB;//企业model
    public $accountDB;//帐号model
    public $accountRoleDB;//帐号角色关系model
    public $moduleDB; //模块model
    public $roleDB;  //角色model
    public $roleModuleDB;  //角色模块关系model
    public $roleOrganDB;  //角色企业关系model
    public $login_role;  //当前登录帐号所属角色
    public $organ_id;  //当前登录帐号所属企业
	function __construct()
	{
		parent::__construct();
		$this->checkLogin();
        $this->organDB = D('Organ');
        $this->accountDB = D('Account');
        $this->accountRoleDB = D('AccountRole');
        $this->moduleDB = D('Module');
        $this->roleDB = D('Role');
        $this->roleModuleDB = D('RoleModule');
        $this->roleOrganDB = D('RoleOrganRel');
        //根据当前登录帐号获取帐号角色
        $loginRole = $this->accountRoleDB->field('role_id')->where(array('account_id' => session('auth_account_id')))->select();
        foreach ($loginRole as $key => $val) 
        {
            $login_role_id[] = $val['role_id'];
        }
        $this->login_role = implode(',',$login_role_id);
	}
    /**
     * 验证登陆状态
     */
    private function checkLogin()
    {
        //验证是否已经登陆
        if(!session('auth_account_id'))
        {
            echo "<script>window.top.location.href='/';</script>"; 
            exit;       
        }
	}	
    /**
    *信息数组赋值null为空
    */
    public function filterNull($v) 
    {
        if (is_null($v)) 
        {
            return '';
        }
        return $v;
    }
    /**
    *企业信息无限极递归函数
    */
    public function getList($pid = '0',$field,$one = 'organ.parent_organ_id',$otherWhere = '')
    {
        $where = "1=1 and {$one} = '{$pid}' and organ.is_available = '1'".$otherWhere;
        $list = $this->organDB->organList($where,$field);
        
        if($list)
        {
            foreach ($list as $key => $val) 
            {
                if ($val['organ_id']) 
                {
                    $val['son'] = $this->getList($val['organ_id'],$field,$one,$otherWhere);
                }
                $array[] = $val;
            }
        }
        return $array;
    }
    /**
    *根据帐号获取所属机构信息
    */
    public function belong_organ()
    {
        $data = $this->accountDB->field('belonged_organ_id')->where(array('account_id' => session('auth_account_id')))->find();
        return $data['belonged_organ_id'];
    }
    /**
    *根据帐号所属机构获取上级机构信息
    */
    public function belong_organ_parent()
    {
        $data = $this->organDB->field('parent_organ_id')->where(array('organ_id' => $this->belong_organ()))->find();
        return $data['parent_organ_id'];
    }
    /**
    *根据帐号角色获取机构信息
    */
    public function login_role_organ()
    {
        $where['role_id'] = array('in',$this->login_role);
        $data = $this->roleOrganDB->where($where)->select();
        foreach ($data as $key => $val) 
        {
            $role_organ[] = $val['organ_id'];
        }
        $organ = implode(',',array_filter(array_unique($role_organ)));
        return $organ;
    }
    /**
    *重组帐号角色管理机构信息
    */
    public function new_login_role_organ()
    {
        $where['organ_id'] = array('in',$this->login_role_organ());
        $data = $this->organDB->field('organ_id,channel_id')->where($where)->select();
        $info = array();
        foreach ($data as $key => $val) 
        {
            $info[$val['channel_id']][] = $val['organ_id'];
        }
        return $info;
    }
    /**
    *根据帐号角色获取模块信息
    */
    public function login_role_module()
    {
        $where['role_id'] = array('in',$this->login_role);
        $data = $this->roleModuleDB->where($where)->select();
        foreach ($data as $key => $val) 
        {
            $role_module[] = $val['module_id'];
        }
        $module = implode(',',array_filter(array_unique($role_module)));
        return $module;
    }
    /**
    *根据帐号角色获取平台信息
    */
    public function login_role_system()
    {
        $where['module_id'] = array('in',$this->login_role_module());
        $data = $this->moduleDB->field('platform_id')->where($where)->group('platform_id')->select();
        foreach ($data as $key => $val) 
        {
            $role_system[] = $val['platform_id'];
        }
        $system = implode(',',array_filter($role_system));
        return $system;
    }
}