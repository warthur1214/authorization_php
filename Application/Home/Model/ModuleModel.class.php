<?php
namespace Home\Model;
use Think\Model;
class ModuleModel extends Model
{
	/**
	*获取模块单个信息
	* $where 条件 array
	*/
	public function getInfo($where)
	{
		return $this->where($where)->find();
	}
    /**
    *获取模块信息
    *$where 条件 array
    */
	public function getData($where)
	{
		return $this->where($where)->order('sort_no asc,module_id asc')->select();
	}
	/**
	*添加模块信息
	* $array 添加数据 array
	*/
	public function addModule($array)
	{
		return $this->add($array);
	}
	/**
	*修改模块信息
	* $where 条件 array
	* $array 修改数据 array
	*/
	public function editModule($where,$array)
	{
		return $this->where($where)->data($array)->save();
	}
	/**
	*删除模块信息
	* $id 模块主键
	*/
	public function delModule($id)
	{
		return $this->delete($id);
	}
	/**
	*为设置权限获取模块信息
	* $where 条件 array
	*/
	public function getModule($where)
	{
		return $this->where($where)->field('module_id,module_name,parent_module_id,display_level')->order('module_id')->select();
	}
}