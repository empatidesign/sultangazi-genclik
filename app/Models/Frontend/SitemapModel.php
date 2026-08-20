<?php
namespace App\Models\Frontend;
use CodeIgniter\Model;

class SitemapModel extends Model {

	var $tablePages = 'pages';
	var $tablePagesLang = 'pages_lang';
	var $tableServices = 'services';
	var $tableServicesLang = 'services_lang';
	var $tablePresidentContents = 'president_contents';
	var $tablePresidentContentsLang = 'president_contents_lang';
	var $tableVicePresidents = 'vice_presidents';
	var $tableVicePresidentsLang = 'vice_presidents_lang';
	var $tableDirectorates = 'directorates';
	var $tableDirectoratesLang = 'directorates_lang';
	var $tableNews = 'news';
	var $tableNewsLang = 'news_lang';
	var $tableProjects = 'projects';
	var $tableProjectsLang = 'projects_lang';
	var $tableSultangaziContents = 'sultangazi_contents';
	var $tableSultangaziContentsLang = 'sultangazi_contents_lang';
	var $tableCityGuideCategories = 'city_guide_categories';
	var $tableCityGuideCategoriesLang = 'city_guide_categories_lang';
	var $tableCityGuideContents = 'city_guide_contents';
	var $tableCityGuideContentsLang = 'city_guide_contents_lang';
	var $tableGalleryCategories = 'gallery_categories';
	var $tableGalleryCategoriesLang = 'gallery_categories_lang';
	var $tableGallery = 'gallery';
	var $tableGalleryLang = 'gallery_lang';
	var $tableEvents = 'events';
	var $tableEventsLang = 'events_lang';
	var $tableLanguages = 'languages';
	var $tableSettings = 'settings';

	public function pagesModel() {
		$query = $this->db->table($this->tablePages);
		$query->select($this->tablePages.'.page_id,
						'.$this->tablePagesLang.'.page_slug,
						'.$this->tableLanguages.'.lang_code,
						(CASE WHEN '.$this->tableLanguages.'.lang_id = '.$this->tableSettings.'.frontend_lang_default THEN ""
																																																					ELSE '.$this->tableLanguages.'.lang_code END) AS lang_code_end');
		$query->join($this->tablePagesLang, $this->tablePagesLang.'.page_id = '.$this->tablePages.'.page_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tablePagesLang.'.lang_id', 'left');
		$query->join($this->tableSettings, $this->tableSettings.'.frontend_lang_default = '.$this->tableLanguages.'.lang_id', 'left');

		$query->where($this->tablePages.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tablePagesLang.'.page_name !=', '');
		$query->orderBy($this->tablePagesLang.'.lang_id', 'ASC');

		return $query->get()->getResult();
	}

	public function servicesModel() {
		$query = $this->db->table($this->tableServices);
		$query->select($this->tableServices.'.service_id,
						'.$this->tableServicesLang.'.service_slug,
						'.$this->tableLanguages.'.lang_code,
						(CASE WHEN '.$this->tableLanguages.'.lang_id = '.$this->tableSettings.'.frontend_lang_default THEN ""
																																																					ELSE '.$this->tableLanguages.'.lang_code END) AS lang_code_end');
		$query->join($this->tableServicesLang, $this->tableServicesLang.'.service_id = '.$this->tableServices.'.service_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableServicesLang.'.lang_id', 'left');
		$query->join($this->tableSettings, $this->tableSettings.'.frontend_lang_default = '.$this->tableLanguages.'.lang_id', 'left');

		$query->where($this->tableServices.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableServices.'.service_type', SERVICES_TYPE_3);
		$query->where($this->tableServicesLang.'.service_name !=', '');
		$query->orderBy($this->tableServicesLang.'.lang_id', 'ASC');

		return $query->get()->getResult();
	}

	public function presidentContentsModel() {
		$query = $this->db->table($this->tablePresidentContents);
		$query->select($this->tablePresidentContents.'.president_content_id,
						'.$this->tablePresidentContentsLang.'.president_content_slug,
						'.$this->tableLanguages.'.lang_code,
						(CASE WHEN '.$this->tableLanguages.'.lang_id = '.$this->tableSettings.'.frontend_lang_default THEN ""
																																																					ELSE '.$this->tableLanguages.'.lang_code END) AS lang_code_end');
		$query->join($this->tablePresidentContentsLang, $this->tablePresidentContentsLang.'.president_content_id = '.$this->tablePresidentContents.'.president_content_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tablePresidentContentsLang.'.lang_id', 'left');
		$query->join($this->tableSettings, $this->tableSettings.'.frontend_lang_default = '.$this->tableLanguages.'.lang_id', 'left');

		$query->where($this->tablePresidentContents.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tablePresidentContentsLang.'.president_content_name !=', '');
		$query->orderBy($this->tablePresidentContentsLang.'.lang_id', 'ASC');

		return $query->get()->getResult();
	}

	public function vicePresidentsModel() {
		$query = $this->db->table($this->tableVicePresidents);
		$query->select($this->tableVicePresidents.'.vice_president_id,
						'.$this->tableVicePresidentsLang.'.vice_president_slug,
						'.$this->tableLanguages.'.lang_code,
						(CASE WHEN '.$this->tableLanguages.'.lang_id = '.$this->tableSettings.'.frontend_lang_default THEN ""
																																																					ELSE '.$this->tableLanguages.'.lang_code END) AS lang_code_end');
		$query->join($this->tableVicePresidentsLang, $this->tableVicePresidentsLang.'.vice_president_id = '.$this->tableVicePresidents.'.vice_president_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableVicePresidentsLang.'.lang_id', 'left');
		$query->join($this->tableSettings, $this->tableSettings.'.frontend_lang_default = '.$this->tableLanguages.'.lang_id', 'left');

		$query->where($this->tableVicePresidents.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableVicePresidents.'.vice_president_name !=', '');
		$query->orderBy($this->tableVicePresidentsLang.'.lang_id', 'ASC');

		return $query->get()->getResult();
	}

	public function directoratesModel() {
		$query = $this->db->table($this->tableDirectorates);
		$query->select($this->tableDirectorates.'.directorates_id,
						'.$this->tableDirectoratesLang.'.directorates_slug,
						'.$this->tableLanguages.'.lang_code,
						(CASE WHEN '.$this->tableLanguages.'.lang_id = '.$this->tableSettings.'.frontend_lang_default THEN ""
																																																					ELSE '.$this->tableLanguages.'.lang_code END) AS lang_code_end');
		$query->join($this->tableDirectoratesLang, $this->tableDirectoratesLang.'.directorates_id = '.$this->tableDirectorates.'.directorates_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableDirectoratesLang.'.lang_id', 'left');
		$query->join($this->tableSettings, $this->tableSettings.'.frontend_lang_default = '.$this->tableLanguages.'.lang_id', 'left');

		$query->where($this->tableDirectorates.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableDirectoratesLang.'.directorates_name !=', '');
		$query->orderBy($this->tableDirectoratesLang.'.lang_id', 'ASC');

		return $query->get()->getResult();
	}

	public function newsModel() {
		$query = $this->db->table($this->tableNews);
		$query->select($this->tableNews.'.news_id,
						'.$this->tableNewsLang.'.news_slug,
						'.$this->tableLanguages.'.lang_code,
						(CASE WHEN '.$this->tableLanguages.'.lang_id = '.$this->tableSettings.'.frontend_lang_default THEN ""
																																																					ELSE '.$this->tableLanguages.'.lang_code END) AS lang_code_end');
		$query->join($this->tableNewsLang, $this->tableNewsLang.'.news_id = '.$this->tableNews.'.news_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableNewsLang.'.lang_id', 'left');
		$query->join($this->tableSettings, $this->tableSettings.'.frontend_lang_default = '.$this->tableLanguages.'.lang_id', 'left');

		$query->where($this->tableNews.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableNewsLang.'.news_name !=', '');
		$query->orderBy($this->tableNewsLang.'.lang_id', 'ASC');

		return $query->get()->getResult();
	}

	public function projectsModel() {
		$query = $this->db->table($this->tableProjects);
		$query->select($this->tableProjects.'.project_id,
						'.$this->tableProjectsLang.'.project_slug,
						(CASE WHEN '.$this->tableLanguages.'.lang_id = '.$this->tableSettings.'.frontend_lang_default THEN ""
																																																					ELSE '.$this->tableLanguages.'.lang_code END) AS lang_code_end');
		$query->join($this->tableProjectsLang, $this->tableProjectsLang.'.project_id = '.$this->tableProjects.'.project_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableProjectsLang.'.lang_id', 'left');
		$query->join($this->tableSettings, $this->tableSettings.'.frontend_lang_default = '.$this->tableLanguages.'.lang_id', 'left');

		$query->where($this->tableProjects.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableProjectsLang.'.project_name !=', '');
		$query->orderBy($this->tableProjectsLang.'.lang_id', 'ASC');

		return $query->get()->getResult();
	}

	public function sultangaziContentsModel() {
		$query = $this->db->table($this->tableSultangaziContents);
		$query->select($this->tableSultangaziContents.'.content_id,
						'.$this->tableSultangaziContentsLang.'.content_slug,
						(CASE WHEN '.$this->tableLanguages.'.lang_id = '.$this->tableSettings.'.frontend_lang_default THEN ""
																																																					ELSE '.$this->tableLanguages.'.lang_code END) AS lang_code_end');
		$query->join($this->tableSultangaziContentsLang, $this->tableSultangaziContentsLang.'.content_id = '.$this->tableSultangaziContents.'.content_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableSultangaziContentsLang.'.lang_id', 'left');
		$query->join($this->tableSettings, $this->tableSettings.'.frontend_lang_default = '.$this->tableLanguages.'.lang_id', 'left');

		$query->where($this->tableSultangaziContents.'.status', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->tableSultangaziContentsLang.'.lang_id', 'ASC');

		return $query->get()->getResult();
	}

	public function cityGuideCategoriesModel() {
		$query = $this->db->table($this->tableCityGuideCategories);
		$query->select($this->tableCityGuideCategories.'.city_guide_category_id,
						'.$this->tableCityGuideCategoriesLang.'.city_guide_category_slug,
						(CASE WHEN '.$this->tableLanguages.'.lang_id = '.$this->tableSettings.'.frontend_lang_default THEN ""
																																																					ELSE '.$this->tableLanguages.'.lang_code END) AS lang_code_end');
		$query->join($this->tableCityGuideCategoriesLang, $this->tableCityGuideCategoriesLang.'.city_guide_category_id = '.$this->tableCityGuideCategories.'.city_guide_category_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableCityGuideCategoriesLang.'.lang_id', 'left');
		$query->join($this->tableSettings, $this->tableSettings.'.frontend_lang_default = '.$this->tableLanguages.'.lang_id', 'left');

		$query->where($this->tableCityGuideCategories.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableCityGuideCategoriesLang.'.city_guide_category_name !=', '');
		$query->orderBy($this->tableCityGuideCategoriesLang.'.lang_id', 'ASC');

		return $query->get()->getResult();
	}

	public function cityGuideContentsModel() {
		$query = $this->db->table($this->tableCityGuideContents);
		$query->select($this->tableCityGuideContents.'.city_guide_content_id,
						'.$this->tableCityGuideContentsLang.'.city_guide_content_slug,
						(CASE WHEN '.$this->tableLanguages.'.lang_id = '.$this->tableSettings.'.frontend_lang_default THEN ""
																																																					ELSE '.$this->tableLanguages.'.lang_code END) AS lang_code_end');
		$query->join($this->tableCityGuideContentsLang, $this->tableCityGuideContentsLang.'.city_guide_content_id = '.$this->tableCityGuideContents.'.city_guide_content_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableCityGuideContentsLang.'.lang_id', 'left');
		$query->join($this->tableSettings, $this->tableSettings.'.frontend_lang_default = '.$this->tableLanguages.'.lang_id', 'left');

		$query->where($this->tableCityGuideContents.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableCityGuideContentsLang.'.city_guide_content_name !=', '');
		$query->orderBy($this->tableCityGuideContentsLang.'.lang_id', 'ASC');

		return $query->get()->getResult();
	}

	public function galleryCategoriesModel() {
		$query = $this->db->table($this->tableGalleryCategories);
		$query->select($this->tableGalleryCategories.'.gallery_category_id,
						'.$this->tableGalleryCategoriesLang.'.gallery_category_name,
						(CASE WHEN '.$this->tableLanguages.'.lang_id = '.$this->tableSettings.'.frontend_lang_default THEN ""
																																																					ELSE '.$this->tableLanguages.'.lang_code END) AS lang_code_end');
		$query->join($this->tableGalleryCategoriesLang, $this->tableGalleryCategoriesLang.'.gallery_category_id = '.$this->tableGalleryCategories.'.gallery_category_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableGalleryCategoriesLang.'.lang_id', 'left');
		$query->join($this->tableSettings, $this->tableSettings.'.frontend_lang_default = '.$this->tableLanguages.'.lang_id', 'left');

		$query->where($this->tableGalleryCategories.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableGalleryCategoriesLang.'.gallery_category_name !=', '');
		$query->orderBy($this->tableGalleryCategoriesLang.'.lang_id', 'ASC');

		return $query->get()->getResult();
	}

	public function galleryDetailModel() {
		$query = $this->db->table($this->tableGallery);
		$query->select($this->tableGallery.'.gallery_id,
						'.$this->tableGalleryLang.'.gallery_name,
						(CASE WHEN '.$this->tableLanguages.'.lang_id = '.$this->tableSettings.'.frontend_lang_default THEN ""
																																																					ELSE '.$this->tableLanguages.'.lang_code END) AS lang_code_end');
		$query->join($this->tableGalleryLang, $this->tableGalleryLang.'.gallery_id = '.$this->tableGallery.'.gallery_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableGalleryLang.'.lang_id', 'left');
		$query->join($this->tableSettings, $this->tableSettings.'.frontend_lang_default = '.$this->tableLanguages.'.lang_id', 'left');

		$query->where($this->tableGallery.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableGalleryLang.'.gallery_name !=', '');
		$query->orderBy($this->tableGalleryLang.'.lang_id', 'ASC');

		return $query->get()->getResult();
	}

	public function eventsModel() {
		$query = $this->db->table($this->tableEvents);
		$query->select($this->tableEvents.'.event_id,
						'.$this->tableEventsLang.'.event_slug,
						(CASE WHEN '.$this->tableLanguages.'.lang_id = '.$this->tableSettings.'.frontend_lang_default THEN ""
																																																					ELSE '.$this->tableLanguages.'.lang_code END) AS lang_code_end');
		$query->join($this->tableEventsLang, $this->tableEventsLang.'.event_id = '.$this->tableEvents.'.event_id', 'left');
		$query->join($this->tableLanguages, $this->tableLanguages.'.lang_id = '.$this->tableEventsLang.'.lang_id', 'left');
		$query->join($this->tableSettings, $this->tableSettings.'.frontend_lang_default = '.$this->tableLanguages.'.lang_id', 'left');

		$query->where($this->tableEvents.'.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableEventsLang.'.event_name !=', '');
		$query->orderBy($this->tableEventsLang.'.lang_id', 'ASC');

		return $query->get()->getResult();
	}
}
