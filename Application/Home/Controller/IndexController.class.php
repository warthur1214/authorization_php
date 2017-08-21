<?php
namespace Home\Controller;
use Home\Common\MyController;
class IndexController extends MyController 
{
    function __construct()
    {
        parent::__construct();
    }
    /**
    *首页 frame
    */
    public function index()
    {
        $this->display('Index/index');
    }
    /**
    *frame头部 
    */
    public function top()
    {
        $this->display('Index/top');
    }
    /**
    *frame选项栏
    */
    public function menu()
    {
        //获取当前帐号的角色的模块信息
        $module = $this->login_role_module();
        $where = array('parent_module_id' => 0,'is_visible' => 1,'platform_id' => C("PLATFORM_ID"));
        $menu = $this->moduleDB->field('module_id,module_name,module_url')->where($where)->order('sort_no desc,module_id asc')->select();
        foreach ($menu as $key => $val) 
        {
            $where['module_id'] = array('in',$module);
            $where['parent_module_id'] = array('eq',$val['module_id']);
            $where['is_visible'] = array('eq',1);
            $menu[$key]['menu_two'] = $this->moduleDB->field('module_id,module_name,module_url')->where($where)->order('sort_no desc,module_id asc')->select();
        }
        $this->assign('menu',$menu);
        $this->display('Index/menu');
    }
    /**
    *frame正文
    */
    public function main()
    {
        //获得当前时间
        $week = array("日","一","二","三","四","五","六");
        $date = date('Y年m月d日',time()).'，星期'.$week[date('w')].' 北京时间：'.date('H:i:s',time());
        //获取当前角色
        $real_name = $this->accountDB->field('real_name')->where(array('account_id' => session('auth_account_id')))->find();
        $this->assign('real_name',$real_name['real_name']);
        $this->assign('date',$date);
        $this->display('Index/main');
    }
}