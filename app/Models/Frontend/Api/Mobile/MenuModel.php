<?php
namespace App\Models\Frontend\Api\Mobile;
use CodeIgniter\Model;

class MenuModel extends Model {

	var $table = 'menus';
	var $tableLang = 'menus_lang';
	protected $tablePages = 'pages';
	protected $tablePagesLang = 'pages_lang';
	protected $tableContracts = 'contracts';
	protected $tableContractsLang = 'contracts_lang';
	protected $tableServices = 'services';
	protected $tableServicesLang = 'services_lang';
	protected $tablePresidentContents = 'president_contents';
	protected $tablePresidentContentsLang = 'president_contents_lang';
	protected $tableSultangaziContents = 'sultangazi_contents';
	protected $tableSultangaziContentsLang = 'sultangazi_contents_lang';

	public function menuListModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.menu_id,
						'.$this->table.'.menu_parent_id,
						'.$this->table.'.menu_type,
						'.$this->table.'.menu_order,
						'.$this->table.'.menu_page_id,
						'.$this->table.'.menu_sultangazi_content_id,
						'.$this->table.'.menu_contract_id,
						'.$this->table.'.menu_service_id,
						'.$this->table.'.menu_president_content_id,
						'.$this->table.'.menu_target,
						'.$this->table.'.menu_location,
						'.$this->tableLang.'.menu_name,
						'.$this->tableLang.'.menu_link');
		$query->join($this->tableLang, $this->tableLang.'.menu_id = '.$this->table.'.menu_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status_mobile', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.menu_order', 'ASC');

		return $query->get()->getResult();
	}

	public function menuLinkModel(int $menu_type, string $menu_link, int $page_id, int $sultangazi_content_id, int $contract_id, int $service_id, int $president_content_id, int $lang_id) {

		$return = NULL;
		if ($menu_type == MENU_MANAGEMENT_TYPE_PAGES) { // Pages

			$query = $this->db->table($this->tablePages);
			$query->select($this->tablePagesLang.'.page_id,
							'.$this->tablePagesLang.'.page_slug');
			$query->join($this->tablePagesLang, $this->tablePagesLang.'.page_id = '.$this->tablePages.'.page_id AND '.$this->tablePagesLang.'.lang_id = '.$lang_id, 'left');

			$query->where($this->tablePages.'.page_id', $page_id);
			$query->limit(1);

			$result = $query->get()->getRow();
			if (isNotNull($result)) {
				$return = web_url(WEB_URL_PAGES.'/'.$result->page_slug.'/'.$result->page_id);
			}

		} elseif ($menu_type == MENU_MANAGEMENT_TYPE_SULTANGAZI_CONTENTS) { // Sultangazi Contents

			$query = $this->db->table($this->tableSultangaziContents);
			$query->select($this->tableSultangaziContents.'.content_id,
							'.$this->tableSultangaziContentsLang.'.content_slug');
			$query->join($this->tableSultangaziContentsLang, $this->tableSultangaziContentsLang.'.content_id = '.$this->tableSultangaziContents.'.content_id AND '.$this->tableSultangaziContentsLang.'.lang_id = '.$lang_id, 'left');

			$query->where($this->tableSultangaziContents.'.content_id', $sultangazi_content_id);
			$query->limit(1);

			$result = $query->get()->getRow();
			if (isNotNull($result)) {
				$return = web_url(WEB_URL_SULTANGAZI.'/'.WEB_URL_SULTANGAZI_CONTENTS.'/'.$result->content_slug.'/'.$result->content_id);
			}

		} elseif ($menu_type == MENU_MANAGEMENT_TYPE_CONTRACTS) { // Contract

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

		} elseif ($menu_type == MENU_MANAGEMENT_TYPE_SERVICES) { // Services

			$query = $this->db->table($this->tableServices);
			$query->select($this->tableServices.'.service_id,
							'.$this->tableServicesLang.'.service_slug');
			$query->join($this->tableServicesLang, $this->tableServicesLang.'.service_id = '.$this->tableServices.'.service_id AND '.$this->tableServicesLang.'.lang_id = '.$lang_id, 'left');

			$query->where($this->tableServices.'.service_id', $service_id);
			$query->limit(1);

			$result = $query->get()->getRow();
			if (isNotNull($result)) {
				$return = web_url(WEB_URL_SERVICES.'/'.$result->service_slug.'/'.$result->service_id);
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
