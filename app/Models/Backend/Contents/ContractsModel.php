<?php
namespace App\Models\Backend\Contents;
use CodeIgniter\Model;

class ContractsModel extends Model {

	var $table = 'contracts';
	var $tableLang = 'contracts_lang';

	public function contractsInfoModel(int $contract_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('contract_id', $contract_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function contractsLangModel(int $contract_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.contract_id,
						'.$this->tableLang.'.contract_name,
						'.$this->tableLang.'.contract_description,
						'.$this->tableLang.'.contract_meta_title,
						'.$this->tableLang.'.contract_meta_keywords,
						'.$this->tableLang.'.contract_meta_description,
						'.$this->tableLang.'.contract_slug,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.contract_id', $contract_id);
		$query->orderBy($this->tableLang.'.contract_id');

		return $query->get()->getResult();
	}

	public function contractsLangControlModel(int $contract_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('contract_id');

		$query->where('contract_id', $contract_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
