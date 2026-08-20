<?php
namespace App\Models\Backend\Events;
use CodeIgniter\Model;

class EventsCategoryModel extends Model {

	var $table = 'events_category';
	var $tableLang = 'events_category_lang';
	var $tableEvents = 'events';

	public function eventsCategoryInfoModel(int $event_category_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('event_category_id', $event_category_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function eventsCategoryLangModel(int $event_category_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.event_category_id,
						'.$this->tableLang.'.event_category_name,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.event_category_id', $event_category_id);
		$query->orderBy($this->tableLang.'.lang_id');

		return $query->get()->getResult();
	}

	public function eventsCategoryLangControlModel(int $event_category_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('event_category_id');

		$query->where('event_category_id', $event_category_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function eventsControlModel(int $event_category_id) {
		$query = $this->db->table($this->tableEvents);
		$query->select('event_category_id');

		$query->where('event_category_id', $event_category_id);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
