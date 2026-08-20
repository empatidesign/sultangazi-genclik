<?php
namespace App\Models\Frontend;
use CodeIgniter\Model;

class RoutingModel extends Model {

	var $tableLanguages = 'languages';
	var $tableContracts = 'contracts';
	var $tableContractsLang = 'contracts_lang';
	var $tablePages = 'pages';
	var $tablePagesLang = 'pages_lang';
	var $tablePresidentContents = 'president_contents';
	var $tablePresidentContentsLang = 'president_contents_lang';
	var $tableSultangaziContents = 'sultangazi_contents';
	var $tableSultangaziContentsLang = 'sultangazi_contents_lang';
	var $tableSultangaziCityGuideCategories = 'city_guide_categories';
	var $tableSultangaziCityGuideCategoriesLang = 'city_guide_categories_lang';
	var $tableSultangaziCityGuideContents = 'city_guide_contents';
	var $tableSultangaziCityGuideContentsLang = 'city_guide_contents_lang';
	var $tableDirectorates = 'directorates';
	var $tableDirectoratesLang = 'directorates_lang';
	var $tableGalleryCategories = 'gallery_categories';
	var $tableGalleryCategoriesLang = 'gallery_categories_lang';
	var $tableGallery = 'gallery';
	var $tableGalleryLang = 'gallery_lang';
	var $tableProjects = 'projects';
	var $tableProjectsLang = 'projects_lang';
	var $tableServices = 'services';
	var $tableServicesLang = 'services_lang';
	var $tableEvents = 'events';
	var $tableEventsLang = 'events_lang';

	public function languagesModel() {
		$query = $this->db->table($this->tableLanguages);
		$query->select('LOWER(lang_code) AS lang_code');

		$query->where('lang_status', FORM_ACTIVE_NUMBER);
		$query->orderBy('lang_id', 'ASC');

		$result = $query->get()->getResult();

		$supportedLang = [];
	  if (isNotNull($result)) {
	    foreach ($result as $row) {
	      $supportedLang[] = $row->lang_code;
	    }
	  }

		return $supportedLang;
	}

	public function contractsModel(string $segment1 = NULL, string $segment2 = NULL, string $segment3 = NULL) {
		$query = $this->db->table($this->tableContracts);
		$query->select($this->tableContracts.'.contract_id');
		$query->join($this->tableContractsLang, $this->tableContractsLang.'.contract_id = '.$this->tableContracts.'.contract_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableContractsLang.'.lang_id', 'left');

		if (isNotNull($segment1)) {
			$segment1 = $this->sanitizeLanguageCode($segment1);
			$query->where($this->tableLanguages.'.lang_code', $segment1);
		}

		if (isNotNull($segment2)) {
			$segment2 = $this->sanitizeSlug($segment2);
			$query->where($this->tableContractsLang.'.contract_slug', $segment2);
		}

		if (isNotNull($segment3)) {
			$segment3 = $this->sanitizeNumericId($segment3);
			$query->where($this->tableContracts.'.contract_id', $segment3);
		}

		$query->where($this->tableContracts.'.status', FORM_ACTIVE_NUMBER);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function pagesModel(string $segment1 = NULL, string $segment2 = NULL, string $segment3 = NULL) {
		$query = $this->db->table($this->tablePages);
		$query->select($this->tablePages.'.page_id');
		$query->join($this->tablePagesLang, $this->tablePagesLang.'.page_id = '.$this->tablePages.'.page_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tablePagesLang.'.lang_id', 'left');

		if (isNotNull($segment1)) {
			$segment1 = $this->sanitizeLanguageCode($segment1);
			$query->where($this->tableLanguages.'.lang_code', $segment1);
		}

		if (isNotNull($segment2)) {
			$segment2 = $this->sanitizeSlug($segment2);
			$query->where($this->tablePagesLang.'.page_slug', $segment2);
		}

		if (isNotNull($segment3)) {
			$segment3 = $this->sanitizeNumericId($segment3);
			$query->where($this->tablePages.'.page_id', $segment3);
		}

		$query->where($this->tablePages.'.status', FORM_ACTIVE_NUMBER);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function sultangaziContentsModel(string $segment1 = NULL, string $segment2 = NULL, string $segment3 = NULL) {
		$query = $this->db->table($this->tableSultangaziContents);
		$query->select($this->tableSultangaziContents.'.content_id');
		$query->join($this->tableSultangaziContentsLang, $this->tableSultangaziContentsLang.'.content_id = '.$this->tableSultangaziContents.'.content_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableSultangaziContentsLang.'.lang_id', 'left');

		if (isNotNull($segment1)) {
			$segment1 = $this->sanitizeLanguageCode($segment1);
			$query->where($this->tableLanguages.'.lang_code', $segment1);
		}

		if (isNotNull($segment2)) {
			$segment2 = $this->sanitizeSlug($segment2);
			$query->where($this->tableSultangaziContentsLang.'.content_slug', $segment2);
		}

		if (isNotNull($segment3)) {
			$segment3 = $this->sanitizeNumericId($segment3);
			$query->where($this->tableSultangaziContents.'.content_id', $segment3);
		}

		$query->where($this->tableSultangaziContents.'.status', FORM_ACTIVE_NUMBER);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function sultangaziCityGuideCategoriesModel(string $segment1 = NULL, string $segment2 = NULL, string $segment3 = NULL) {
		$query = $this->db->table($this->tableSultangaziCityGuideCategories);
		$query->select($this->tableSultangaziCityGuideCategories.'.city_guide_category_id');
		$query->join($this->tableSultangaziCityGuideCategoriesLang, $this->tableSultangaziCityGuideCategoriesLang.'.city_guide_category_id = '.$this->tableSultangaziCityGuideCategories.'.city_guide_category_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableSultangaziCityGuideCategoriesLang.'.lang_id', 'left');

		if (isNotNull($segment1)) {
			$segment1 = $this->sanitizeLanguageCode($segment1);
			$query->where($this->tableLanguages.'.lang_code', $segment1);
		}

		if (isNotNull($segment2)) {
			$segment2 = $this->sanitizeSlug($segment2);
			$query->where($this->tableSultangaziCityGuideCategoriesLang.'.city_guide_category_slug', $segment2);
		}

		if (isNotNull($segment3)) {
			$segment3 = $this->sanitizeNumericId($segment3);
			$query->where($this->tableSultangaziCityGuideCategories.'.city_guide_category_id', $segment3);
		}

		$query->where($this->tableSultangaziCityGuideCategories.'.status', FORM_ACTIVE_NUMBER);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function sultangaziCityGuideContentsModel(string $segment1 = NULL, string $segment2 = NULL, string $segment3 = NULL) {
		$query = $this->db->table($this->tableSultangaziCityGuideContents);
		$query->select($this->tableSultangaziCityGuideContents.'.city_guide_content_id');
		$query->join($this->tableSultangaziCityGuideContentsLang, $this->tableSultangaziCityGuideContentsLang.'.city_guide_content_id = '.$this->tableSultangaziCityGuideContents.'.city_guide_content_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableSultangaziCityGuideContentsLang.'.lang_id', 'left');

		if (isNotNull($segment1)) {
			$segment1 = $this->sanitizeLanguageCode($segment1);
			$query->where($this->tableLanguages.'.lang_code', $segment1);
		}

		if (isNotNull($segment2)) {
			$segment2 = $this->sanitizeSlug($segment2);
			$query->where($this->tableSultangaziCityGuideContentsLang.'.city_guide_content_slug', $segment2);
		}

		if (isNotNull($segment3)) {
			$segment3 = $this->sanitizeNumericId($segment3);
			$query->where($this->tableSultangaziCityGuideContents.'.city_guide_content_id', $segment3);
		}

		$query->where($this->tableSultangaziCityGuideContents.'.status', FORM_ACTIVE_NUMBER);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function presidentContentModel(string $segment1 = NULL, string $segment2 = NULL, string $segment3 = NULL) {
		$query = $this->db->table($this->tablePresidentContents);
		$query->select($this->tablePresidentContents.'.president_content_id');
		$query->join($this->tablePresidentContentsLang, $this->tablePresidentContentsLang.'.president_content_id = '.$this->tablePresidentContents.'.president_content_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tablePresidentContentsLang.'.lang_id', 'left');

		if (isNotNull($segment1)) {
			$segment1 = $this->sanitizeLanguageCode($segment1);
			$query->where($this->tableLanguages.'.lang_code', $segment1);
		}

		if (isNotNull($segment2)) {
			$segment2 = $this->sanitizeSlug($segment2);
			$query->where($this->tablePresidentContentsLang.'.president_content_slug', $segment2);
		}

		if (isNotNull($segment3)) {
			$segment3 = $this->sanitizeNumericId($segment3);
			$query->where($this->tablePresidentContents.'.president_content_id', $segment3);
		}

		$query->where($this->tablePresidentContents.'.status', FORM_ACTIVE_NUMBER);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function directoratesModel(string $segment1 = NULL, string $segment2 = NULL, string $segment3 = NULL) {
		$query = $this->db->table($this->tableDirectorates);
		$query->select($this->tableDirectorates.'.directorates_id');
		$query->join($this->tableDirectoratesLang, $this->tableDirectoratesLang.'.directorates_id = '.$this->tableDirectorates.'.directorates_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableDirectoratesLang.'.lang_id', 'left');

		if (isNotNull($segment1)) {
			$segment1 = $this->sanitizeLanguageCode($segment1);
			$query->where($this->tableLanguages.'.lang_code', $segment1);
		}

		if (isNotNull($segment2)) {
			$segment2 = $this->sanitizeSlug($segment2);
			$query->where($this->tableDirectoratesLang.'.directorates_slug', $segment2);
		}

		if (isNotNull($segment3)) {
			$segment3 = $this->sanitizeNumericId($segment3);
			$query->where($this->tableDirectorates.'.directorates_id', $segment3);
		}

		$query->where($this->tableDirectorates.'.status', FORM_ACTIVE_NUMBER);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function galleryCategoriesModel(string $segment1 = NULL, string $segment2 = NULL) {
		$query = $this->db->table($this->tableGalleryCategories);
		$query->select($this->tableGalleryCategories.'.gallery_category_id');
		$query->join($this->tableGalleryCategoriesLang, $this->tableGalleryCategoriesLang.'.gallery_category_id = '.$this->tableGalleryCategories.'.gallery_category_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableGalleryCategoriesLang.'.lang_id', 'left');

		if (isNotNull($segment1)) {
			$segment1 = $this->sanitizeLanguageCode($segment1);
			$query->where($this->tableLanguages.'.lang_code', $segment1);
		}

		if (isNotNull($segment2)) {
			$segment2 = $this->sanitizeNumericId($segment2);
			$query->where($this->tableGalleryCategories.'.gallery_category_id', $segment2);
		}

		$query->where($this->tableGalleryCategories.'.status', FORM_ACTIVE_NUMBER);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function galleryDetailModel(string $segment1 = NULL, string $segment2 = NULL) {
		$query = $this->db->table($this->tableGallery);
		$query->select($this->tableGallery.'.gallery_id');
		$query->join($this->tableGalleryLang, $this->tableGalleryLang.'.gallery_id = '.$this->tableGallery.'.gallery_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableGalleryLang.'.lang_id', 'left');

		if (isNotNull($segment1)) {
			$segment1 = $this->sanitizeLanguageCode($segment1);
			$query->where($this->tableLanguages.'.lang_code', $segment1);
		}

		if (isNotNull($segment2)) {
			$segment2 = $this->sanitizeNumericId($segment2);
			$query->where($this->tableGallery.'.gallery_id', $segment2);
		}

		$query->where($this->tableGallery.'.status', FORM_ACTIVE_NUMBER);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function projectDetailModel(string $segment1 = NULL, string $segment2 = NULL, string $segment3 = NULL) {
		$query = $this->db->table($this->tableProjects);
		$query->select($this->tableProjects.'.project_id');
		$query->join($this->tableProjectsLang, $this->tableProjectsLang.'.project_id = '.$this->tableProjects.'.project_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableProjectsLang.'.lang_id', 'left');

		if (isNotNull($segment1)) {
			$segment1 = $this->sanitizeLanguageCode($segment1);
			$query->where($this->tableLanguages.'.lang_code', $segment1);
		}

		if (isNotNull($segment2)) {
			$segment2 = $this->sanitizeSlug($segment2);
			$query->where($this->tableProjectsLang.'.project_slug', $segment2);
		}

		if (isNotNull($segment3)) {
			$segment3 = $this->sanitizeNumericId($segment3);
			$query->where($this->tableProjects.'.project_id', $segment3);
		}

		$query->where($this->tableProjects.'.status', FORM_ACTIVE_NUMBER);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function serviceDetailModel(string $segment1 = NULL, string $segment2 = NULL, string $segment3 = NULL) {
		$query = $this->db->table($this->tableServices);
		$query->select($this->tableServices.'.service_id');
		$query->join($this->tableServicesLang, $this->tableServicesLang.'.service_id = '.$this->tableServices.'.service_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableServicesLang.'.lang_id', 'left');

		if (isNotNull($segment1)) {
			$segment1 = $this->sanitizeLanguageCode($segment1);
			$query->where($this->tableLanguages.'.lang_code', $segment1);
		}

		if (isNotNull($segment2)) {
			$segment2 = $this->sanitizeSlug($segment2);
			$query->where($this->tableServicesLang.'.service_slug', $segment2);
		}

		if (isNotNull($segment3)) {
			$segment3 = $this->sanitizeNumericId($segment3);
			$query->where($this->tableServices.'.service_id', $segment3);
		}

		$query->where($this->tableServices.'.status', FORM_ACTIVE_NUMBER);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function eventDetailModel(string $segment1 = NULL, string $segment2 = NULL, string $segment3 = NULL) {
		$query = $this->db->table($this->tableEvents);
		$query->select($this->tableEvents.'.event_id');
		$query->join($this->tableEventsLang, $this->tableEventsLang.'.event_id = '.$this->tableEvents.'.event_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableEventsLang.'.lang_id', 'left');

		if (isNotNull($segment1)) {
			$segment1 = $this->sanitizeLanguageCode($segment1);
			$query->where($this->tableLanguages.'.lang_code', $segment1);
		}

		if (isNotNull($segment2)) {
			$segment2 = $this->sanitizeSlug($segment2);
			$query->where($this->tableEventsLang.'.event_slug', $segment2);
		}

		if (isNotNull($segment3)) {
			$segment3 = $this->sanitizeNumericId($segment3);
			$query->where($this->tableEvents.'.event_id', $segment3);
		}

		$query->where($this->tableEvents.'.status', FORM_ACTIVE_NUMBER);
		$query->limit(1);

		return $query->get()->getRow();
	}

	/**
	 * Dil kodunu güvenli hale getiren fonksiyon
	 * @param string $code
	 * @return string
	 */
	private function sanitizeLanguageCode($code) {
		if (empty($code)) {
			return '';
		}
		
		$code = preg_replace('/[^a-zA-Z]/', '', $code);
		$code = substr($code, 0, 5);
		
		return strtolower($code);
	}
	
	
	private function sanitizeSlug($slug) {
		if (empty($slug)) {
			return '';
		}
		
		$slug = str_replace(['../', './'], '', $slug);
		$slug = preg_replace('/[^a-zA-Z0-9_\-]/', '', $slug);
		
		return $slug;
	}
	

	private function sanitizeNumericId($id) {
		if (empty($id)) {
			return 0;
		}
		
		$id = preg_replace('/[^0-9]/', '', $id);
		
		return (int) $id;
	}
}
