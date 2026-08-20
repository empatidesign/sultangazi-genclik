<?php
namespace App\Models\Backend\News;
use CodeIgniter\Model;

class AnnouncementsModel extends Model {

	var $table = 'announcements';
	var $tableLang = 'announcements_lang';
	var $tableDirectorates = 'directorates';
	var $tableDirectoratesLang = 'directorates_lang';

	public function announcementInfoModel(int $announcement_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('announcement_id', $announcement_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function announcementLangModel(int $announcement_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.announcement_id,
						'.$this->tableLang.'.announcement_name,
						'.$this->tableLang.'.announcement_link,
						'.$this->tableLang.'.announcement_department,
						'.$this->tableLang.'.announcement_description,
						'.$this->tableLang.'.announcement_slug,
						'.$this->tableLang.'.announcement_mobile_name,
						'.$this->tableLang.'.announcement_mobile_description,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.announcement_id', $announcement_id);
		$query->orderBy($this->tableLang.'.lang_id');

		return $query->get()->getResult();
	}

	public function announcementLangControlModel(int $announcement_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('announcement_id');

		$query->where('announcement_id', $announcement_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function directoratesListModel(int $lang_id) {
		$query = $this->db->table($this->tableDirectorates);
		$query->select($this->tableDirectorates.'.directorates_id,
						'.$this->tableDirectoratesLang.'.directorates_name');
		$query->join($this->tableDirectoratesLang, $this->tableDirectoratesLang.'.directorates_id = '.$this->tableDirectorates.'.directorates_id AND '.$this->tableDirectoratesLang.'.lang_id = '.$lang_id, 'left');

		$query->orderBy($this->tableDirectoratesLang.'.directorates_name');

		return $query->get()->getResult();
	}
}
