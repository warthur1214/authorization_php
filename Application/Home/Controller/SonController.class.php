<?php
namespace Home\Controller;
use Home\Common\MyController;
class SonController extends MyController 
{
    function __construct()
    {
        parent::__construct();
    }
    /**
    *添加机构页
    */
    public function addSon()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addSon',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('addSon');
    }
    /**
    *添加机构数据处理
    */
    public function addSonAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('addSon',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $organ_name = $this->organDB->field('organ_id')->where(array('organ_name' => I('post.organ_name')))->find();
        $organ_short_name = $this->organDB->field('organ_id')->where(array('organ_short_name' => I('post.organ_short_name')))->find();

        if(!empty($organ_name))
        {
            echo json_encode(array('msg' => '机构名称已存在，请重新输入','status' => 0));
            exit;
        }
        if(!empty($organ_short_name))
        {
            echo json_encode(array('msg' => '机构简称已存在，请重新输入','status' => 0));
            exit;
        }

        $depth = $this->organDB->field('channel_id')->where(array('organ_id' => I('post.parent_organ_id')))->find();
        $array = array(
            'organ_name' => I('post.organ_name'),
            'organ_short_name' => I('post.organ_short_name'),
            'parent_organ_id' => I('post.parent_organ_id'),
            'organ_level_id' => I('post.organ_level_id'),
            'prov_code' => I('post.prov_code'),
            'city_code' => I('post.city_code'),
            'address' => I('post.address'),
            'contact_person' => I('post.contact_person'),
            'contact_phone' => I('post.contact_phone'),
            'contact_email' => I('post.contact_email'),
            'channel_id' => $depth['channel_id'],
            'comment' => I('post.comment')
            );

        $insertId = $this->organDB->data($array)->add();

        $msg = ($insertId > 0) ? '添加成功' : '添加失败';
        $status = ($insertId > 0) ? 1 : 0;

        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *机构列表页
    */
    public function sonList()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('sonList',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('sonList');
    }
    /**
    *机构列表数据处理
    */
    public function sonListAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('sonList',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $where = "1=1 and organ.parent_organ_id != 0";
        //根据企业机构标识展示企业信息
        if ($this->belong_organ()) 
        {
            //企业级别展示帐号子机构的子信息
            $comWhere['parent_organ_id'] = $this->belong_organ();
            $comWhere['organ_id'] = $this->belong_organ(); 
            $comWhere['_logic'] = 'OR';
            $com = $this->organDB->field('channel_id')->where($comWhere)->select();
            if($com)
            {
                foreach ($com as $key => $val) 
                {
                   $new_com[] = "'".$val['channel_id']."'";
                }
                $channel_id = implode(',',array_unique($new_com));
                $where .= " and organ.channel_id in (".$channel_id.")";
            }
            else
            {
                $where .= " and organ.channel_id = '-1'";
            }
        }
        //根据机构名称搜索
        if(I('post.name'))
        {
            $where .= " and (organ.organ_name like '%".I('post.name')."%'";
            $where .= " or organ.organ_short_name like '%".I('post.name')."%')";
        }
        //根据机构级别搜索
        if(I('post.organ_lv'))
        {
            $where .= " and organ.organ_level_id = '".I('post.organ_lv')."'";
        }
        //根据机构归属搜索
        if(I('post.p_id'))
        {
            $where .= " and organ.parent_organ_id = '".I('post.p_id')."'";
        }
        $data = $this->organDB->sonList($where);
        foreach ($data as $key => $val) 
        {
            $data[$key]['organ_area'] = $val['prov_name'].$val['city_name'].$val['address'];
        }
        echo json_encode(array('data' => $data));
        exit;
    }
    /**
    *修改机构页
    */
    public function editSon()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editSon',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('editSon');
    }
    /**
    *修改机构页
    */
    public function checkSon()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editSon',0); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/

        $this->display('checkSon');
    }
    /**
    *获取机构信息
    */
    public function getInfoById()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editSon',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $where = "1=1 and organ.organ_id = '".I('get.id')."'";
        $data = $this->organDB->sonList($where);
        $info = $data[0];
        $info = array_map(array($this, 'filterNull'), $info);
        echo json_encode($info);
        exit;
    }
    /**
    *修改机构数据处理
    */
    public function editSonAjax()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('editSon',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $organ_name = $this->organDB->field('organ_id')->where(array('organ_name' => I('post.organ_name')))->find();
        $organ_short_name = $this->organDB->field('organ_id')->where(array('organ_short_name' => I('post.organ_short_name')))->find();

        if(!empty($organ_name) && $organ_name['organ_id'] != I('post.organ_id'))
        {
            echo json_encode(array('msg' => '机构名称已存在，请重新输入','status' => 0));
            exit;
        }
        if(!empty($organ_short_name) && $organ_short_name['organ_id'] != I('post.organ_id'))
        {
            echo json_encode(array('msg' => '机构简称已存在，请重新输入','status' => 0));
            exit;
        }
        if(I('post.parent_organ_id') == I('post.organ_id'))
        {
            echo json_encode(array('msg' => '操作无效','status' => 0));
            exit;
        }
        $depth = $this->organDB->field('channel_id')->where(array('organ_id' => I('post.parent_organ_id')))->find();
        $array = array(
            'organ_name' => I('post.organ_name'),
            'organ_short_name' => I('post.organ_short_name'),
            'parent_organ_id' => I('post.parent_organ_id'),
            'organ_level_id' => I('post.organ_level_id'),
            'prov_code' => I('post.prov_code'),
            'city_code' => I('post.city_code'),
            'address' => I('post.address'),
            'contact_person' => I('post.contact_person'),
            'contact_phone' => I('post.contact_phone'),
            'contact_email' => I('post.contact_email'),
            'channel_id' => $depth['channel_id'],
            'comment' => I('post.comment')
            );

        $id = $this->organDB->data($array)->where(array('organ_id' => I('post.organ_id')))->save();
        $msg = ($id > 0) ? '修改成功' : '修改失败或未修改';
        $status = ($id > 0) ? 1 : 0;

        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *删除机构
    */
    public function delSon()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('delSon',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        //检查机构是否有子机构信息
        $son = $this->organDB->field('organ_id')->where(array('parent_organ_id' => I('get.id')))->find();
        if(!empty($son))
        {
            echo json_encode(array('msg' => '该机构存在所属信息，请检查','status' => 0));
            exit;
        }
        $id = $this->organDB->where(array('organ_id' => I('get.id')))->delete();

        $msg = ($id > 0) ? '删除成功' : '删除失败';
        $status = ($id > 0) ? 1 : 0;
        echo json_encode(array('msg' => $msg,'status' => $status));
        exit;
    }
    /**
    *机构失效
    */
    public function sonAvailable()
    {
        /*##############验证当前账户是否拥有模块访问权限##############**/
        A('Check')->isUse('sonAvailable',1); //模块关键词 //是否ajax 0 1
        /*##############验证当前账户是否拥有模块访问权限##############**/
        
        $id = $this->organDB->data(array('is_available' => I('post.is_available')))->where(array('organ_id' => I('get.id')))->save();
        echo json_encode(array('status' => 1));
        exit;
    }
    /**
    *机构归属
    */
    public function sonParentList()
    {
        $pid = I('get.pid') ? I('get.pid') : $this->belong_organ_parent();
        if($this->belong_organ())
        {
            $where = " and organ.organ_id = '".$this->belong_organ()."'";
        }
        else
        {
            $where = '';
        }
        $data = $this->getSonList($pid,$where);
        
        if(empty($data))
        {
            $data = array();
        }
        echo json_encode($data);
        exit;
    }
    /**
    *企业信息无限极递归函数
    */
    public function getSonList($pid = '0',$otherWhere = '')
    {

        $where = "1=1 and organ.parent_organ_id = '{$pid}' and organ.is_available = '1'".$otherWhere;
        $list = $this->organDB->organList($where,'organ.organ_id,organ.organ_name');
        
        if($list)
        {
            foreach ($list as $key => $val) 
            {
                if ($val['organ_id']) 
                {
                    $val['son'] = $this->getSonList($val['organ_id'],$otherWhere = '');
                }
                $array[] = $val;
            }
        }
        return $array;
    }
    /**
    *机构归属
    */
    public function sonParent()
    {
        $pid = I('get.pid') ? I('get.pid') : $this->belong_organ_parent();
        $data = $this->getList($pid,'organ.organ_id,organ.organ_name');
        
        if(empty($data) || ($this->belong_organ() != '0' && !$pid))
        {
            $data = array();
        }
        echo json_encode($data);
        exit;
    }
    /**
    *机构级别
    */
    public function organLv()
    {
        $where['organ_level_id'] = array('neq',1);
        $info = M('organLevel')->field('organ_level_id as level_id,organ_level_name as level_name')->where($where)->select();
        echo json_encode($info);
        exit;
    }
}