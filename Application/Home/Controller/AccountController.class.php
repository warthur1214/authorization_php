<?php
namespace Home\Controller;
use Home\Common\MyController;
class AccountController extends MyController 
{
    function __construct()
    {
        parent::__construct();
    }
    /**
    *添加帐号页
    */
    public function addAccount()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addAccount',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('addAccount');
    }
    /**
    *添加帐号数据处理
    */
    public function addAccountAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addAccount',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $info = $this->accountDB->field('account_id')->where(array('account_name' => I('post.account_name')))->find();

        if(!empty($info))
        {
            echo json_encode(array('msg' => '账号名已存在，请重新输入','status' => 0));
            exit;
        }
        $array = array(
            'account_name' => I('post.account_name'),
            'real_name' => I('post.display_name'),
            'password' => I('post.password'),
            'belonged_organ_id' => I('post.organ_id')
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
    *帐号列表页
    */
    public function accountList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('accountList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('accountList');
    }
    /**
    *帐号列表数据处理
    */
    public function accountListAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('accountList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $where = "1=1";
        if(session('auth_account_id') != '1')
        {
            $where .= " and acc.account_id != '1'";
            if(session('auth_account_id') != '28')
            {
                $where .= " and acc.belonged_organ_id != '0'";
            }
        }
        //获取管理企业的channel_id
        $comWhere['parent_organ_id'] = $this->belong_organ();
        $comWhere['organ_id'] = $this->belong_organ(); 
        $comWhere['_logic'] = 'OR';
        $com = $this->organDB->field('channel_id')->where($comWhere)->select();

        if($com)
        {
            foreach ($com as $key => $val) 
            {
               $new_com[] = $val['channel_id'];
            }
            $channel_id = implode(',',array_unique($new_com));
        }
        else
        {
            $channel_id = '-1';
        }
        //根据channel_id获取信息
        $organWhere['channel_id'] = array('in',$channel_id);
        $organ_id = $this->organDB->field('organ_id')->where($organWhere)->select();
        if($organ_id)
        {
            foreach ($organ_id as $key => $val) 
            {
                $new_id[] = $val['organ_id'];
            }
            $id = implode(',',$new_id);
            if (session('auth_account_id') == 28) {
                $id .= ",0";
            }
        }
        else
        {
            $id = '-1';
        }
        $where .= " and acc.belonged_organ_id in (".$id.")";

        //根据帐号名称搜索
        if(I('post.account_name'))
        {
            $where .= " and acc.account_name like '%".I('post.account_name')."%'";
        }
        //根据帐号姓名搜索
        if(I('post.display_name'))
        {
            $where .= " and acc.real_name like '%".I('post.display_name')."%'";
        }
        //根据帐号归属企业搜索
        if(I('post.organ_id'))
        {
            $where .= " and acc.belonged_organ_id = '".I('post.organ_id')."'";
        }
        //获取帐号信息
        $data = $this->accountDB->accountList($where);
        //获取帐号角色关系信息
        $accountRole = $this->accountRoleDB->accountRoleList();
        //以帐号id为key重组帐号角色关系信息
        foreach ($accountRole as $key => $val) 
        {
            $newAccRole[$val['account_id']][] = $val['role_name']; 
        }
        foreach ($data as $key => $val) 
        {
            $organ = $this->getList($val['belonged_organ_id'],'organ.parent_organ_id as organ_id,organ.organ_name','organ.organ_id');
            $name = $this->getOrganName($organ);
            $data[$key]['organ_name'] = $name;
            $data[$key]['role_name'] = $newAccRole[$val['account_id']];
        }
        echo json_encode(array('data' => $data));
        exit;
    }
    /**
    *机构完整名称处理
    */
    public function getOrganName($data)
    {
        $name = '';
        if($data)
        {
            foreach ($data as $key => $val) 
            {
                $son = $val['organ_name'];
                if($val)
                {
                    $parent = $this->getOrganName($val);
                    $name = ($parent) ? $parent."_".$son : $son; 
                }
            }
        }
        return htmlspecialchars($name);
    }
    /**
    *修改帐号页
    */
    public function editAccount()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editAccount',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('editAccount');
    }
    /**
    *获取帐号信息
    */
    public function getInfoById()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editAccount',1); //模块关键词 //是否ajax 0 1
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
    *修改帐号数据处理
    */
    public function editAccountAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editAccount',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $info = $this->accountDB->field('account_id')->where(array('account_name' => I('post.account_name')))->find();

        if(!empty($info) && $info['account_id'] != I('post.account_id'))
        {
            echo json_encode(array('msg' => '账号名已存在，请重新输入','status' => 0));
            exit;
        }
        $array = array(
            'account_name' => I('post.account_name'),
            'real_name' => I('post.real_name'),
            'password' => I('post.password'),
            'belonged_organ_id' => I('post.belonged_organ_id')
            );
        //事务开启
        M()->startTrans();
        $id = $this->accountDB->data($array)->where(array('account_id' => I('post.account_id')))->save();
        $roleId = explode(',',I('post.role_id'));
        $del = $this->accountRoleDB->where(array('account_id' => I('post.account_id')))->delete();
        $row = $this->accountRoleDB->addAccountRole($roleId,I('post.account_id'));
        if($id > 0 || count($roleId) == $row)
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
    *删除帐号
    */
    public function delAccount()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('delAccount',1); //模块关键词 //是否ajax 0 1
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
    *帐号失效
    */
    public function accountAvailable()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('organAccountAvailable',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $id = $this->accountDB->data(array('is_available' => I('post.is_available')))->where(array('account_id' => I('get.id')))->save();
        echo json_encode(array('status' => 1));
        exit;
    }
    /**
    *根据帐号归属企业id获取机构信息
    */
    public function getSonById()
    {
        if(I('get.id'))
        {
            $data = $this->getList(I('get.id'),'organ.organ_id,organ.organ_name');
        }
        else
        {
            $data = array();
        }
        echo json_encode($data);
        exit;
    }
}