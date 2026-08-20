<?php
namespace App\Models\Frontend\Contents;
use CodeIgniter\Model;

class CorporateModel extends Model {

	protected $tableMenus = 'menus';
	protected $tableMenusLang = 'menus_lang';
	protected $tablePages = 'pages';
	protected $tablePagesLang = 'pages_lang';
	protected $tableContracts = 'contracts';
	protected $tableContractsLang = 'contracts_lang';
	protected $tableProjects = 'projects';
	protected $tableProjectsLang = 'projects_lang';
	protected $tableServices = 'services';
	protected $tableServicesLang = 'services_lang';
	protected $tablePresidentContents = 'president_contents';
	protected $tablePresidentContentsLang = 'president_contents_lang';
	protected $tableSultangaziContents = 'sultangazi_contents';
	protected $tableSultangaziContentsLang = 'sultangazi_contents_lang';

	public function leftMenuSlugInfoModel(string $segment, int $lang_id, int $contract_id = NULL, int $president_content_id = NULL, int $projects_id = null) {
		$query = $this->db->table($this->tableMenus);
		$query->select('p.menu_id,
						p_lang.menu_name');
		$query->join($this->tableMenusLang, $this->tableMenusLang.'.menu_id = '.$this->tableMenus.'.menu_id AND '.$this->tableMenusLang.'.lang_id = '.$lang_id, 'left');
		$query->join($this->tableMenus.' AS p', 'p.menu_id = '.$this->tableMenus.'.menu_parent_id', 'left');
		$query->join($this->tableMenusLang.' AS p_lang', 'p_lang.menu_id = p.menu_id AND p_lang.lang_id = '.$lang_id, 'left');

		$query->where($this->tableMenus.'.status', FORM_ACTIVE_NUMBER);

		if ($segment == 'sozlesmeler' && isNotNull($contract_id)) {
			$query->where($this->tableMenus.'.menu_contract_id', $contract_id);
		} elseif ($segment == 'projeler' && isNotNull($projects_id)) {
			$query->where($this->tableMenus.'.menu_project_id', $projects_id);
		} elseif ($segment == 'baskan' && isNotNull($president_content_id)) {
			$query->where($this->tableMenus.'.menu_president_content_id', $president_content_id);
		} else {
			$query->where($this->tableMenusLang.'.menu_link', $segment);
		}

		$query->limit(1);

		$result = $query->get()->getRow();

		$menu = [];
		if (isNotNull($result)) {

			// Submenu
			$submenu = [];
			$submenu_sql = isNotNull($result->menu_id) ? $this->leftMenuListModel($result->menu_id, $lang_id) : NULL;
			if (isNotNull($submenu_sql)) {
				foreach ($submenu_sql as $row) {

					$active = NULL;
					if ($segment == 'sozlesmeler' && isNotNull($contract_id)) {
						$active = $contract_id == $row->menu_contract_id ? TRUE : FALSE;
					} elseif ($segment == 'projeler' && isNotNull($projects_id)) {
						$active = $projects_id == $row->menu_project_id ? TRUE : FALSE;
					} elseif ($segment == 'pages' && isNotNull($page_id)) {
						$active = $page_id == $row->menu_page_id ? TRUE : FALSE;
					} elseif ($segment == 'sultangazi_contents' && isNotNull($sultangazi_content_id)) {
						$active = $sultangazi_content_id == $row->menu_sultangazi_content_id ? TRUE : FALSE;
					} elseif ($segment == 'baskan' && isNotNull($president_content_id)) {
						$active = $president_content_id == $row->menu_president_content_id ? TRUE : FALSE;
					} else {
						$active = $segment == $row->menu_link ? TRUE : FALSE;
					}

					$submenu[] = [
						'menu_name' => $row->menu_name,
						'menu_link' => $this->menuLinkModel($row->menu_type, $row->menu_link, $row->menu_page_id, $row->menu_sultangazi_content_id, $row->menu_contract_id, $row->menu_service_id, $row->menu_president_content_id, $row->menu_project_id, $lang_id),
						'menu_target' => $row->menu_target,
						'active' => $active
					];
				}
			}

			$menu = [
				'menu_name' => $result->menu_name,
				'submenu' => $submenu
			];

		}

		return $menu;
	}

	public function leftMenuListModel(?string $menu_parent_id, int $lang_id) {
		if (!isNotNull($menu_parent_id)) {
			return NULL;
		}

		$query = $this->db->table($this->tableMenus);
		$query->select($this->tableMenus.'.*,
						'.$this->tableMenusLang.'.menu_name,
						'.$this->tableMenusLang.'.menu_link');
		$query->join($this->tableMenusLang, $this->tableMenusLang.'.menu_id = '.$this->tableMenus.'.menu_id AND '.$this->tableMenusLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->tableMenus.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableMenus.'.menu_parent_id', $menu_parent_id);
		$query->orderBy($this->tableMenus.'.menu_order', 'ASC');

		return $query->get()->getResult();
	}

	public function menuLinkModel(int $menu_type, string $menu_link, int $page_id, int $sultangazi_content_id, int $contract_id, int $service_id, int $president_content_id, int $project_id, int $lang_id) {

		$return = NULL;
		if ($menu_type == MENU_MANAGEMENT_TYPE_CONTRACTS) { // Contract

			$query = $this->db->table($this->tableContracts);
			$query->select($this->tableContracts.'.contract_id,
							'.$this->tableContractsLang.'.contract_slug');
			$query->join($this->tableContractsLang, $this->tableContractsLang.'.contract_id = '.$this->tableContracts.'.contract_id AND '.$this->tableContractsLang.'.lang_id = '.$lang_id, 'left');

			$query->where($this->tableContracts.'.contract_id', $contract_id);
			$query->limit(1);

			$result = $query->get()->getRow();
			if (isNotNull($result)) {
				$return = web_url(WEB_URL_CONTRACTS.'/'.$result->contract_slug.'/'.$result->contract_id);
			}


		} elseif ($menu_type == MENU_MANAGEMENT_TYPE_PRESIDENT_CONTENTS) { // President Contents

			$query = $this->db->table($this->tablePresidentContents);
			$query->select($this->tablePresidentContents.'.president_content_id,
							'.$this->tablePresidentContentsLang.'.president_content_slug');
			$query->join($this->tablePresidentContentsLang, $this->tablePresidentContentsLang.'.president_content_id = '.$this->tablePresidentContents.'.president_content_id AND '.$this->tablePresidentContentsLang.'.lang_id = '.$lang_id, 'left');

			$query->where($this->tablePresidentContents.'.president_content_id', $president_content_id);
			$query->limit(1);

			$result = $query->get()->getRow();
			if (isNotNull($result)) {
				$return = web_url(WEB_URL_PRESIDENT.'/'.$result->president_content_slug.'/'.$result->president_content_id);
			}

		} elseif ($menu_type == MENU_MANAGEMENT_TYPE_PROJECTS) { // Projects

			$query = $this->db->table($this->tableProjects);
			$query->select($this->tableProjects.'.project_id,
							'.$this->tableProjectsLang.'.project_slug');
			$query->join($this->tableProjectsLang, $this->tableProjectsLang.'.project_id = '.$this->tableProjects.'.project_id AND '.$this->tableProjectsLang.'.lang_id = '.$lang_id, 'left');

			$query->where($this->tableProjects.'.project_id', $project_id);
			$query->limit(1);

			$result = $query->get()->getRow();
			if (isNotNull($result)) {
				$return = web_url(WEB_URL_PROJECTS.'/'.$result->project_slug.'/'.$result->project_id);
			}

		} elseif ($menu_type == MENU_MANAGEMENT_TYPE_LINK) { // Link

			if (substr($menu_link, 0, 8) == 'https://' || substr($menu_link, 0, 8) == 'http://') {
				$menu_link = $menu_link;
			} else {
				$menu_link = web_url($menu_link);
			}

			$return = $menu_link;
		}

		return $return;
	}
}
