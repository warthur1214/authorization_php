<?php
namespace Home\Controller;
use Home\Common\MyController;
class OrganAccountController extends MyController 
{
    function __construct()
    {
        parent::__construct();
    }
    /**
    *添加企业帐号
    */
    public function addOrganAccount()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addOrganAccount',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('addOrganAccount');
    }
    /**
    *添加企业帐号数据处理
    */
    public function addOrganAccountAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addOrganAccount',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $info = $this->accountDB->field('account_id')->where(array('account_name' => I('post.account_name')))->find();

        if(!empty($info))
        {
            echo json_encode(array('msg' => '账号名已存在，请重新输入','status' => 0));
            exit;
        }
        $channel_id = $this->organDB->field('organ_channel_id')->where(array('organ_id' => I('post.organ_parent_id')))->find();
        $array = array(
            'account_name' => I('post.account_name'),
            'display_name' => I('post.display_name'),
            'password' => I('post.password'),
            'create_time' => time(),
            'organ_parent_id' => I('post.organ_parent_id'),
            'organ_channel_id' => $channel_id['organ_channel_id']
            );
        //事务开启
        M()->startTrans();
        $insertId = $this->accountDB->data($array)->add();
        $roleId = explode(',',I('post.role_id'));
        $row = $this->accountRoleDB->addAccountRole($roleId,$insertId);
        if($insertId > 0 && count($roleId) == $row)
        {
            //事务提交
            M()->commit();
            $msg = '添加成功';
            $status = 1;
        }
        else
        {
            //事务回滚
            M()->rollback();
            $msg = '添加失败';
            $status = 0;
        }
        
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *企业帐号列表页
    */
    public function organAccountList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('organAccountList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('organAccountList');
    }
    /**
    *企业帐号列表数据处理
    */
    public function organAccountListAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('organAccountList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $where = "1=1 and acc.account_id != '1' and acc.organ_id = '0'";
        //获取该帐号管理的企业下的企业帐号信息
        switch (session('auth_account_id')) 
        {
            case '1':

                break;
            
            default:
                $where .= " and acc.organ_parent_id = '".$this->belong_organ()."'";
                $where .= " and acc.account_id != '".session('auth_account_id')."'";
                break;
        }
        //根据帐号名称搜索
        if(I('post.account_name'))
        {
            $where .= " and acc.account_name like '%".I('post.account_name')."%'";
        }
        //根据帐号姓名搜索
        if(I('post.display_name'))
        {
            $where .= " and acc.display_name like '%".I('post.display_name')."%'";
        }
        //根据帐号归属企业搜索
        if(I('post.organ_id'))
        {
            $where .= " and acc.organ_id = '".I('post.organ_id')."'";
        }
        //根据授权系统搜索
        if(I('post.system_id'))
        {
            //根据平台获取模块id
            $system_id = explode(',',I('post.system_id'));
            $systemWhere['system_id'] = array('in',$system_id);
            $module_id = $this->moduleDB->field('module_id')->where($systemWhere)->select();
            if($module_id)
            {
                foreach ($module_id as $key => $val) 
                {
                    $m_id[] = $val['module_id'];
                }
                $str = implode(',',$m_id);
            }
            else
            {
                $str = '-1';
            }
            //根据模块id获取角色信息
            $roleWhere['module_id'] = array('in',$str);
            $role = $this->roleModuleDB->field('role_id')->where($roleWhere)->select();
            foreach ($role as $key => $val) 
            {
                $role_id[] = $val['role_id']; 
            }
            $role_id = array_unique($role_id);
            if($role_id)
            {
                $accountWhere['role_id'] = array('in',implode(',',$role_id));
            }
            else
            {
                $accountWhere['role_id'] = array('eq','0');
            }
            //根据角色信息获取帐号信息
            $account = $this->accountRoleDB->field('account_id')->where($accountWhere)->select();
            foreach ($account as $key => $val) 
            {
                $account_id[] = $val['account_id']; 
            }
            $account_id = $account_id ? implode(',',array_unique($account_id)) : '-1';
            $where .= " and acc.account_id in (".$account_id.")";
        }
        //获取帐号信息
        $data = $this->accountDB->accountList($where);
        //获取平台和角色相关信息
        $system = $this->accountDB->getSystem();
        //dump($data);exit;
        //以角色id为主键重组平台信息
        foreach ($system as $key => $val) 
        {
            $roleSys[$val['role_id']][] = $val['system_name'];
        }
        //获取帐号角色关系信息
        $accountRole = $this->accountRoleDB->accountRoleList();
        //以帐号id为key重组帐号角色关系信息
        foreach ($accountRole as $key => $val) 
        {
            $newAccRole[$val['account_id']] = $roleSys[$val['role_id']]; 
        }
        //重组帐号信息
        foreach ($data as $key => $val) 
        {
            $data[$key]['system'] = array_filter($newAccRole[$val['account_id']]);
        }
        echo json_encode(array('data' => $data));
        exit();
    }
    /**
    *修改企业帐号
    */
    public function editOrganAccount()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editOrganAccount',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('editOrganAccount');
    }
    /**
    *获取企业帐号信息
    */
    public function getInfoById()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editOrganAccount',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $info = $this->accountDB->where(array('account_id' => I('get.id')))->find();
        //根据帐号id获取帐号角色
        $role = $this->accountRoleDB->field('role_id')->where(array('account_id' => $info['account_id']))->select();
        foreach ($role as $key => $val) 
        {
            $role_id[] = $val['role_id'];
        }
        $info['role_id'] = $role_id;
        echo json_encode($info);
        exit;
    }
    /**
    *修改企业帐号数据处理
    */
    public function editOrganAccountAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editOrganAccount',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $info = $this->accountDB->field('account_id')->where(array('account_name' => I('post.account_name')))->find();
        if(!empty($info) && $info['account_id'] != I('post.account_id'))
        {
            echo json_encode(array('msg' => '账号名已存在，请重新输入','status' => 0));
            exit;
        }
        $channel_id = $this->organDB->field('organ_channel_id')->where(array('organ_id' => I('post.organ_parent_id')))->find();
        $array = array(
            'account_name' => I('post.account_name'),
            'display_name' => I('post.display_name'),
            'password' => I('post.password'),
            'organ_parent_id' => I('post.organ_parent_id'),
            'organ_channel_id' => $channel_id['organ_channel_id']
            );

        //事务开启
        M()->startTrans();
        $id = $this->accountDB->data($array)->where(array('account_id' => I('post.account_id')))->save();
        $roleId = explode(',',I('post.role_id'));
        $del = $this->accountRoleDB->where(array('account_id' => I('post.account_id')))->delete();
        $row = $this->accountRoleDB->addAccountRole($roleId,I('post.account_id'));
        if($id > 0 && count($roleId) == $row)
        {
            //事务提交
            M()->commit();
            $msg = '修改成功';
            $status = 1;
        }
        else
        {
            //事务回滚
            M()->rollback();
            $msg = '修改失败或未修改';
            $status = 0;
        }
        
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *删除企业帐号
    */
    public function delOrganAccount()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('delOrganAccount',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        //事务开启
        M()->startTrans();
        $id = $this->accountDB->where(array('account_id' => I('get.id')))->delete();
        $del = $this->accountRoleDB->where(array('account_id' => I('get.id')))->delete();
        if($id > 0)
        {
            //事务提交
            M()->commit();
            $msg = '删除成功';
            $status = 1;
        }
        else
        {
            //事务回滚
            M()->rollback();
            $msg = '删除失败';
            $status = 0;
        }
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;

    }
    /**
    *企业帐号失效
    */
    public function organAccountAvailable()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('organAccountAvailable',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $id = $this->accountDB->data(array('is_available' => I('post.is_available')))->where(array('account_id' => I('get.id')))->save();
        echo json_encode(array('status' => 1));
        exit;
    }
    /**
    *获取企业信息
    */
    public function organList()
    {
        switch (session('auth_account_id')) 
        {
            case '1':
                $pid = '0';
                break;
            
            default:
                $pid = $this->belong_organ();
                break;
        }

        $info = $this->getList($pid,'organ_id,organ_name,organ_bs','organ.parent_organ_id',' and organ.organ_bs < 3');
        echo json_encode($info);
        exit;
    }
    /**
    *获取授权系统
    */
    public function systemList()
    {
        //获取当前帐号角色管理的平台id
        switch (session('auth_account_id')) 
        {
            case '1':

                break;
            
            default:
                $where['system_id'] = array('in',$this->login_role_system());
                break;
        }
        $info = M('adminSystem')->where($where)->select();
        echo json_encode($info);
        exit;
    }
    /**
    *根据企业id获取角色信息
    */
    public function getRoleById()
    {
        $where['belonged_organ_id'] = array('eq',I('get.id'));
        $where['role_id'] = array('neq',"-1");
        $info = $this->roleDB->field('role_id,role_name')->where($where)->select();
        
        if($this->belong_organ() != '0' && !I('get.id'))
        {
            $info = array();
        }
        echo json_encode($info);
        exit;
    }
}