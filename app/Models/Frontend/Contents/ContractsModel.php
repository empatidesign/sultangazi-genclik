<?php
namespace App\Models\Frontend\Contents;
use CodeIgniter\Model;

class ContractsModel extends Model {

	var $table = 'contracts';
	var $tableLang = 'contracts_lang';

	public function contractsInfoModel(string $slug = NULL, int $contract_id, int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->tableLang.'.contract_name,
						'.$this->tableLang.'.contract_description,
						'.$this->tableLang.'.contract_meta_title,
						'.$this->tableLang.'.contract_meta_keywords,
						'.$this->tableLang.'.contract_meta_description');
		$query->join($this->tableLang, $this->tableLang.'.contract_id = '.$this->table.'.contract_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);

		if (isNotNull($slug)) {
			$query->where($this->tableLang.'.contract_slug', $slug);
		}

		$query->where($this->table.'.contract_id', $contract_id);
		$query->where($this->tableLang.'.contract_name !=', '');
		$query->limit(1);

		return $query->get()->getRow();
	}
}
