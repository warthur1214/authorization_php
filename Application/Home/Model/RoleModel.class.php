<?php
namespace Home\Model;
use Think\Model;
class RoleModel extends Model
{
	/**
	*获取角色成员信息
	* $where 条件 array
	*/
	public function roleAccountList($where)
	{
		$sql = "select organ.organ_id,organ.organ_name,acc.account_id,acc.account_name from tp_organ as organ
		 left join tp_account as acc on organ.organ_id = acc.organ_id
		 where {$where} order by organ.organ_id desc";
		 $row = $this->query($sql);
		 return $row;
	}
	/**
	*添加角色关系
	* $table 表名
	* $field 字段名
	* $data 关系id array
	* $id 角色id
	*/
	public function addRoleContact($table,$field,$data,$id)
	{
		$sql = "insert into {$table} {$field} values ";
		foreach ($data as $key => $val) 
		{
			$sql .= "('{$id}','{$val}')".",";
		}
		$sql = substr($sql,0,-1);
		$row = $this->execute($sql);
		return $row;
	}
	/**
	*添加角色帐号关系
	* $data 帐号id array
	* $id 角色id
	*/
	public function addRoleAccount($data,$id)
	{
		$sql = "insert into tp_account_role_rel (account_id,role_id) values ";
		foreach ($data as $key => $val) 
		{
			$sql .= "('{$val}','{$id}')".",";
		}
		$sql = substr($sql,0,-1);
		$row = $this->execute($sql);
		return $row;
	}

}