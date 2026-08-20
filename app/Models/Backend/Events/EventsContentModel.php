<?php
namespace App\Models\Backend\Events;
use CodeIgniter\Model;

class EventsContentModel extends Model {

	var $table = 'events';
	var $tableLang = 'events_lang';
	var $tableParagraphs = 'events_paragraphs';
	var $tableParagraphsLang = 'events_paragraphs_lang';
	var $tableCategories = 'events_category';
	var $tableCategoriesLang = 'events_category_lang';

	public function eventInfoModel(int $event_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('event_id', $event_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function eventLangModel(int $event_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.event_id,
						'.$this->tableLang.'.event_name,
						'.$this->tableLang.'.event_age_group,
						'.$this->tableLang.'.event_quota,
						'.$this->tableLang.'.event_description,
						'.$this->tableLang.'.event_meta_title,
						'.$this->tableLang.'.event_meta_keywords,
						'.$this->tableLang.'.event_meta_description,
						'.$this->tableLang.'.event_slug,
						'.$this->tableLang.'.event_mobile_name,
						'.$this->tableLang.'.event_mobile_description,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.event_id', $event_id);
		$query->orderBy($this->tableLang.'.event_id');

		return $query->get()->getResult();
	}

	public function eventLangControlModel(int $event_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('event_id');

		$query->where('event_id', $event_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function eventParagraphInfoModel(int $event_paragraph_id) {
		$query = $this->db->table($this->tableParagraphs);
		$query->select('*');

		$query->where('event_paragraph_id', $event_paragraph_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function eventParagraphListModel(int $event_id) {
		$query = $this->db->table($this->tableParagraphs);
		$query->select('event_paragraph_id,
						event_id,
						event_paragraph_image');

		$query->where('event_id', $event_id);
		$query->orderBy('event_paragraph_id', 'ASC');

		return $query->get()->getResult();
	}

	public function eventParagraphLangModel(int $event_paragraph_id) {
		$query = $this->db->table($this->tableParagraphsLang);
		$query->select($this->tableParagraphsLang.'.event_paragraph_lang_id,
						'.$this->tableParagraphsLang.'.event_paragraph_id,
						'.$this->tableParagraphsLang.'.event_paragraph_name,
						'.$this->tableParagraphsLang.'.event_paragraph_description,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableParagraphsLang.'.lang_id', 'left');

		$query->where($this->tableParagraphsLang.'.event_paragraph_id', $event_paragraph_id);
		$query->orderBy($this->tableParagraphsLang.'.event_paragraph_id', 'ASC');

		return $query->get()->getResult();
	}

	public function eventParagraphLangControlModel(int $event_paragraph_id, int $lang_id) {
		$query = $this->db->table($this->tableParagraphsLang);
		$query->select('event_paragraph_id');

		$query->where('event_paragraph_id', $event_paragraph_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function eventCategoryListModel(int $lang_id) {
		$query = $this->db->table($this->tableCategories);
		$query->select($this->tableCategories.'.event_category_id,
						'.$this->tableCategoriesLang.'.event_category_name');
		$query->join($this->tableCategoriesLang, $this->tableCategoriesLang.'.event_category_id = '.$this->tableCategories.'.event_category_id AND '.$this->tableCategoriesLang.'.lang_id = '.$lang_id, 'left');

		$query->orderBy($this->tableCategories.'.event_category_order', 'ASC');

		return $query->get()->getResult();
	}
}
