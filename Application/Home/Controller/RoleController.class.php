<?php
namespace Home\Controller;
use Home\Common\MyController;
class RoleController extends MyController 
{
    function __construct()
    {
        parent::__construct();
    }
    /**
    *添加角色页
    */
    public function addRole()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('addRole',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $this->display('addRole');
    }
    /**
    *添加角色数据处理
    */
    public function addRoleAjax()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('addRole',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $info = $this->roleDB->field('role_id')->where(array('role_name' => I('post.role_name')))->find();
        if(!empty($info))
        {
            echo json_encode(array('msg' => '角色名已存在，请重新输入','status' => 0));
            exit;
        }

        $array = array(
            'role_name' => I('post.role_name'),
            'belonged_organ_id' => I('post.organ_id'),
            'desc' => I('post.desc')
            );
        //事务开启
        M()->startTrans();
        $insertId = $this->roleDB->data($array)->add();
        $roRow = $rmRow = $raRow = 1;
        if(I('post.role_organ'))
        {
            //角色企业关系入库
            $role_organ = explode(',',I('post.role_organ'));
            $roRow = $this->roleDB->addRoleContact('tp_role_organ_rel','(role_id,organ_id)',$role_organ,$insertId);
        }
        if(I('post.role_module'))
        {
            //角色模块关系入库
            $role_module = explode(',',I('post.role_module'));
            $rmRow = $this->roleDB->addRoleContact('tp_role_module_rel','(role_id,module_id)',$role_module,$insertId);
        }
        if(I('post.role_account'))
        {
            //角色帐号关系入库
            $role_account = explode(',',I('post.role_account'));
            $raRow = $this->roleDB->addRoleAccount($role_account,$insertId);
        }
        if($insertId > 0 && $roRow >= 1 && $rmRow >= 1 && $raRow >= 1)
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
    *角色列表页
    */
    public function roleList()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('roleList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $this->display('roleList');
    }
    /**
    *角色列表数据处理
    */
    public function roleListAjax()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('roleList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $where['role_id'] = array('neq','1');
        //根据企业机构标识展示企业信息
        switch ($this->belong_organ()) 
        {
            case false:

                break;
            default://获取管理企业的channel_id
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
                }
                else
                {
                    $id = '-1';
                }
                $where['belonged_organ_id'] = array('in',$id);
                break;
        }
        //根据角色归属企业搜索
        if(I('post.organ_id'))
        {
            $where['belonged_organ_id'] = array('eq',I('post.organ_id'));
        }
        $data = $this->roleDB->where($where)->order('role_id desc')->select();
        foreach ($data as $key => $val) 
        {
            $organ = $this->organDB->field('organ_name')->where(array('organ_id' => $val['belonged_organ_id']))->find();
            $data[$key]['organ_name'] = $organ['organ_name'] ? $organ['organ_name'] : '全部';
        }

        echo json_encode(array('data' => $data));
        exit;
    }
    /**
    *修改角色页
    */
    public function editRole()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('editRole',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $this->display('editRole');
    }
    /**
    *获取角色信息
    */
    public function getInfoById()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editRole',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $info = $this->roleDB->where(array('role_id' => I('get.id')))->find();
        //根据角色id获取相关机构信息
        $organ = $this->roleOrganDB->field('organ_id')->where(array('role_id' => I('get.id')))->select();
        foreach ($organ as $key => $val) 
        {
            $role_organ[$key] = $val['organ_id'];
        }
        //获取平台和模块信息
        $system = $this->moduleDB->field('module_id,platform_id')->select();
        //以模块id为key重组平台信息
        foreach ($system as $key => $val) 
        {
            $sys[$val['module_id']] = $val['platform_id'];
        }
        //根据角色id获取相关模块信息
        $module = $this->roleModuleDB->field('module_id')->where(array('role_id' => I('get.id')))->select();
        foreach ($module as $key => $val) 
        {
            $role_module[$key] = $val['module_id'];
            $role_system[$key] = $sys[$val['module_id']];
        }
        //根据角色id获取相关帐号信息
        $account = $this->accountRoleDB->field('account_id')->where(array('role_id' => I('get.id')))->select();
        foreach ($account as $key => $val) 
        {
            $role_account[$key] = $val['account_id'];
        }

        $info['role_organ'] = implode(',',$role_organ);
        $info['role_system'] = implode(',',array_filter(array_unique($role_system)));
        $info['role_module'] = implode(',',$role_module);
        $info['role_account'] = implode(',',$role_account);

        echo json_encode($info);
        exit;
    }
    /**
    *修改角色数据处理
    */
    public function editRoleAjax()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('editRole',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $info = $this->roleDB->field('role_id')->where(array('role_name' => I('post.role_name')))->find();
        if(!empty($info) && $info['role_id'] != I('post.role_id'))
        {
            echo json_encode(array('msg' => '角色名已存在，请重新输入','status' => 0));
            exit;
        }

        $array = array(
            'role_name' => I('post.role_name'),
            'belonged_organ_id' => I('post.belonged_organ_id'),
            'desc' => I('post.desc')
            );
        //事务开启
        M()->startTrans();
        $id = $this->roleDB->data($array)->where(array('role_id' => I('post.role_id')))->save();
        $roRow = $rmRow = $raRow = 1;
        //删除现有的角色企业关系数据
        $this->roleOrganDB->where(array('role_id' => I('post.role_id')))->delete();
        if(I('post.role_organ'))
        {
            //角色企业关系入库
            $role_organ = explode(',',I('post.role_organ'));
            //新增角色企业关系数据
            $roRow = $this->roleDB->addRoleContact('tp_role_organ_rel','(role_id,organ_id)',$role_organ,I('post.role_id'));
        }
        //删除现有的角色模块关系数据
        $this->roleModuleDB->where(array('role_id' => I('post.role_id')))->delete();
        if(I('post.role_module'))
        {
            //角色模块关系入库
            $role_module = explode(',',I('post.role_module'));
            //新增角色模块关系数据
            $rmRow = $this->roleDB->addRoleContact('tp_role_module_rel','(role_id,module_id)',$role_module,I('post.role_id'));
        }
        //删除现有的角色帐号关系数据
        $this->accountRoleDB->where(array('role_id' => I('post.role_id')))->delete();
        if(I('post.role_account'))
        {
            //角色帐号关系入库
            $role_account = explode(',',I('post.role_account'));
            //新增角色帐号关系数据
            $raRow = $this->roleDB->addRoleAccount($role_account,I('post.role_id'));
        }
        if($id >= 0 && $roRow >= 1 && $rmRow >= 1 && $raRow >= 1)
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
    *删除角色
    */
    public function delRole()
    {
        /*##############验证当前用户是否拥有模块访问权限##############**/
        A('Check')->isUse('delRole',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前用户是否拥有模块访问权限##############**/

        $account = $this->accountRoleDB->field('account_id')->where(array('role_id' => I('get.id')))->find();
        if(!empty($account))
        {
            echo json_encode(array('msg' => '删除失败，有账号属于该角色，请检查','status' => 0));
            exit;
        }
        //事务开启
        M()->startTrans();
        $id = $this->roleDB->where(array('role_id' => I('get.id')))->delete();
        $this->roleModuleDB->where(array('role_id' => I('get.id')))->delete();
        $this->roleOrganDB->where(array('role_id' => I('get.id')))->delete();
        
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
    *根据角色归属获取企业帐号信息
    */
    public function getAccountByOrgan()
    {
        $where['belonged_organ_id'] = I('get.pid');
        $where['account_id'] = array('neq','1');
        //获取帐号信息
        $data = $this->accountDB->field('account_id,account_name')->where($where)->select();
        if($this->belong_organ() != '0' && !I('get.pid'))
        {
            $data = array();
        }
        echo json_encode($data);
        exit;
    }
    /**
    *获取功能权限
    */
    public function roleModuleList()
    {
        //获取当前帐号角色管理的平台id
        switch (session('auth_account_id')) 
        {
            case '1':

                break;
            
            default:
                $where['platform_id'] = array('in',$this->login_role_system());
                break;
        }
        $data = M('Platform')->where($where)->select();
        foreach ($data as $key => $val) 
        {
            $data[$key]['system_module'] = $this->getModule('0','0',$val['platform_id']);
        }
        echo json_encode($data);
        exit;
    }
    /**
    *模块信息无限极递归函数
    */
    public function getModule($pid = '0',$level = '0',$system_id)
    {
        //获取该帐号管理的企业下的模块信息
        switch (session('auth_account_id')) 
        {
            case '1':

                break;
            
            default:
                $where['module_id'] = array('in',$this->login_role_module());
                break;
        }
        $where['parent_module_id'] = array('eq',$pid);
        $where['display_level'] = array('eq',$level);
        $where['platform_id'] = array('eq',$system_id);
        $list = $this->moduleDB->field('module_id,module_name,platform_id')->where($where)->select();
        if($list)
        {
            foreach ($list as $key => $val) 
            {
                if ($val['module_id']) 
                {
                    $val['son'] = $this->getModule($val['module_id'],$level+1,$system_id);
                }
                $array[] = $val;
            }
        }
        return $array;
    }
    /**
    *根据所选帐号获取帐号信息
    */
    public function getAccountById()
    {
        if(empty(I('post.account_id')))
        {
            echo json_encode(array('data' => array()));
            exit;
        }
        $where = "1=1 and acc.account_id in (".I('post.account_id').")";
        //获取帐号信息
        $data = $this->accountDB->accountListByRole($where);
        foreach ($data as $key => $val) 
        {
            $organ = $this->getList($val['belonged_organ_id'],'organ.parent_organ_id as organ_id,organ.organ_name','organ.organ_id');
            $name = A('Account')->getOrganName($organ);
            $data[$key]['organ_name'] = $name;
        }
        echo json_encode(array('data' => $data));
        exit;
    }
}