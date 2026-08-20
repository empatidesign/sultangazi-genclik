<?php
namespace App\Models\Frontend\Contents;
use CodeIgniter\Model;

class ContactModel extends Model {

	var $DBGroup = 'application';
	var $table = 'contact_information';
	var $tableLang = 'contact_information_lang';
	var $tableContracts = 'contracts';
  var $tableContractsLang = 'contracts_lang';

	public function contactInformationListModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.*,
						'.$this->tableLang.'.contact_title,
						'.$this->tableLang.'.contact_address,
						'.$this->tableLang.'.contact_working_hours');
		$query->join($this->tableLang, $this->tableLang.'.contact_id = '.$this->table.'.contact_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.contact_default', 'ASC');

		return $query->get()->getResult();
	}

	public function agreementModel(int $lang_id) {
		$query = $this->db->table($this->tableContracts);
		$query->select($this->tableContracts.'.contract_id,
            '.$this->tableContractsLang.'.contract_name,
            '.$this->tableContractsLang.'.contract_description');
    $query->join($this->tableContractsLang, $this->tableContractsLang.'.contract_id = '.$this->tableContracts.'.contract_id AND '.$this->tableContractsLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->tableContracts.'.status', FORM_ACTIVE_NUMBER);
    $query->where($this->tableContracts.'.show_on_contact_page', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->tableContracts.'.contract_id', 'ASC');

		return $query->get()->getResult();
	}
}
