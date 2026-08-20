<?php
namespace App\Models\Frontend\Api\Mobile;
use CodeIgniter\Model;

class NewsModel extends Model {

	var $table = 'news';
	var $tableLang = 'news_lang';
	var $tableImages = 'news_image';

	public function newsModel(int $lang_id) {
		// Genclik sitesinde haberler 'application' baglantisindadir
		// (bkz. app/Models/Frontend/News/NewsModel.php).
		$appdb = db_connect('application');
		$query = $appdb->table($this->table);
		$query->select($this->table.'.news_id,
						'.$this->table.'.news_image,
						'.$this->table.'.news_created_date,
						'.$this->tableLang.'.news_name,
						'.$this->tableLang.'.news_description');
		$query->join($this->tableLang, $this->tableLang.'.news_id = '.$this->table.'.news_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status_mobile', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->table.'.news_created_date', 'DESC');
		$query->limit(50);

		return $query->get()->getResult();
	}

	public function newsGalleryModel(int $news_id) {
		$appdb = db_connect('application');
		$query = $appdb->table($this->tableImages);
		$query->select('news_image,
						news_image_order');

		$query->where('news_id', $news_id);
		$query->orderBy('news_image_order', 'ASC');

		return $query->get()->getResult();
	}
}
