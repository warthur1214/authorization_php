<?php
namespace Home\Controller;
use Home\Common\MyController;
class OrganController extends MyController 
{
    function __construct()
    {
        parent::__construct();
    }
    /**
    *添加企业页
    */
    public function addOrgan()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addOrgan',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('addOrgan');
    }
    /**
    *添加企业数据处理
    */
    public function addOrganAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addOrgan',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $organ_name = $this->organDB->field('organ_id')->where(array('organ_name' => I('post.organ_name')))->find();
        $organ_short_name = $this->organDB->field('organ_id')->where(array('organ_short_name' => I('post.organ_short_name')))->find();

        if(!empty($organ_name))
        {
            echo json_encode(array('msg' => '企业名称已存在，请重新输入','status' => 0));
            exit;
        }
        if(!empty($organ_short_name))
        {
            echo json_encode(array('msg' => '企业简称已存在，请重新输入','status' => 0));
            exit;
        }

        $array = array(
            'organ_name' => I('post.organ_name'),
            'organ_short_name' => I('post.organ_short_name'),
            'organ_type_id' => I('post.organ_type_id'),
            'prov_code' => I('post.prov_code'),
            'city_code' => I('post.city_code'),
            'address' => I('post.address'),
            'contact_person' => I('post.contact_person'),
            'contact_phone' => I('post.contact_phone'),
            'contact_email' => I('post.contact_email'),
            'coop_type_id' => I('post.coop_type_id'),
            'channel_id' => I('post.channel_id'),
            'channel_secret' => I('post.channel_secret'),
            'comment' => I('post.comment'),
            'organ_level_id' => 1
            );

        $insertId = $this->organDB->data($array)->add();

        $msg = ($insertId > 0) ? '添加成功' : '添加失败';
        $status = ($insertId > 0) ? 1 : 0;

        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *企业列表页
    */
    public function organList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('organList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('organList');
    }
    /**
    *企业列表数据处理
    */
    public function organListAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('organList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $field = "organ.organ_id,organ.organ_name,organ.organ_short_name,type.organ_type_name as organ_type,organ.coop_type_id,organ.parent_organ_id,organ.is_available";
        $where = "1=1 and organ.parent_organ_id = 0";
        //根据企业机构标识展示企业信息
        if ($this->belong_organ()) 
        {
                $where .= " and organ.organ_id = '".$this->belong_organ()."'";
        }
        //根据企业名称搜索
        if(I('post.name'))
        {
            $where .= " and (organ.organ_name like '%".I('post.name')."%'";
            $where .= " or organ.organ_short_name like '%".I('post.name')."%')";
        }
        //根据企业类型搜索
        if(I('post.organ_type_id'))
        {
            $where .= " and organ.organ_type_id = '".I('post.organ_type_id')."'";
        }
        //根据合作类型搜索
        if(I('post.coop_type_id'))
        {
            $where .= " and organ.coop_type_id like '%".I('post.coop_type_id')."%'";
        }
        $data = $this->organDB->organList($where,$field, false);
        $cooperate =  M('CoopType')->field('coop_type_id,coop_type')->select();
        foreach ($cooperate as $key => $val) 
        {
            $newCoo[$val['coop_type_id']] = $val['coop_type'];
        }
        foreach ($data as $key => $val) 
        {
            $coop = array_filter(explode(',',$val['coop_type_id']));
            $coop = array_map(function(&$v) use ($newCoo) {
                return $newCoo[$v];
            }, $coop);
            $data[$key]['coop_type_id'] = $coop;
        }
        echo json_encode(array('data' => $data));
        exit;
    }
    /**
    *修改企业页
    */
    public function editOrgan()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editOrgan',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('editOrgan');
    }
    /**
    *修改公司页
    */
    public function checkOrgan()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editOrgan',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('editOrgan');
    }
    /**
    *获取企业信息
    */
    public function getInfoById()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editOrgan',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $where = "1=1 and organ.organ_id = '".I('get.id')."'";
        $data = $this->organDB->sonList($where);
        $info = $data[0];
        //获取合作类型
        $coop['coop_type_id'] = array('in',$info['coop_type_id']);
        $cooperate =  M('CoopType')->field('coop_type_id,coop_type')->where($coop)->select();
        foreach ($cooperate as $key => $val) 
        {
            $id[] = $val['coop_type_id'];
        }
        foreach ($cooperate as $key => $val) 
        {
            $type[] = $val['coop_type'];
        }
        $info['coop_type_id'] = $id;
        $info['coop_type'] = $type;
        
        $info = array_map(array($this, 'filterNull'), $info);
        
        echo json_encode($info);
        exit;
    }
    /**
    *修改企业数据处理
    */
    public function editOrganAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editOrgan',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $organ_name = $this->organDB->field('organ_id')->where(array('organ_name' => I('post.organ_name')))->find();
        $organ_short_name = $this->organDB->field('organ_id')->where(array('organ_short_name' => I('post.organ_short_name')))->find();

        if(!empty($organ_name) && $organ_name['organ_id'] != I('post.organ_id'))
        {
            echo json_encode(array('msg' => '企业名称已存在，请重新输入','status' => 0));
            exit;
        }
        if(!empty($organ_short_name) && $organ_short_name['organ_id'] != I('post.organ_id'))
        {
            echo json_encode(array('msg' => '企业简称已存在，请重新输入','status' => 0));
            exit;
        }

        $array = array(
            'organ_name' => I('post.organ_name'),
            'organ_short_name' => I('post.organ_short_name'),
            'organ_type_id' => I('post.organ_type_id'),
            'prov_code' => I('post.prov_code'),
            'city_code' => I('post.city_code'),
            'address' => I('post.address'),
            'contact_person' => I('post.contact_person'),
            'contact_phone' => I('post.contact_phone'),
            'contact_email' => I('post.contact_email'),
            'coop_type_id' => I('post.coop_type_id'),
            'channel_id' => I('post.channel_id'),
            'channel_secret' => I('post.channel_secret'),
            'comment' => I('post.comment')
            );
        $id = $this->organDB->data($array)->where(array('organ_id' => I('post.organ_id')))->save();
        $msg = ($id > 0) ? '修改成功' : '修改失败或未修改';
        $status = ($id > 0) ? 1 : 0;

        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *删除企业
    */
    public function delOrgan()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('delOrgan',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        //检查企业是否有机构信息
        $son = $this->organDB->field('organ_id')->where(array('parent_organ_id' => I('get.id')))->find();
        if(!empty($son))
        {
            echo json_encode(array('msg' => '该企业存在所属信息，请检查','status' => 0));
            exit;
        }
        $id = $this->organDB->where(array('organ_id' => I('get.id')))->delete();

        $msg = ($id > 0) ? '删除成功' : '删除失败';
        $status = ($id > 0) ? 1 : 0;
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;

    }
    /**
    *企业失效
    */
    public function organAvailable()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('organAvailable',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $id = $this->organDB->data(array('is_available' => I('post.is_available')))->where(array('organ_id' => I('get.id')))->save();
        echo json_encode(array('status' => 1));
        exit;
    }
    /**
    *企业类型
    */
    public function organType()
    {
        $info = M('organType')->field('organ_type_id,organ_type_name')->select();
        echo json_encode($info);
        exit;
    }
    /**
    *合作类型
    */
    public function cooperate()
    {
        $info =  M('CoopType')->field('coop_type_id,coop_type')->select();
        echo json_encode($info);
        exit;
    }
    /**
    *省份
    */
    public function province()
    {
        $info = M('Prov')->field('prov_code,prov_name')->select();
        echo json_encode($info);
        exit;
    }
    /**
    *根据省份获取城市
    */
    public function getCityById()
    {
        $info = M('City')->field('city_code,city_name')->where(array('prov_code' => I('get.id')))->select();
        echo json_encode($info);
        exit;
    }
}