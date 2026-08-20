<?php
namespace App\Models\Frontend\News;
use CodeIgniter\Model;

class NewsModel extends Model {

    protected $DBGroup = 'application';
	var $table = 'news';
	var $tableLang = 'news_lang';
	var $tableParagraphs = 'news_paragraphs';
	var $tableParagraphsLang = 'news_paragraphs_lang';
	var $tableImages = 'news_image';

	public function newsListModel(int $lang_id, ?int $page_start = NULL, ?int $per_page = NULL) {
		$query = $this->db->table($this->table);
		$query->select($this->table.'.news_id,
						'.$this->table.'.news_image,
						'.$this->table.'.news_created_date,
						'.$this->tableLang.'.news_name,
						'.$this->tableLang.'.news_slug');
		$query->join($this->tableLang, $this->tableLang.'.news_id = '.$this->table.'.news_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->table.'.status_genclik', FORM_ACTIVE_NUMBER);
		$query->where($this->tableLang.'.news_name !=', '');
		$query->orderBy($this->table.'.news_created_date', 'DESC');

		if (isNotNull($per_page)) {
			$query->limit($per_page, $page_start);
		}

		return $query->get()->getResult();
	}

	public function newsInfoModel(string $news_slug, int $news_id, int $lang_id) {
		$news_slug = $this->db->escapeLikeString($news_slug);
		
		$query = $this->db->table($this->table);
		$query->select($this->table.'.news_id,
						'.$this->table.'.news_image,
						'.$this->table.'.news_created_date,
						'.$this->tableLang.'.news_name,
						'.$this->tableLang.'.news_meta_title,
						'.$this->tableLang.'.news_meta_keywords,
						'.$this->tableLang.'.news_meta_description,
						'.$this->tableLang.'.news_description,
						'.$this->tableLang.'.news_slug');
		$query->join($this->tableLang, $this->tableLang.'.news_id = '.$this->table.'.news_id AND '.$this->tableLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->table.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->table.'.status_genclik', FORM_ACTIVE_NUMBER);
		$query->where($this->table.'.news_id', $news_id);
		$query->where($this->tableLang.'.news_slug', $news_slug);
		$query->where($this->tableLang.'.news_name !=', '');
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function newsParagraphsListModel(int $news_id, int $lang_id) {
		$query = $this->db->table($this->tableParagraphs);
		$query->select($this->tableParagraphs.'.news_paragraph_image,
						'.$this->tableParagraphsLang.'.news_paragraph_name,
						'.$this->tableParagraphsLang.'.news_paragraph_description');
		$query->join($this->tableParagraphsLang, $this->tableParagraphsLang.'.news_paragraph_id = '.$this->tableParagraphs.'.news_paragraph_id AND '.$this->tableParagraphsLang.'.lang_id = '.$lang_id, 'left');

		$query->where($this->tableParagraphs.'.news_id', $news_id);
		$query->where($this->tableParagraphsLang.'.news_paragraph_name !=', '');
		$query->orderBy($this->tableParagraphs.'.news_paragraph_created_date', 'DESC');

		return $query->get()->getResult();
	}

	public function newsImageListModel(int $news_id) {
		$query = $this->db->table($this->tableImages);
		$query->select('news_image');

		$query->where('news_id', $news_id);
		$query->orderBy('news_image_order', 'ASC');

		return $query->get()->getResult();
	}
}
