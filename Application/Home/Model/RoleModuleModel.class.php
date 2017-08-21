<?php
namespace Home\Model;
use Think\Model;
class RoleModuleModel extends Model
{
	protected $trueTableName  = 'tp_role_module_rel';
    /**
    *获取角色模块关系信息
    *$where 条件 array
    */
	public function getRoleM($where)
	{
		return $this->where($where)->select();
	}
	/**
	*添加角色模块关系信息
	* $array 添加数据 array
	*/
	public function addRoleM($array)
	{
		return $this->add($array);
	}
	/**
	*删除角色模块关系信息
	* $where 条件 array
	*/
	public function delRoleM($where)
	{
		return $this->where($where)->delete();
	}
}