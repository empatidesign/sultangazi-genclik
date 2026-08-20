<?php
namespace App\Models\Frontend\Contents;
use CodeIgniter\Model;

class OrganizationChartModel extends Model {

	var $table = 'organization_chart';
	var $tableLang = 'organization_chart_lang';

	public function organizationChartFirstListModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.organization_chart_order,
						'.$this->tableLang.'.organization_chart_name,
						'.$this->tableLang.'.organization_chart_sub_title,
						'.$this->tableLang.'.organization_chart_link');
		$query->join($this->tableLang, $this->tableLang.'.organization_chart_id = '.$this->table.'.organization_chart_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->table.'.organization_chart_parent_id', MENU_MANAGEMENT_PARENT_MENU);
		$query->where($this->table.'.organization_chart_level', ORGANIZATION_CHART_LEVEL_1);
		$query->orderBy($this->table.'.organization_chart_order', 'ASC');

		return $query->get()->getResult();
	}

	public function organizationChartSecondListModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.organization_chart_order,
						'.$this->tableLang.'.organization_chart_name,
						'.$this->tableLang.'.organization_chart_sub_title,
						'.$this->tableLang.'.organization_chart_link');
		$query->join($this->tableLang, $this->tableLang.'.organization_chart_id = '.$this->table.'.organization_chart_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->table.'.organization_chart_parent_id', MENU_MANAGEMENT_PARENT_MENU);
		$query->where($this->table.'.organization_chart_level', ORGANIZATION_CHART_LEVEL_2);
		$query->orderBy($this->table.'.organization_chart_order', 'ASC');

		return $query->get()->getResult();
	}

	public function organizationChartThirdListModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.organization_chart_order,
						'.$this->tableLang.'.organization_chart_name,
						'.$this->tableLang.'.organization_chart_sub_title,
						'.$this->tableLang.'.organization_chart_link');
		$query->join($this->tableLang, $this->tableLang.'.organization_chart_id = '.$this->table.'.organization_chart_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->table.'.organization_chart_parent_id', MENU_MANAGEMENT_PARENT_MENU);
		$query->where($this->table.'.organization_chart_level', ORGANIZATION_CHART_LEVEL_3);
		$query->orderBy($this->table.'.organization_chart_order', 'ASC');

		return $query->get()->getResult();
	}
}
