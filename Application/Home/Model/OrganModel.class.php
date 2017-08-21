<?php
namespace Home\Model;
use Think\Model;
class OrganModel extends Model
{
	/**
	*获取企业信息
	* $where 条件 array
	* $field 字段 string
	*/
	public function organList($where,$field, $flag = true)
	{
	    if ($flag == true) {
	        $where .= " AND (type.organ_type_name != '主机厂' or organ.parent_organ_id !=0)";
        }
		$sql = "select {$field} from tp_organ as organ
		 left join tp_organ_type as type on organ.organ_type_id = type.organ_type_id
		 left join tp_organ as parent on organ.parent_organ_id = parent.organ_id
		 left join tp_prov as province on organ.prov_code = province.prov_code
		 left join tp_city as city on organ.city_code = city.city_code
		 where {$where}
		 order by organ.organ_id desc";
		 $row = $this->query($sql);
		 return $row;
	}
	/**
	*获取机构信息
	* $where 条件 array
	*/
	public function sonList($where)
	{
		$sql = "select organ.*,province.prov_name,city.city_name,level.organ_level_name as level_name,type.organ_type_name,parent.organ_name as organ_parent from tp_organ as organ
		 left join tp_organ_level as level on organ.organ_level_id = level.organ_level_id
		 left join tp_organ_type as type on organ.organ_type_id = type.organ_type_id
		 left join tp_organ as parent on organ.parent_organ_id = parent.organ_id
		 left join tp_prov as province on organ.prov_code = province.prov_code
		 left join tp_city as city on organ.city_code = city.city_code
		 where {$where} order by organ.organ_id desc";
		 $row = $this->query($sql);
		 return $row;
	}

}