<?php
namespace App\Models\Backend\News;
use CodeIgniter\Model;

class NewsContentModel extends Model {

	var $table = 'news';
    protected $DBGroup = 'application';
	var $tableLang = 'news_lang';
	var $tableParagraphs = 'news_paragraphs';
	var $tableParagraphsLang = 'news_paragraphs_lang';
	var $tableImages = 'news_image';
	var $tableDirectorates = 'directorates';
	var $tableDirectoratesLang = 'directorates_lang';

	public function newsInfoModel(int $news_id) {
		$query = $this->db->table($this->table);
		$query->select('*');

		$query->where('news_id', $news_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function newsLangModel(int $news_id) {
		$query = $this->db->table($this->tableLang);
		$query->select($this->tableLang.'.news_id,
						'.$this->tableLang.'.news_name,
						'.$this->tableLang.'.news_description,
						'.$this->tableLang.'.news_meta_title,
						'.$this->tableLang.'.news_meta_keywords,
						'.$this->tableLang.'.news_meta_description,
						'.$this->tableLang.'.news_slug,
						'.$this->tableLang.'.news_mobile_name,
						'.$this->tableLang.'.news_mobile_description,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableLang.'.lang_id', 'left');

		$query->where($this->tableLang.'.news_id', $news_id);
		$query->orderBy($this->tableLang.'.news_id');

		return $query->get()->getResult();
	}

	public function newsLangControlModel(int $news_id, int $lang_id) {
		$query = $this->db->table($this->tableLang);
		$query->select('news_id');

		$query->where('news_id', $news_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function newsParagraphInfoModel(int $news_paragraph_id) {
		$query = $this->db->table($this->tableParagraphs);
		$query->select('*');

		$query->where('news_paragraph_id', $news_paragraph_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function newsParagraphListModel(int $news_id) {
		$query = $this->db->table($this->tableParagraphs);
		$query->select('news_paragraph_id,
						news_id,
						news_paragraph_image');

		$query->where('news_id', $news_id);
		$query->orderBy('news_paragraph_id', 'ASC');

		return $query->get()->getResult();
	}

	public function newsParagraphLangModel(int $news_paragraph_id) {
		$query = $this->db->table($this->tableParagraphsLang);
		$query->select($this->tableParagraphsLang.'.news_paragraph_lang_id,
						'.$this->tableParagraphsLang.'.news_paragraph_id,
						'.$this->tableParagraphsLang.'.news_paragraph_name,
						'.$this->tableParagraphsLang.'.news_paragraph_description,
						languages.lang_id,
						languages.lang_title');
		$query->join('languages', 'languages.lang_id = '.$this->tableParagraphsLang.'.lang_id', 'left');

		$query->where($this->tableParagraphsLang.'.news_paragraph_id', $news_paragraph_id);
		$query->orderBy($this->tableParagraphsLang.'.news_paragraph_id', 'ASC');

		return $query->get()->getResult();
	}

	public function newsParagraphLangControlModel(int $news_paragraph_id, int $lang_id) {
		$query = $this->db->table($this->tableParagraphsLang);
		$query->select('news_paragraph_id');

		$query->where('news_paragraph_id', $news_paragraph_id);
		$query->where('lang_id', $lang_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	/*****************************************************/

	public function newsImageListModel(int $news_id) {
		$query = $this->db->table($this->tableImages);
		$query->select('*');

		$query->where('news_id', $news_id);
		$query->orderBy('news_image_order', 'ASC');

		return $query->get()->getResult();
	}

	public function newsImageInfoModel(int $news_image_id) {
		$query = $this->db->table($this->tableImages);
		$query->select('*');

		$query->where('news_image_id', $news_image_id);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function newsImageOrderModel(int $news_id) {
		$query = $this->db->table($this->tableImages);
		$query->select('news_image_order');

		$query->where('news_id', $news_id);
		$query->orderBy('news_image_order', 'DESC');
		$query->limit(1);

		return $query->get()->getRow();
	}

	/*****************************************************/

	public function directoratesListModel(int $lang_id) {
		$query = $this->db->table($this->tableDirectorates);
		$query->select($this->tableDirectorates.'.directorates_id,
						'.$this->tableDirectoratesLang.'.directorates_name');
		$query->join($this->tableDirectoratesLang, $this->tableDirectoratesLang.'.directorates_id = '.$this->tableDirectorates.'.directorates_id AND '.$this->tableDirectoratesLang.'.lang_id = '.$lang_id, 'left');

		$query->orderBy($this->tableDirectoratesLang.'.directorates_name');

		return $query->get()->getResult();
	}
}
