<?php
namespace App\Models\Backend\Contents;
use CodeIgniter\Model;

class DirectoratesModel extends Model {

	var $table = 'directorates';
	var $tableLang = 'directorates_lang';
	var $tableFiles = 'directorates_file';
	var $tableFilesLang = 'directorates_file_lang';
	var $tableCategories = 'directorate_categories';
	var $tableCategoriesLang = 'directorate_categories_lang';

	public function directoratesInfoModel(int $directorates_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('directorates_id', $directorates_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function directoratesLangModel(int $directorates_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.directorates_id,
						'.$this->tableLang.'.directorates_name,
						'.$this->tableLang.'.directorates_person_sub_title,
						'.$this->tableLang.'.directorates_slug,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.directorates_id', $directorates_id);
		$query->orderBy($this->tableLang.'.directorates_id', 'ASC');

		return $query->get()->getResult();
	}

	public function directoratesLangControlModel(int $directorates_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('directorates_id');

		$query->where('directorates_id', $directorates_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function directoratesFileInfoModel(int $directorates_file_id) {
		$query = $this->db->table($this->tableFiles);
		$query->select('*');

		$query->where('directorates_file_id', $directorates_file_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function directoratesFileLangModel(int $directorates_file_id) {
		$query = $this->db->table($this->tableFilesLang);
		$query->select($this->tableFilesLang.'.directorates_file_lang_id,
						'.$this->tableFilesLang.'.directorates_file_id,
						'.$this->tableFilesLang.'.directorates_file_name,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableFilesLang.'.lang_id', 'left');

		$query->where($this->tableFilesLang.'.directorates_file_id', $directorates_file_id);
		$query->orderBy($this->tableFilesLang.'.directorates_file_id', 'ASC');

		return $query->get()->getResult();
	}

	public function directoratesFileLangControlModel(int $directorates_file_id, int $lang_id) {
		$query = $this->db->table($this->tableFilesLang);
		$query->select('directorates_file_id');

		$query->where('directorates_file_id', $directorates_file_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function directorateCategoriesListModel(int $lang_id) {
		$query = $this->db->table($this->tableCategories);
		$query->select($this->tableCategories.'.directorate_category_id,
						'.$this->tableCategoriesLang.'.directorate_category_name');
		$query->join($this->tableCategoriesLang, $this->tableCategoriesLang.'.directorate_category_id = '.$this->tableCategories.'.directorate_category_id AND '.$this->tableCategoriesLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->tableCategories.'.status', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->tableCategories.'.directorate_category_id', 'ASC');

		return $query->get()->getResult();
	}
}
