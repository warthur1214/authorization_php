<?php
namespace Home\Controller;
use Home\Common\MyController;
class CompanyController extends MyController 
{
    function __construct()
    {
        parent::__construct();
    }
    /**
    *添加公司页
    */
    public function addCompany()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addCompany',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('addCompany');
    }
    /**
    *添加公司数据处理
    */
    public function addCompanyAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addCompany',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $organ_name = $this->organDB->field('organ_id')->where(array('organ_name' => I('post.organ_name')))->find();
        $organ_short_name = $this->organDB->field('organ_id')->where(array('organ_short_name' => I('post.organ_short_name')))->find();

        if(!empty($organ_name))
        {
            echo json_encode(array('msg' => '公司名称已存在，请重新输入','status' => 0));
            exit;
        }
        if(!empty($organ_short_name))
        {
            echo json_encode(array('msg' => '公司简称已存在，请重新输入','status' => 0));
            exit;
        }

        $array = array(
            'organ_name' => I('post.organ_name'),
            'organ_short_name' => I('post.organ_short_name'),
            'parent_organ_id' => I('post.parent_organ_id'),
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

        $insertId = $this->organDB->data($array)->add();
        
        $msg = ($insertId > 0) ? '添加成功' : '添加失败';
        $status = ($insertId > 0) ? 1 : 0;

        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *公司列表页
    */
    public function companyList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('companyList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('companyList');
    }
    /**
    *公司列表数据处理
    */
    public function companyListAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('companyList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        echo json_encode(array('data' => []));
        exit;
        $field = "organ.organ_id,organ.organ_name,organ.organ_short_name,organ.coop_type_id,parent.organ_name as parent_name,province.prov_name,city.city_name,organ.address,organ.is_available";
        $where = "1=1 and organ.organ_bs = '2'";
        //根据企业机构标识展示企业信息
        switch ($this->belong_organ_bs()) 
        {
            case false:

                break;
            case '1'://企业级别展示帐号子公司信息
                $where .= " and organ.parent_organ_id = '".$this->belong_organ()."'";
                break;
            case '2'://公司级别展示自己
                $where .= " and organ.organ_id = '".$this->belong_organ()."'";
                break;
            default:
                $where .= " and organ.organ_id = '-1'";
                break;
        }
        //根据公司名称搜索
        if(I('post.name'))
        {
            $where .= " and (organ.organ_name like '%".I('post.name')."%'";
            $where .= " or organ.abbreviated_name like '%".I('post.name')."%')";
        }
        //根据公司归属搜索
        if(I('post.p_id'))
        {
            $where .= " and organ.parent_organ_id = '".I('post.p_id')."'";
        }
        $data = $this->organDB->organList($where,$field);
        $cooperate =  M('Cooperate')->select();
        foreach ($cooperate as $key => $val) 
        {
            $newCoo[$val['cooperate_id']] = $val['cooperate_type'];
        }
        foreach ($data as $key => $val) 
        {
            $coop = array_filter(explode(',',$val['cooperate_type']));
            $coop = array_map(function(&$v) use ($newCoo) {
                return $newCoo[$v];
            }, $coop);
            $data[$key]['cooperate_type'] = $coop;
            $data[$key]['address'] = $val['province'].$val['city'].$val['address'];
        }
        echo json_encode(array('data' => $data));
        exit;
    }
    /**
    *修改公司页
    */
    public function editCompany()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editCompany',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('editCompany');
    }
    /**
    *修改公司页
    */
    public function checkCompany()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editCompany',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('checkCompany');
    }
    /**
    *获取公司信息
    */
    public function getInfoById()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editCompany',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $where = "1=1 and organ.organ_id = '".I('get.id')."'";
        $data = $this->organDB->sonList($where);
        $info = $data[0];
        //获取合作类型
        if($info['cooperate_type'])
        {
            $coop['cooperate_id'] = array('in',$info['cooperate_type']);
        }
        $cooperate =  M('Cooperate')->where($coop)->select();
        foreach ($cooperate as $key => $val) 
        {
            $id[] = $val['cooperate_id'];
        }
        foreach ($cooperate as $key => $val) 
        {
            $type[] = $val['cooperate_type'];
        }
        $info['cooperate_id'] = $id;
        $info['cooperate_type'] = $type;
        
        $info = array_map(array($this, 'filterNull'), $info);
        
        echo json_encode($info);
        exit;
    }
    /**
    *修改公司数据处理
    */
    public function editCompanyAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editCompany',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $organ_name = $this->organDB->field('organ_id')->where(array('organ_name' => I('post.organ_name')))->find();
        $abbreviated_name = $this->organDB->field('organ_id')->where(array('abbreviated_name' => I('post.abbreviated_name')))->find();

        if(!empty($organ_name) && $organ_name['organ_id'] != I('post.organ_id'))
        {
            echo json_encode(array('msg' => '公司名称已存在，请重新输入','status' => 0));
            exit;
        }
        if(!empty($abbreviated_name) && $abbreviated_name['organ_id'] != I('post.organ_id'))
        {
            echo json_encode(array('msg' => '公司简称已存在，请重新输入','status' => 0));
            exit;
        }

        $array = array(
            'organ_name' => I('post.organ_name'),
            'abbreviated_name' => I('post.abbreviated_name'),
            'parent_organ_id' => I('post.parent_organ_id'),
            'prov_code' => I('post.prov_code'),
            'city_code' => I('post.city_code'),
            'address' => I('post.address'),
            'contact_person' => I('post.contact_person'),
            'contact_phone' => I('post.contact_phone'),
            'contact_email' => I('post.contact_email'),
            'cooperate_type' => I('post.cooperate_type'),
            'organ_channel_id' => I('post.organ_channel_id'),
            'organ_channel_secret' => I('post.organ_channel_secret'),
            'comment' => I('post.comment'),
            'organ_bs' => 2,
            'organ_depth' => 2
            );
        $id = $this->organDB->data($array)->where(array('organ_id' => I('post.organ_id')))->save();
        $msg = ($id > 0) ? '修改成功' : '修改失败或未修改';
        $status = ($id > 0) ? 1 : 0;

        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *删除公司
    */
    public function delCompany()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('delCompany',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        //检查公司是否有机构信息
        $son = $this->organDB->field('organ_id')->where(array('parent_organ_id' => I('get.id')))->find();
        if(!empty($son))
        {
            echo json_encode(array('msg' => '该公司存在所属信息，请检查','status' => 0));
            exit;
        }
        $id = $this->organDB->where(array('organ_id' => I('get.id')))->delete();

        $msg = ($id > 0) ? '删除成功' : '删除失败';
        $status = ($id > 0) ? 1 : 0;
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;

    }
    /**
    *公司失效
    */
    public function companyAvailable()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('companyAvailable',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $id = $this->organDB->data(array('is_available' => I('post.is_available')))->where(array('organ_id' => I('get.id')))->save();
        echo json_encode(array('status' => 1));
        exit;
    }
    /**
    *获取企业信息
    */
    public function organList()
    {
        echo json_encode([]);
        exit;
        //根据企业机构标识展示企业信息
        switch ($this->belong_organ_bs()) 
        {
            case false:

                break;
            case '1'://企业级别展示帐号所属公司信息
                $where['organ_id'] = $this->belong_organ();
                break;
            case '2'://公司级别展示所属公司父级信息
                $pid = $this->organDB->field('parent_organ_id')->where(array('organ_id' => $this->belong_organ()))->find();
                $where['organ_id'] = $pid['parent_organ_id'];
                break;
            default:
                $where['organ_id'] = '-1';
                break;
        }
        $where['organ_bs'] = '1';
        $where['is_available'] = '1';
        $info = $this->organDB->field('organ_id,organ_name')->where($where)->select();
        echo json_encode($info);
        exit;
    }
}