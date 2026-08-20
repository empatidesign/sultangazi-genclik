<?php

namespace App\Models\Frontend\Projects;

use CodeIgniter\Model;

class CourseModel extends Model
{

	var $table = 'courses';
	var $tableLang = 'courses_lang';
	var $tableParagraphs = 'courses_paragraphs';
	var $tableParagraphsLang = 'courses_paragraphs_lang';
	var $tableImages = 'courses_image';

	public function newsListModel(int $lang_id, ?int $page_start = NULL, ?int $per_page = NULL)
	{
		$query = $this->db->table($this->table);
		$query->select($this->table . '.courses_id,
						' . $this->table . '.courses_image,
						' . $this->table . '.courses_created_date,
						' . $this->tableLang . '.courses_name,
						' . $this->tableLang . '.courses_slug');
		$query->join($this->tableLang, $this->tableLang . '.courses_id = ' . $this->table . '.courses_id AND ' . $this->tableLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->table . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableLang . '.courses_name !=', '');
		$query->orderBy($this->table . '.courses_created_date', 'DESC');

		if (isNotNull($per_page)) {
			$query->limit($per_page, $page_start);
		}

		return $query->get()->getResult();
	}

	public function coursesInfoModel(string $courses_slug = null, int $courses_id = null, int $lang_id)
	{
		if (isNotNull($courses_slug)) {
			$courses_slug = $this->db->escapeLikeString($courses_slug);
		}

		$query = $this->db->table($this->table);
		$query->select($this->table . '.courses_id,
						' . $this->table . '.courses_image,
						' . $this->table . '.courses_created_date,
						' . $this->tableLang . '.courses_name,
						' . $this->tableLang . '.courses_meta_title,
						' . $this->tableLang . '.courses_meta_keywords,
						' . $this->tableLang . '.courses_meta_description,
						' . $this->tableLang . '.courses_description,
						' . $this->tableLang . '.courses_slug');
		$query->join($this->tableLang, $this->tableLang . '.courses_id = ' . $this->table . '.courses_id AND ' . $this->tableLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->table . '.status', FORM_ACTIVE_NUMBER);
		if (isNotNull($courses_id)) {
			$query->where($this->table . '.courses_id', $courses_id);
		}
		if (isNotNull($courses_slug)) {
			$query->where($this->tableLang . '.courses_slug', $courses_slug);
		}
		$query->where($this->tableLang . '.courses_name !=', '');
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function coursesParagraphsListModel(int $courses_id, int $lang_id)
	{
		$query = $this->db->table($this->tableParagraphs);
		$query->select($this->tableParagraphs . '.courses_paragraph_image,
						' . $this->tableParagraphsLang . '.courses_paragraph_name,
						' . $this->tableParagraphsLang . '.courses_paragraph_description');
		$query->join($this->tableParagraphsLang, $this->tableParagraphsLang . '.courses_paragraph_id = ' . $this->tableParagraphs . '.courses_paragraph_id AND ' . $this->tableParagraphsLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableParagraphs . '.courses_id', $courses_id);
		$query->where($this->tableParagraphsLang . '.courses_paragraph_name !=', '');
		$query->orderBy($this->tableParagraphs . '.courses_paragraph_created_date', 'DESC');

		return $query->get()->getResult();
	}

	public function coursesImageListModel(int $courses_id)
	{
		$query = $this->db->table($this->tableImages);
		$query->select('courses_image');

		$query->where('courses_id', $courses_id);
		$query->orderBy('courses_image_order', 'ASC');

		return $query->get()->getResult();
	}
}
