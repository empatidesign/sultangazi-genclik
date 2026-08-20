<?php
namespace App\Models\Backend\Contents;
use CodeIgniter\Model;

class OrganizationChartModel extends Model {

	var $table = 'organization_chart';
	var $tableLang = 'organization_chart_lang';
	var $tablePresidentGeneralInformation = 'president_general_information';
	var $tableVicePresidents = 'vice_presidents';
	var $tableDirectorates = 'directorates';
	var $tableDirectoratesLang = 'directorates_lang';

	public function organizationChartInfoModel(int $organization_chart_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('organization_chart_id', $organization_chart_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function organizationChartLangModel(int $organization_chart_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.organization_chart_id,
						'.$this->tableLang.'.organization_chart_name,
						'.$this->tableLang.'.organization_chart_sub_title,
						'.$this->tableLang.'.organization_chart_link,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.organization_chart_id', $organization_chart_id);
		$query->orderBy($this->tableLang.'.lang_id');

		return $query->get()->getResult();
	}

	public function organizationChartLangControlModel(int $organization_chart_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('organization_chart_id');

		$query->where('organization_chart_id', $organization_chart_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	/*****************************************************/

	public function organizationChartParentModel(int $lang_id){
		$query = $this->db->table($this->table);
		$query->select($this->table.'.organization_chart_id AS id,
						'.$this->table.'.organization_chart_parent_id AS parent_id,
						'.$this->tableLang.'.lang_id,
						'.$this->tableLang.'.organization_chart_name AS name');
		$query->join($this->tableLang, $this->tableLang.'.organization_chart_id = '.$this->table.'.organization_chart_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->orderBy($this->table.'.organization_chart_order', 'ASC');
		$result = $query->get()->getResultArray();

		$list = [];
		if (isNotNull($result)) {
			foreach ($result as $data) {
				$list[$data['parent_id']][] = $data;
			}
		}

		return parentMenuNested($list);
	}

	public function organizationChartNestableModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.*,
						'.$this->tableLang.'.lang_id,
						'.$this->tableLang.'.organization_chart_name,
						'.$this->tableLang.'.organization_chart_sub_title');
		$query->join($this->tableLang, $this->tableLang.'.organization_chart_id = '.$this->table.'.organization_chart_id', 'left');

		$query->where($this->tableLang.'.lang_id', $lang_id);
		$query->orderBy($this->table.'.organization_chart_order', 'ASC');

		return $query->get()->getResult();
	}

	/*****************************************************/

	public function organizationChartBreadchumbModel(int $organization_chart_id, int $lang_id, array $crumbs = []) {
			$query = $this->db->table($this->table);
			$query->select($this->table.'.organization_chart_id,
							'.$this->table.'.organization_chart_parent_id,
							'.$this->tableLang.'.organization_chart_name');
			$query->join($this->tableLang, $this->tableLang.'.organization_chart_id = '.$this->table.'.organization_chart_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

			$query->where($this->table.'.organization_chart_id', $organization_chart_id);
			$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);

			$result = $query->get()->getRowArray();
			if (isNotNull($result)) {
				$crumbs[] = $result['organization_chart_name'];

		    if ($result['organization_chart_parent_id'] == MENU_MANAGEMENT_PARENT_MENU) {
		        krsort($crumbs);
		        return implode(BREADCRUMB_SEPERATOR, $crumbs);
		    } else {
		        return $this->organizationChartBreadchumbModel($result['organization_chart_parent_id'], $lang_id, $crumbs);
		    }
			}
	}

	/*****************************************************/

	public function organizationChartPresidentGeneralInformationListModel() {
		$query = $this->db->table($this->tablePresidentGeneralInformation);
		$query->select('president_general_information_id,
						president_name_surname');

		$query->orderBy('president_name_surname', 'ASC');

		return $query->get()->getResult();
	}

	public function organizationChartVicePresidentsListModel() {
		$query = $this->db->table($this->tableVicePresidents);
		$query->select('vice_president_id,
						vice_president_name,
						vice_president_surname');

		$query->where('status', FORM_ACTIVE_NUMBER);
		$query->orderBy('vice_president_name ASC, vice_president_surname ASC');

		return $query->get()->getResult();
	}

	public function organizationChartDirectoratesListModel(int $lang_id) {
		$query = $this->db->table($this->tableDirectorates);
		$query->select($this->tableDirectorates.'.directorates_id,
						'.$this->tableDirectoratesLang.'.directorates_name');
		$query->join($this->tableDirectoratesLang, $this->tableDirectoratesLang.'.directorates_id = '.$this->tableDirectorates.'.directorates_id', 'left');

		$query->where($this->tableDirectoratesLang.'.lang_id', $lang_id);
		$query->where($this->tableDirectorates.'.status', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->tableDirectoratesLang.'.directorates_name', 'ASC');

		return $query->get()->getResult();
	}
}
