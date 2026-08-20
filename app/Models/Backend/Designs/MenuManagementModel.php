<?php
namespace App\Models\Backend\Designs;
use CodeIgniter\Model;

class MenuManagementModel extends Model {

	var $table = 'menus';
	var $tableLang = 'menus_lang';

	public function menuManagementInfoModel(int $menu_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('menu_id', $menu_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function menuManagementLangModel(int $menu_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.menu_id,
						'.$this->tableLang.'.menu_name,
						'.$this->tableLang.'.menu_link,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.menu_id', $menu_id);
		$query->orderBy($this->tableLang.'.lang_id');

		return $query->get()->getResult();
	}

	public function menuManagementLangControlModel(int $menu_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('menu_id');

		$query->where('menu_id', $menu_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	/*****************************************************/

	public function menuManagementParentModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.menu_id AS id,
						'.$this->table.'.menu_parent_id AS parent_id,
						'.$this->tableLang.'.lang_id,
						'.$this->tableLang.'.menu_name AS name');
		$query->join($this->tableLang, $this->tableLang.'.menu_id = '.$this->table.'.menu_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->orderBy($this->table.'.menu_order', 'ASC');
		$result = $query->get()->getResultArray();

		$list = [];
		if (isNotNull($result)) {
			foreach ($result as $data) {
				$list[$data['parent_id']][] = $data;
			}
		}

		return parentMenuNested($list);
	}

	public function menuManagementNestableModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.*,
						'.$this->tableLang.'.lang_id,
						'.$this->tableLang.'.menu_name');
		$query->join($this->tableLang, $this->tableLang.'.menu_id = '.$this->table.'.menu_id', 'left');

		$query->where($this->tableLang.'.lang_id', $lang_id);
		$query->orderBy($this->table.'.menu_order', 'ASC');

		return $query->get()->getResult();
	}

	/*****************************************************/

	public function menuManagementBreadchumbModel(int $menu_id, int $lang_id, array $crumbs = []) {
			$query = $this->db->table($this->table);
			$query->select($this->table.'.menu_id,
							'.$this->table.'.menu_parent_id,
							'.$this->tableLang.'.menu_name');
			$query->join($this->tableLang, $this->tableLang.'.menu_id = '.$this->table.'.menu_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

			$query->where($this->table.'.menu_id', $menu_id);
			$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);

			$result = $query->get()->getRowArray();
			if (isNotNull($result)) {
				$crumbs[] = $result['menu_name'];

		    if ($result['menu_parent_id'] == MENU_MANAGEMENT_PARENT_MENU) {
		        krsort($crumbs);
		        return implode(BREADCRUMB_SEPERATOR, $crumbs);
		    } else {
		        return $this->menuManagementBreadchumbModel($result['menu_parent_id'], $lang_id, $crumbs);
		    }
			}
	}

	/*****************************************************/

	public function menuManagementPagesListModel(int $lang_id) {
		$query = $this->db->table('pages');
		$query->select('pages.page_id,
						pages_lang.page_name');
		$query->join('pages_lang', 'pages_lang.page_id = pages.page_id', 'left');

		$query->where('pages_lang.lang_id', $lang_id);
		$query->where('pages.status', FORM_ACTIVE_NUMBER);
		$query->orderBy('pages_lang.page_name', 'ASC');

		return $query->get()->getResult();
	}

	public function menuManagementSultangaziContentsListModel(int $lang_id) {
		$query = $this->db->table('sultangazi_contents');
		$query->select('sultangazi_contents.content_id,
						sultangazi_contents_lang.content_name');
		$query->join('sultangazi_contents_lang', 'sultangazi_contents_lang.content_id = sultangazi_contents.content_id', 'left');

		$query->where('sultangazi_contents_lang.lang_id', $lang_id);
		$query->where('sultangazi_contents.status', FORM_ACTIVE_NUMBER);
		$query->orderBy('sultangazi_contents_lang.content_name', 'ASC');

		return $query->get()->getResult();
	}

	public function menuManagementContractsListModel(int $lang_id) {
		$query = $this->db->table('contracts');
		$query->select('contracts.contract_id,
						contracts_lang.contract_name');
		$query->join('contracts_lang', 'contracts_lang.contract_id = contracts.contract_id', 'left');

		$query->where('contracts_lang.lang_id', $lang_id);
		$query->where('contracts.status', FORM_ACTIVE_NUMBER);
		$query->orderBy('contracts_lang.contract_name', 'ASC');

		return $query->get()->getResult();
	}

	public function menuManagementProjectsListModel(int $lang_id) {
		$query = $this->db->table('projects');
		$query->select('projects.project_id,
						projects_lang.project_name');
		$query->join('projects_lang', 'projects_lang.project_id = projects.project_id', 'left');

		$query->where('projects_lang.lang_id', $lang_id);
		$query->where('projects.status', FORM_ACTIVE_NUMBER);
		$query->orderBy('projects_lang.project_name', 'ASC');

		return $query->get()->getResult();
	}

	public function menuManagementServicesListModel(int $lang_id) {
		$query = $this->db->table('services');
		$query->select('services.service_id,
						services_lang.service_name,
						(CASE WHEN services.service_type = "'.SERVICES_TYPE_1.'" THEN "'.lang('AdminContents.services.general.types.option1').'"
									WHEN services.service_type = "'.SERVICES_TYPE_2.'" THEN "'.lang('AdminContents.services.general.types.option2').'"
									WHEN services.service_type = "'.SERVICES_TYPE_3.'" THEN "'.lang('AdminContents.services.general.types.option3').'" ELSE NULL END) AS service_type_name');
		$query->join('services_lang', 'services_lang.service_id = services.service_id', 'left');

		$query->where('services_lang.lang_id', $lang_id);
		$query->where('services.status', FORM_ACTIVE_NUMBER);
		$query->orderBy('services_lang.service_name', 'ASC');

		return $query->get()->getResult();
	}

	public function menuManagementPresidentContentsListModel(int $lang_id) {
		$query = $this->db->table('president_contents');
		$query->select('president_contents.president_content_id,
						president_contents_lang.president_content_name');
		$query->join('president_contents_lang', 'president_contents_lang.president_content_id = president_contents.president_content_id', 'left');

		$query->where('president_contents_lang.lang_id', $lang_id);
		$query->where('president_contents.status', FORM_ACTIVE_NUMBER);
		$query->orderBy('president_contents_lang.president_content_name', 'ASC');

		return $query->get()->getResult();
	}
}
