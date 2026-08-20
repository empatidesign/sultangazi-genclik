<?php
namespace App\Models\Frontend\Contents;
use CodeIgniter\Model;

class DirectoratesModel extends Model {

	var $table = 'directorates';
	var $tableLang = 'directorates_lang';
	var $tableFiles = 'directorates_file';
	var $tableFilesLang = 'directorates_file_lang';
	var $tableCategories = 'directorate_categories';
	var $tableCategoriesLang = 'directorate_categories_lang';
	var $tableAnnouncements = 'announcements';
	var $tableAnnouncementsLang = 'announcements_lang';
	var $tableNews = 'news';
	var $tableNewsLang = 'news_lang';

	public function directoratesInfoModel(string $directorates_slug, int $directorates_id, int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.directorates_id,
						'.$this->table.'.directorates_person_name,
						'.$this->table.'.directorates_person_surname,
						'.$this->table.'.directorates_person_image,
						'.$this->table.'.directorates_telephone,
						'.$this->table.'.directorates_fax,
						'.$this->table.'.directorates_email_address,
						'.$this->tableLang.'.directorates_name,
						'.$this->tableLang.'.directorates_person_sub_title');
		$query->join($this->tableLang, $this->tableLang.'.directorates_id = '.$this->table.'.directorates_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->table.'.directorates_id', $directorates_id);
		$query->where($this->tableLang.'.directorates_slug', $directorates_slug);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function directoratesListModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.directorates_id,
						'.$this->table.'.directorates_icon,
						'.$this->tableLang.'.directorates_name,
						'.$this->tableLang.'.directorates_slug');
		$query->join($this->tableLang, $this->tableLang.'.directorates_id = '.$this->table.'.directorates_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->tableLang.'.directorates_name', 'ASC');

		return $query->get()->getResult();
	}

	public function directorateCategoriesListModel(int $directorates_id, int $lang_id) {
		$query = $this->db->table($this->tableFiles);
		$query->select($this->tableFiles.'.directorate_category_id,
						'.$this->tableCategoriesLang.'.directorate_category_name');
		$query->join($this->tableCategories, $this->tableCategories.'.directorate_category_id = '.$this->tableFiles.'.directorate_category_id', 'left');
		$query->join($this->tableCategoriesLang, $this->tableCategoriesLang.'.directorate_category_id = '.$this->tableCategories.'.directorate_category_id AND '.$this->tableCategoriesLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->tableFiles.'.directorates_id', $directorates_id);
		$query->where($this->tableCategories.'.status', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->tableFiles.'.directorates_id', 'DESC');
		$query->groupBy($this->tableFiles.'.directorate_category_id');

		return $query->get()->getResult();
	}

	public function directoratesFileListModel(int $directorates_id, int $category_id, int $lang_id) {
		$query = $this->db->table($this->tableFiles);
		$query->select($this->tableFiles.'.directorates_file,
						'.$this->tableFilesLang.'.directorates_file_name');
		$query->join($this->tableFilesLang, $this->tableFilesLang.'.directorates_file_id = '.$this->tableFiles.'.directorates_file_id AND '.$this->tableFilesLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->tableFiles.'.directorates_id', $directorates_id);
		$query->where($this->tableFiles.'.directorate_category_id', $category_id);
		$query->orderBy($this->tableFiles.'.directorates_file_id', 'ASC');

		return $query->get()->getResult();
	}

	public function announcementsListModel(int $directorates_id, int $lang_id) {
		$query = $this->db->table($this->tableAnnouncements);
		$query->select($this->tableAnnouncementsLang.'.announcement_name');
		$query->join($this->tableAnnouncementsLang, $this->tableAnnouncementsLang.'.announcement_id = '.$this->tableAnnouncements.'.announcement_id AND '.$this->tableAnnouncementsLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->tableAnnouncements.'.status', FORM_ACTIVE_NUMBER);
		$query->where("FIND_IN_SET(".$directorates_id.", REPLACE(directorates_id, ',', ',')) != 0");
		$query->orderBy($this->tableAnnouncements.'.announcement_created_date', 'DESC');

		return $query->get()->getResult();
	}

	public function newsListModel(int $directorates_id, int $lang_id) {
		$query = $this->db->table($this->tableNews);
		$query->select($this->tableNews.'.news_id,
						'.$this->tableNewsLang.'.news_name,
						'.$this->tableNewsLang.'.news_slug');
		$query->join($this->tableNewsLang, $this->tableNewsLang.'.news_id = '.$this->tableNews.'.news_id AND '.$this->tableNewsLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->tableNews.'.status', FORM_ACTIVE_NUMBER);
		$query->where("FIND_IN_SET(".$directorates_id.", REPLACE(directorates_id, ',', ',')) != 0");
		$query->orderBy($this->tableNews.'.news_created_date', 'DESC');

		return $query->get()->getResult();
	}
}
