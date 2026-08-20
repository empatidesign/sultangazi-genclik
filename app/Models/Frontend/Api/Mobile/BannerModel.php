<?php
namespace App\Models\Frontend\Api\Mobile;
use CodeIgniter\Model;

class BannerModel extends Model {

	var $table = 'banner';
	var $tableLang = 'banner_lang';

	public function bannerModel(int $lang_id) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.banner_type,
						'.$this->table.'.banner_web_image,
						'.$this->table.'.banner_mobile_image,
						'.$this->table.'.banner_order,
						'.$this->tableLang.'.banner_name,
						'.$this->tableLang.'.banner_description,
						'.$this->tableLang.'.banner_link,
						(CASE WHEN '.$this->table.'.banner_link_target = "'.FORM_ACTIVE_NUMBER.'" THEN "'.MENU_MANAGEMENT_TARGET_NEW_WINDOW.'"
																																											ELSE "'.MENU_MANAGEMENT_TARGET_SAME_WINDOW.'" END) AS banner_link_target');
		$query->join($this->tableLang, $this->tableLang.'.banner_id = '.$this->table.'.banner_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status_mobile', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.banner_order', 'ASC');

		return $query->get()->getResult();
	}
}
