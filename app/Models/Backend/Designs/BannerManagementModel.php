<?php
namespace App\Models\Backend\Designs;
use CodeIgniter\Model;

class BannerManagementModel extends Model {

	var $table = 'banner';
	var $tableLang = 'banner_lang';

	public function bannerInfoModel(int $banner_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('banner_id', $banner_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function bannerLangModel(int $banner_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.banner_id,
						'.$this->tableLang.'.banner_name,
						'.$this->tableLang.'.banner_description,
						'.$this->tableLang.'.banner_link,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.banner_id', $banner_id);
		$query->orderBy($this->tableLang.'.lang_id');

		return $query->get()->getResult();
	}

	public function bannerLangControlModel(int $banner_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('banner_id');

		$query->where('banner_id', $banner_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function bannerListModel(int $lang_id, $banner_type = NULL, string $banner_name = NULL) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.banner_id,
						'.$this->table.'.banner_type,
						'.$this->table.'.banner_web_image,
						'.$this->table.'.banner_start_date,
						'.$this->table.'.banner_end_date,
						'.$this->table.'.banner_created_date,
						'.$this->table.'.banner_updated_date,
						'.$this->tableLang.'.banner_name');
		$query->join($this->tableLang, $this->tableLang.'.banner_id = '.$this->table.'.banner_id', 'left');

		$query->where($this->tableLang.'.lang_id', $lang_id);

		if (isNotNull($banner_type)) {
			$query->where($this->table.'.banner_type', $banner_type);
		}

		if (isNotNull($banner_name)) {
			$query->like($this->tableLang.'.banner_name', $banner_name);
		}

		$query->orderBy($this->table.'.banner_order', 'ASC');

		return $query->get()->getResult();
	}
}
