<?php
namespace Home\Model;
use Think\Model;
class AccountRoleModel extends Model
{
	protected $trueTableName  = 'tp_account_role_rel';

	/**
	*获取帐号角色关系列表
	*/
	public function accountRoleList()
	{
		$sql = "select acc_role.account_id,acc_role.role_id,role.role_name from tp_account_role_rel as acc_role
		 left join tp_role as role on acc_role.role_id = role.role_id";
		$row = $this->query($sql);
		return $row;
	}
	/**
	*添加帐号角色关系
	* $data 角色id array
	* $id 帐号id
	*/
	public function addAccountRole($data,$id)
	{
		$sql = "insert into tp_account_role_rel (account_id,role_id) values ";
		foreach ($data as $key => $val) 
		{
			$sql .= "('{$id}','{$val}')".",";
		}
		$sql = substr($sql,0,-1);
		$row = $this->execute($sql);
		return $row;
	}


}