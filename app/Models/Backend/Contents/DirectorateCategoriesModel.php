<?php
namespace App\Models\Backend\Contents;
use CodeIgniter\Model;

class DirectorateCategoriesModel extends Model {

	var $table = 'directorate_categories';
	var $tableLang = 'directorate_categories_lang';
	var $tableDirectoratesFile = 'directorates_file';

	public function directorateCategoriesInfoModel(int $directorate_category_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('directorate_category_id', $directorate_category_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function directorateCategoriesLangModel(int $directorate_category_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.directorate_category_id,
						'.$this->tableLang.'.directorate_category_name,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.directorate_category_id', $directorate_category_id);
		$query->orderBy($this->tableLang.'.directorate_category_id', 'ASC');

		return $query->get()->getResult();
	}

	public function directorateCategoriesLangControlModel(int $directorate_category_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('directorate_category_id');

		$query->where('directorate_category_id', $directorate_category_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function directoratesFileControlModel(int $directorate_category_id) {
		$query = $this->db->table($this->tableDirectoratesFile);
		$query->select('directorate_category_id');

		$query->where('directorate_category_id', $directorate_category_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
