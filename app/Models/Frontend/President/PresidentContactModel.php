<?php

namespace App\Models\Frontend\President;

use CodeIgniter\Model;

class PresidentContactModel extends Model
{
	var $DBGroup = 'application';
	var $tableContracts = 'contracts';
	var $tableContractsLang = 'contracts_lang';

	public function agreementModel(int $lang_id)
	{
		$query = $this->db->table($this->tableContracts);
		$query->select($this->tableContracts . '.contract_id,
            ' . $this->tableContractsLang . '.contract_name,
            ' . $this->tableContractsLang . '.contract_description');
		$query->join($this->tableContractsLang, $this->tableContractsLang . '.contract_id = ' . $this->tableContracts . '.contract_id AND ' . $this->tableContractsLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableContracts . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableContracts . '.show_on_president_contact_page', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->tableContracts . '.contract_id', 'ASC');

		return $query->get()->getResult();
	}
}
