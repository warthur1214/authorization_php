<?php
namespace Home\Model;
use Think\Model;
class AccountModel extends Model
{
	/**
	*获取账号信息
	* $where 条件 array
	* $field 指定字段
	*/
	public function accountList($where)
	{
		$sql = "select acc.account_id,acc.account_name,acc.real_name,acc.belonged_organ_id,
                organ.organ_name,acc.last_login_time,acc.is_available from tp_account as acc
		 left join tp_organ as organ on acc.belonged_organ_id = organ.organ_id
		 where {$where} order by acc.account_id desc";
		$row = $this->query($sql);
		return $row;
	}

    public function accountListByRole($where)
    {
        $sql = "select acc.account_id,acc.account_name,acc.real_name,acc.belonged_organ_id,
                organ.organ_name,acc.last_login_time as login_time,acc.is_available from tp_account as acc
		 left join tp_organ as organ on acc.belonged_organ_id = organ.organ_id
		 where {$where} order by acc.account_id desc";
        $row = $this->query($sql);
        return $row;
    }
	/**
	*获取平台名称
	*/
	public function getSystem()
	{
		$sql = "select system.platform_name,role.role_id from tp_module as module
		 left join tp_platform as system on module.platform_id = system.platform_id
		 left join tp_role_module_rel as rolem on module.module_id = rolem.module_id
		 left join tp_role as role on rolem.role_id = role.role_id
		 group by module.platform_id,role.role_id";
		$row = $this->query($sql);
		return $row;
	}
}