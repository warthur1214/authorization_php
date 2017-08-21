<?php
namespace Home\Controller;
use Home\Common\MyController;
class ModuleController extends MyController
{
    function __construct()
    {
        parent::__construct();
    }
    /**
     *添加模块页
     */
    public function index()
    {
        /*##############验证当前角色是否拥有模块访问权限##############**/
        A('Check')->isUse('addModule',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前角色是否拥有模块访问权限##############**/

        $where['display_level'] = array('lt',2);
        $where['platform_id'] = array('eq','-1');
        $parent = $this->moduleDB->field('module_id,module_name')->where($where)->order('sort_no desc,module_id asc')->select();
        $system = M('Platform')->field('platform_id,platform_name')->select();

        $this->assign('parent',$parent);
        $this->assign('system',$system);
        $this->display('addModule');
    }
    /**
     *添加模块数据处理
     */
    public function addModuleAjax()
    {
        /*##############验证当前角色是否拥有模块访问权限##############**/
        A('Check')->isUse('addModule',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前角色是否拥有模块访问权限##############**/

        $module_name = $this->moduleDB->field('module_id')->where(array('module_name' => I('post.module_name'),'platform_id' => I('post.platform_id')))->find();
        $matched_key = $this->moduleDB->field('module_id')->where(array('matched_key' => I('post.matched_key'),'platform_id' => I('post.platform_id')))->find();

        if(!empty($module_name))
        {
            echo json_encode(array('msg' => '模块名已存在，请重新输入','status' => 0));
            exit;
        }
        if(!empty($matched_key))
        {
            echo json_encode(array('msg' => '关键词已存在，请重新输入','status' => 0));
            exit;
        }
        if(empty(I('post.module_parent_id')))
        {
            $mLv = array(
                'display_level' => 0
            );
        }
        else
        {
            $parent = $this->moduleDB->field('display_level')->where(array('module_id' => I('post.module_parent_id')))->find();
            $mLv = array(
                'display_level' => $parent['display_level'] + 1
            );
        }

        $array = array(
            'module_name' => I('post.module_name'),
            'module_url' => I('post.module_url'),
            'matched_key' => I('post.matched_key'),
            'platform_id' => I('post.platform_id'),
            'parent_module_id' => I('post.module_parent_id'),
            'sort_no' => I('post.sort_no'),
            'is_visible' => I('post.is_visible')
        );
        $array = array_merge($mLv,$array);

        $insertId = $this->moduleDB->data($array)->add();
        $data = array(
            'module_id' => $insertId,
            'role_id' => 1
        );
        $this->roleModuleDB->data($data)->add();
        $msg = ($insertId > 0) ? '添加成功' : '添加失败';
        $status = ($insertId > 0) ? 1 : 0;

        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
     *模块列表页
     */
    public function moduleList()
    {
        /*##############验证当前角色是否拥有模块访问权限##############**/
        A('Check')->isUse('moduleList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前角色是否拥有模块访问权限##############**/

        $system = M('Platform')->field('platform_id,platform_name')->select();

        $this->assign('system',$system);
        $this->display('moduleList');
    }
    /**
     *模块列表数据处理
     */
    public function moduleListAjax()
    {
        /*##############验证当前角色是否拥有模块访问权限##############**/
        A('Check')->isUse('moduleList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前角色是否拥有模块访问权限##############**/

        $draw = I('post.draw');//计数器
        $start = I('post.start');//分页偏移值
        $end = I('post.length');//分页每页显示数
        $search = I('post.search');//查询框提交参数 array 取消使用
        $sort = I('post.order');//排序字段 array
        $columns = I('post.columns');//数据列 array
        if(I('param.platform_id') != '')
        {
            $where['platform_id'] = array('eq',I('param.platform_id'));
        }
        else
        {
            $where['platform_id'] = array('eq', C("PLATFORM_ID"));
        }

        $where['parent_module_id'] = array('eq','0');
        $fields = ['*', 'matched_key'=>'module_matched_key', 'sort_no'=>'module_sort'];
        $data = $this->moduleDB->where($where)->field($fields)->select();
        $dataCnt = count($data);

        $result = array(
            "draw"=>$draw,
            "recordsTotal"=>$dataCnt,
            "recordsFiltered"=>$dataCnt,
            "data"=>$data
        );

        echo json_encode($result);
        exit();
    }
    /**
     *修改模块页
     */
    public function editModule()
    {
        /*##############验证当前角色是否拥有模块访问权限##############**/
        A('Check')->isUse('editModule',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前角色是否拥有模块访问权限##############**/

        $info = $this->moduleDB->where(array('module_id' => I('get.id')))->find();
        $where['display_level'] = array('lt',2);
        $where['platform_id'] = array('eq','-1');
        $parent = $this->moduleDB->field('module_id,module_name')->where($where)->order('sort_no desc,module_id asc')->select();
        $system = M('Platform')->field('platform_id,platform_name')->select();

        $this->assign('info',$info);
        $this->assign('parent',$parent);
        $this->assign('system',$system);
        $this->display('editModule');
    }
    /**
     *修改模块数据处理
     */
    public function editModuleAjax()
    {
        /*##############验证当前角色是否拥有模块访问权限##############**/
        A('Check')->isUse('editModule',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前角色是否拥有模块访问权限##############**/

        $module_name = $this->moduleDB->field('module_id')->where(array('module_name' => I('post.module_name'),'platform_id' => I('post.platform_id')))->find();
        $matched_key = $this->moduleDB->field('module_id')->where(array('matched_key' => I('post.matched_key'),'platform_id' => I('post.platform_id')))->find();

        if(!empty($module_name) && $module_name['module_id'] != I('post.module_id'))
        {
            echo json_encode(array('msg' => '模块名已存在，请重新输入','status' => 0));
            exit;
        }
        if(!empty($matched_key) && $matched_key['module_id'] != I('post.module_id'))
        {
            echo json_encode(array('msg' => '关键词已存在，请重新输入','status' => 0));
            exit;
        }
        if(empty(I('post.module_parent_id')))
        {
            $mLv = array(
                'display_level' => 0
            );
        }
        else
        {
            $parent = $this->moduleDB->field('display_level')->where(array('module_id' => I('post.module_parent_id')))->find();
            $mLv = array(
                'display_level' => $parent['display_level'] + 1
            );
        }

        $array = array(
            'module_name' => I('post.module_name'),
            'module_url' => I('post.module_url'),
            'matched_key' => I('post.matched_key'),
            'platform_id' => I('post.platform_id'),
            'parent_module_id' => I('post.module_parent_id')
        );
        $array = array_merge($mLv,$array);
        $id = $this->moduleDB->where(array('module_id' => I('post.module_id')))->data($array)->save();
        $msg = ($id > 0) ? '修改成功' : '修改失败或未修改';
        $status = ($id > 0) ? 1 : 0;

        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
     *删除模块
     */
    public function delModule()
    {
        /*##############验证当前角色是否拥有模块访问权限##############**/
        A('Check')->isUse('delModule',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前角色是否拥有模块访问权限##############**/

        $info = $this->moduleDB->field('module_id')->where(array('parent_module_id' => I('get.id')))->find();
        if(!empty($info))
        {
            echo json_encode(array('msg' => '删除失败，该模块存在子模块','status' => 0));
            exit;
        }
        $id = $this->moduleDB->where(array('module_id' => I('get.id')))->delete();
        $this->roleModuleDB->where(array('module_id' => I('get.id')))->delete();
        $msg = ($id > 0) ? '删除成功' : '删除失败';
        $status = ($id > 0) ? 1 : 0;

        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
     *修改排序数据处理
     */
    public function editOther()
    {
        $this->moduleDB->where(array('module_id' => I('get.id')))->data(array('sort_no' => I('post.sort_no')))->save();

        echo json_encode(array('status' => '1'));
        exit;
    }
    /**
     *获取子模块
     */
    public function getSonM()
    {
        $son = $this->moduleDB->where(array('parent_module_id' => I('get.id')))->order('sort_no desc,module_id asc')->select();
        foreach ($son as $key => $val)
        {
            $gson = $this->moduleDB->where(array('parent_module_id' => $val['module_id']))->order('sort_no desc,module_id asc')->select();
            foreach ($gson as $keys => $vals)
            {
                $son[] = $vals;
            }
        }

        echo json_encode(array('data' => $son));
        exit;
    }
    /**
     *根据平台id获取模块信息
     */
    public function getModuleById()
    {
        if(I('get.id'))
        {
            $where['platform_id'] = array('eq',I('get.id'));
        }
        else
        {
            $where['platform_id'] = array('eq','0');
        }
        $where['display_level'] = array('lt',2);
        $data = $this->moduleDB->field('module_id,module_name')->where($where)->order('sort_no desc,module_id asc')->select();
        echo json_encode($data);
        exit;
    }
}