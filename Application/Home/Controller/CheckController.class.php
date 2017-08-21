<?php
namespace Home\Controller;
use Home\Common\MyController;
class CheckController extends MyController
{
    function __construct()
    {
        parent::__construct();
    }
    public function isUse($key,$isAjax)
    {
        //检查是否存在该模块关键词
        $keyVal = $this->moduleDB->field('module_id')->where(array('matched_key' => $key,'platform_id' => C("PLATFORM_ID")))->find();
        if(empty($keyVal)) $this->showMsg('无权限访问',$isAjax);
        //获取当前帐号的角色的模块权限
        $roleM = explode(',',$this->login_role_module());
        if( ! in_array($keyVal['module_id'],$roleM)) $this->showMsg('无访问权限',$isAjax);

    }
    public function showMsg($msg,$isAjax)
    {
        if($isAjax == 1)
        {
            echo json_encode(array('status' => 0,'msg' => $msg));
            exit;
        }
        $this->assign('msg',$msg);
        $this->display('Check/show');
        exit;
    }
}