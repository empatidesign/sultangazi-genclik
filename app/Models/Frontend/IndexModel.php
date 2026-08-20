<?php

namespace App\Models\Frontend;

use CodeIgniter\Model;

class IndexModel extends Model
{

	var $tableBanner = 'banner';
	var $tableBannerLang = 'banner_lang';
	var $tableAnnouncements = 'announcements';
	var $tableAnnouncementsLang = 'announcements_lang';
	var $tableProjects = 'projects';
	var $tableProjectsLang = 'projects_lang';
	var $tableProjectsImage = 'projects_image';
	var $tableProjectsCategory = 'projects_category';
	var $tableProjectsCategoryLang = 'projects_category_lang';
	var $tableEvents = 'events';
	var $tableEventsLang = 'events_lang';
	var $tableEventsCategoryLang = 'events_category_lang';
	var $tableNews = 'news';
	var $tableNewsLang = 'news_lang';
	var $tableServices = 'services';
	var $tableServicesLang = 'services_lang';
	var $tableReferances = 'referances';
	var $tableReferancesLang = 'referances_lang';
	var $tableStatistics = 'statistics';
	var $tableStatisticsLang = 'statistics_lang';
	var $tableGalleryCategories = 'gallery_categories';
	var $tableGalleryCategoriesLang = 'gallery_categories_lang';
	var $tableVideoGallery = 'video_gallery';
	var $tableVideoGalleryLang = 'video_gallery_lang';
	var $tableFastMenus = 'fast_menus';
	var $tableFastMenusLang = 'fast_menus_lang';
	var $tableMapCategories = 'map_categories';
	var $tableMapCategoriesLang = 'map_categories_lang';
	var $tableMapLocations = 'map_locations';
	var $tableMapLocationsLang = 'map_locations_lang';
	var $tablePages = 'pages';
	var $tablePagesLang = 'pages_lang';

	public function mainBannerModel(int $lang_id)
	{
		$appdb = db_connect('application');
		$appQuery = $appdb->table($this->tableBanner);
		$appQuery->select($this->tableBanner . '.banner_web_image,
						' . $this->tableBanner . '.banner_mobile_image,
						' . $this->tableBanner . '.banner_link_target,
						' . $this->tableBannerLang . '.banner_name,
						' . $this->tableBannerLang . '.banner_description,
						' . $this->tableBannerLang . '.banner_link,
						(CASE WHEN ' . $this->tableBanner . '.banner_link_target = "' . FORM_ACTIVE_NUMBER . '" THEN "' . MENU_MANAGEMENT_TARGET_NEW_WINDOW . '"
																																														ELSE "' . MENU_MANAGEMENT_TARGET_SAME_WINDOW . '" END) AS banner_link_target');
		$appQuery->join($this->tableBannerLang, $this->tableBannerLang . '.banner_id = ' . $this->tableBanner . '.banner_id AND ' . $this->tableBannerLang . '.lang_id = ' . $lang_id, 'left');

		$appQuery->where($this->tableBanner . '.status', FORM_ACTIVE_NUMBER);
		$appQuery->where($this->tableBanner . '.status_genclik', FORM_ACTIVE_NUMBER);
		$appQuery->where($this->tableBanner . '.banner_type', BANNER_TYPE_1);
		$appQuery->where('if(' . $this->tableBanner . '.banner_start_date != "0000-00-00" AND ' . $this->tableBanner . '.banner_end_date != "0000-00-00",
									 if(' . $this->tableBanner . '.banner_start_date <= CURDATE() AND ' . $this->tableBanner . '.banner_end_date >= CURDATE(), TRUE, FALSE), TRUE)');
		$appQuery->orderBy($this->tableBanner . '.banner_order', 'ASC');
		$appBanner = $appQuery->get()->getResult();

		$tempBanner = [];
		foreach ($appBanner as $banner) {
			$banner->appBanner = 1;
			$tempBanner[] = $banner;
		}
		$appBanner = $tempBanner;

		$query = $this->db->table($this->tableBanner);
		$query->select($this->tableBanner . '.banner_web_image,
		' . $this->tableBanner . '.banner_mobile_image,
		' . $this->tableBanner . '.banner_link_target,
		' . $this->tableBannerLang . '.banner_name,
		' . $this->tableBannerLang . '.banner_description,
		' . $this->tableBannerLang . '.banner_link,
		(CASE WHEN ' . $this->tableBanner . '.banner_link_target = "' . FORM_ACTIVE_NUMBER . '" THEN "' . MENU_MANAGEMENT_TARGET_NEW_WINDOW . '"
		ELSE "' . MENU_MANAGEMENT_TARGET_SAME_WINDOW . '" END) AS banner_link_target');
		$query->join($this->tableBannerLang, $this->tableBannerLang . '.banner_id = ' . $this->tableBanner . '.banner_id AND ' . $this->tableBannerLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableBanner . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableBanner . '.banner_type', BANNER_TYPE_1);
		$query->where('if(' . $this->tableBanner . '.banner_start_date != "0000-00-00" AND ' . $this->tableBanner . '.banner_end_date != "0000-00-00",
		if(' . $this->tableBanner . '.banner_start_date <= CURDATE() AND ' . $this->tableBanner . '.banner_end_date >= CURDATE(), TRUE, FALSE), TRUE)');
		$query->orderBy($this->tableBanner . '.banner_order', 'ASC');
		$banners = $query->get()->getResult();

		$tempBanner = [];
		foreach ($banners as $banner) {
			$banner->appBanner = 0;
			$tempBanner[] = $banner;
		}
		$banners = $tempBanner;

		return array_merge($banners, $appBanner);
	}

	public function miniBannerModel(int $lang_id)
	{
		$query = $this->db->table($this->tableBanner);
		$query->select($this->tableBanner . '.banner_web_image,
						' . $this->tableBanner . '.banner_mobile_image,
						' . $this->tableBanner . '.banner_link_target,
						' . $this->tableBannerLang . '.banner_name,
						' . $this->tableBannerLang . '.banner_description,
						' . $this->tableBannerLang . '.banner_link,
						(CASE WHEN ' . $this->tableBanner . '.banner_link_target = "' . FORM_ACTIVE_NUMBER . '" THEN "' . MENU_MANAGEMENT_TARGET_NEW_WINDOW . '" ELSE "' . MENU_MANAGEMENT_TARGET_SAME_WINDOW . '" END) AS banner_link_target');
		$query->join($this->tableBannerLang, $this->tableBannerLang . '.banner_id = ' . $this->tableBanner . '.banner_id AND ' . $this->tableBannerLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableBanner . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableBanner . '.banner_type', BANNER_TYPE_2);
		$query->where('if(' . $this->tableBanner . '.banner_start_date != "0000-00-00" AND ' . $this->tableBanner . '.banner_end_date != "0000-00-00",
									 if(' . $this->tableBanner . '.banner_start_date <= CURDATE() AND ' . $this->tableBanner . '.banner_end_date >= CURDATE(), TRUE, FALSE), TRUE)');
		$query->orderBy($this->tableBanner . '.banner_order', 'ASC');

		return $query->get()->getResult();
	}

	public function projectCategoriesListModel(int $lang_id)
	{
		$query = $this->db->table($this->tableProjectsCategory);
		$query->select($this->tableProjectsCategory . '.project_category_id,
						' . $this->tableProjectsCategory . '.project_category_icon,
						' . $this->tableProjectsCategoryLang . '.project_category_name,
						' . $this->tableProjectsCategoryLang . '.project_category_slug');
		$query->join($this->tableProjectsCategoryLang, $this->tableProjectsCategoryLang . '.project_category_id = ' . $this->tableProjectsCategory . '.project_category_id AND ' . $this->tableProjectsCategoryLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableProjectsCategory . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableProjectsCategory . '.home_page', FORM_ACTIVE_NUMBER);
		$query->where($this->tableProjectsCategoryLang . '.project_category_name !=', '');
		$query->orderBy($this->tableProjectsCategory . '.project_category_id', 'ASC');

		return $query->get()->getResult();
	}

	public function projectsModel(int $lang_id, int $category_id = null)
	{
		$query = $this->db->table($this->tableProjects);
		$query->select($this->tableProjects . '.project_id,
						' . $this->tableProjectsLang . '.project_name,
						' . $this->tableProjectsLang . '.project_slug,
						' . $this->tableProjectsCategoryLang . '.project_category_name,
						' . $this->tableProjectsImage . '.project_image');
		$query->join($this->tableProjectsLang, $this->tableProjectsLang . '.project_id = ' . $this->tableProjects . '.project_id AND ' . $this->tableProjectsLang . '.lang_id = ' . $lang_id, 'left');
		$query->join($this->tableProjectsCategoryLang, $this->tableProjectsCategoryLang . '.project_category_id = ' . $this->tableProjects . '.project_category_id AND ' . $this->tableProjectsCategoryLang . '.lang_id = ' . $lang_id, 'left');
		$query->join($this->tableProjectsImage, $this->tableProjectsImage . '.project_id = ' . $this->tableProjects . '.project_id AND ' . $this->tableProjectsImage . '.project_image_default = 1', 'left');

		$query->where($this->tableProjects . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableProjects . '.home_page', FORM_ACTIVE_NUMBER);
		if ($category_id) $query->where($this->tableProjects . '.project_category_id', $category_id);
		$query->where($this->tableProjectsLang . '.project_name !=', '');
		$query->orderBy($this->tableProjects . '.project_id', 'DESC');
		$query->limit(10);

		return $query->get()->getResult();
	}
public function sportProjectsModel(int $lang_id)
{
    $query = $this->db->table($this->tableProjects);

    $query->select("
        {$this->tableProjects}.project_id,
        {$this->tableProjectsLang}.project_name,
        {$this->tableProjectsLang}.project_slug,
        {$this->tableProjectsCategoryLang}.project_category_name,
        img_default.project_image AS project_image,
        img_second.project_image AS project_image2
    ");

    // JOIN: proje dili
    $query->join(
        $this->tableProjectsLang,
        "{$this->tableProjectsLang}.project_id = {$this->tableProjects}.project_id
         AND {$this->tableProjectsLang}.lang_id = {$lang_id}",
        'left'
    );

    // JOIN: kategori dili
    $query->join(
        $this->tableProjectsCategoryLang,
        "{$this->tableProjectsCategoryLang}.project_category_id = {$this->tableProjects}.project_category_id
         AND {$this->tableProjectsCategoryLang}.lang_id = {$lang_id}",
        'left'
    );

    // JOIN 1: ana resim (default = 1)
    $query->join(
        "{$this->tableProjectsImage} AS img_default",
        "img_default.project_id = {$this->tableProjects}.project_id
         AND img_default.project_image_default = 1",
        'left'
    );

    // JOIN 2: ikinci resim (order = 1)
    $query->join(
        "{$this->tableProjectsImage} AS img_second",
        "img_second.project_id = {$this->tableProjects}.project_id
         AND img_second.project_image_order = 1",
        'left'
    );

    // Filtreler
    $query->where("{$this->tableProjects}.status", FORM_ACTIVE_NUMBER);
    $query->where("{$this->tableProjects}.home_page", FORM_ACTIVE_NUMBER);
    $query->where("{$this->tableProjects}.project_category_id", SPORT_PROJECT_CATEGORY_ID);
    $query->where("{$this->tableProjectsLang}.project_name !=", '');
    $query->orderBy("{$this->tableProjects}.project_id", 'DESC');

    return $query->get()->getResult();
}

	public function eventsModel(int $lang_id)
	{
		$appDb = db_connect('application');
		$query = $this->db->table($this->tableEvents);
		$query->select($this->tableEvents . '.event_id,
						' . $this->tableEvents . '.event_date,
						' . $this->tableEvents . '.event_hour,
						' . $this->tableEvents . '.event_location,
						' . $this->tableEvents . '.event_image,
						' . $this->tableEventsLang . '.event_name,
						' . $this->tableEventsLang . '.event_slug,
						' . $this->tableEventsCategoryLang . '.event_category_name');
		$query->join($this->tableEventsLang, $this->tableEventsLang . '.event_id = ' . $this->tableEvents . '.event_id AND ' . $this->tableEventsLang . '.lang_id = ' . $lang_id, 'left');
		$query->join($this->tableEventsCategoryLang, $this->tableEventsCategoryLang . '.event_category_id = ' . $this->tableEvents . '.event_category_id AND ' . $this->tableEventsCategoryLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableEvents . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableEventsLang . '.event_name !=', '');
		$query->orderBy($this->tableEvents . '.event_date', 'DESC');
		$query->limit(10);

		return $query->get()->getResult();
	}

	public function announcementsModel(int $lang_id)
	{
		$query = $this->db->table($this->tableAnnouncements);
		$query->select($this->tableAnnouncementsLang . '.announcement_name');
		$query->join($this->tableAnnouncementsLang, $this->tableAnnouncementsLang . '.announcement_id = ' . $this->tableAnnouncements . '.announcement_id AND ' . $this->tableAnnouncementsLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableAnnouncements . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableAnnouncementsLang . '.announcement_name !=', '');
		$query->orderBy($this->tableAnnouncements . '.announcement_created_date', 'DESC');
		$query->limit(10);

		return $query->get()->getResult();
	}

	public function newsSliderModel(int $lang_id)
	{
		$appdb = db_connect('application');
		$appQuery = $appdb->table($this->tableNews);
		$appQuery->select($this->tableNews . '.news_id,
						' . $this->tableNews . '.news_image,
						' . $this->tableNewsLang . '.news_name,
						' . $this->tableNewsLang . '.news_slug');
		$appQuery->join($this->tableNewsLang, $this->tableNewsLang . '.news_id = ' . $this->tableNews . '.news_id AND ' . $this->tableNewsLang . '.lang_id = ' . $lang_id, 'left');

		$appQuery->where($this->tableNews . '.status', FORM_ACTIVE_NUMBER);
		$appQuery->where($this->tableNews . '.status_genclik', FORM_ACTIVE_NUMBER);
		$appQuery->where($this->tableNewsLang . '.news_name !=', '');
		$appQuery->orderBy($this->tableNews . '.news_created_date', 'DESC');
		$appQuery->limit(5);
		$appNews = $appQuery->get()->getResult();

		$query = $this->db->table($this->tableNews);
		$query->select($this->tableNews . '.news_id,
						' . $this->tableNews . '.news_image,
						' . $this->tableNewsLang . '.news_name,
						' . $this->tableNewsLang . '.news_slug');
		$query->join($this->tableNewsLang, $this->tableNewsLang . '.news_id = ' . $this->tableNews . '.news_id AND ' . $this->tableNewsLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableNews . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableNewsLang . '.news_name !=', '');
		$query->orderBy($this->tableNews . '.news_created_date', 'DESC');
		$query->limit(5);
		$news = $query->get()->getResult();

		return array_merge($appNews, $news);
	}

	public function newsBoxesModel(int $lang_id)
	{
		$appdb = db_connect('application');
		$appQuery = $appdb->table($this->tableNews);
		$appQuery->select($this->tableNews . '.news_id,
						' . $this->tableNews . '.news_image,
						' . $this->tableNewsLang . '.news_name,
						' . $this->tableNewsLang . '.news_slug');
		$appQuery->join($this->tableNewsLang, $this->tableNewsLang . '.news_id = ' . $this->tableNews . '.news_id AND ' . $this->tableNewsLang . '.lang_id = ' . $lang_id, 'left');

		$appQuery->where($this->tableNews . '.status', FORM_ACTIVE_NUMBER);
		$appQuery->where($this->tableNewsLang . '.news_name !=', '');
		$appQuery->where($this->tableNews . '.status_genclik', FORM_ACTIVE_NUMBER);
		$appQuery->orderBy($this->tableNews . '.news_created_date', 'DESC');
		$appQuery->limit(4, 5);
		$appNews = $appQuery->get()->getResult();

		$query = $appdb->table($this->tableNews);
		$query->select($this->tableNews . '.news_id,
						' . $this->tableNews . '.news_image,
						' . $this->tableNewsLang . '.news_name,
						' . $this->tableNewsLang . '.news_slug');
		$query->join($this->tableNewsLang, $this->tableNewsLang . '.news_id = ' . $this->tableNews . '.news_id AND ' . $this->tableNewsLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableNews . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableNewsLang . '.news_name !=', '');
		$query->where($this->tableNews . '.status_genclik', FORM_ACTIVE_NUMBER);
		$query->orderBy($this->tableNews . '.news_created_date', 'DESC');
		$query->limit(4, 5);
		$news = $query->get()->getResult();

		return array_merge($news, $appNews);
	}

	public function generalServicesModel(int $lang_id)
	{
		$query = $this->db->table($this->tableServices);
		$query->select($this->tableServices . '.service_id,
						' . $this->tableServices . '.service_icon,
						' . $this->tableServicesLang . '.service_name,
						' . $this->tableServicesLang . '.service_link,
						' . $this->tableServicesLang . '.service_slug');
		$query->join($this->tableServicesLang, $this->tableServicesLang . '.service_id = ' . $this->tableServices . '.service_id AND ' . $this->tableServicesLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableServices . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableServices . '.service_type', SERVICES_TYPE_1);
		$query->where($this->tableServicesLang . '.service_name !=', '');
		$query->orderBy($this->tableServices . '.service_order', 'ASC');

		return $query->get()->getResult();
	}

	public function referancesModel(int $lang_id)
	{
		$query = $this->db->table($this->tableReferances);
		$query->select($this->tableReferances . '.referance_image,
						' . $this->tableReferancesLang . '.referance_name,
						' . $this->tableReferancesLang . '.referance_link');
		$query->join($this->tableReferancesLang, $this->tableReferancesLang . '.referance_id = ' . $this->tableReferances . '.referance_id AND ' . $this->tableReferancesLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableReferances . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableReferances . '.referance_image !=', '');
		$query->where($this->tableReferancesLang . '.referance_name !=', '');
		$query->orderBy($this->tableReferances . '.referance_id', 'ASC');

		return $query->get()->getResult();
	}
	
	public function statisticsModel(int $lang_id)
	{
		$query = $this->db->table($this->tableStatistics);
		$query->select([
			$this->tableStatisticsLang . '.statistic_name',
			$this->tableStatistics . '.statistic_number',
		]);
		$query->join($this->tableStatisticsLang, $this->tableStatisticsLang . '.statistic_id = ' . $this->tableStatistics . '.statistic_id AND ' . $this->tableStatisticsLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableStatistics . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableStatisticsLang . '.statistic_name !=', '');
		$query->orderBy($this->tableStatistics . '.statistic_id', 'ASC');

		return $query->get()->getResult();
	}

	public function galleryCategoriesModel(int $lang_id)
	{
		$query = $this->db->table($this->tableGalleryCategories);
		$query->select($this->tableGalleryCategories . '.gallery_category_id,
						' . $this->tableGalleryCategories . '.gallery_category_image,
						' . $this->tableGalleryCategories . '.gallery_category_created_date,
						' . $this->tableGalleryCategoriesLang . '.gallery_category_name');
		$query->join($this->tableGalleryCategoriesLang, $this->tableGalleryCategoriesLang . '.gallery_category_id = ' . $this->tableGalleryCategories . '.gallery_category_id AND ' . $this->tableGalleryCategoriesLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableGalleryCategories . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableGalleryCategoriesLang . '.gallery_category_name !=', '');
		$query->orderBy($this->tableGalleryCategories . '.gallery_category_created_date', 'DESC');
		$query->limit(6);

		return $query->get()->getResult();
	}

	public function videoGalleryModel(int $lang_id)
	{
		$query = $this->db->table($this->tableVideoGallery);
		$query->select($this->tableVideoGallery . '.video_gallery_link,
						' . $this->tableVideoGallery . '.video_gallery_image,
						' . $this->tableVideoGallery . '.video_gallery_date,
						' . $this->tableVideoGalleryLang . '.video_gallery_name');
		$query->join($this->tableVideoGalleryLang, $this->tableVideoGalleryLang . '.video_gallery_id = ' . $this->tableVideoGallery . '.video_gallery_id AND ' . $this->tableVideoGalleryLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableVideoGallery . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableVideoGalleryLang . '.video_gallery_name !=', '');
		$query->orderBy($this->tableVideoGallery . '.video_gallery_date', 'DESC');
		$query->limit(6);

		return $query->get()->getResult();
	}

	public function fastMenuManagementModel(int $lang_id, int $parent_menu = MENU_MANAGEMENT_PARENT_MENU)
	{
		$query = $this->db->table($this->tableFastMenus);
		$query->select($this->tableFastMenus . '.menu_id,
						' . $this->tableFastMenus . '.menu_type,
						' . $this->tableFastMenus . '.menu_page_id,
						' . $this->tableFastMenus . '.menu_service_id,
						' . $this->tableFastMenus . '.menu_target,
						' . $this->tableFastMenus . '.menu_icon,
						' . $this->tableFastMenusLang . '.menu_name,
						' . $this->tableFastMenusLang . '.menu_link');
		$query->join($this->tableFastMenusLang, $this->tableFastMenusLang . '.menu_id = ' . $this->tableFastMenus . '.menu_id AND ' . $this->tableFastMenusLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableFastMenus . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableFastMenus . '.menu_parent_id', $parent_menu);
		$query->where($this->tableFastMenusLang . '.menu_name !=', '');
		$query->orderBy($this->tableFastMenus . '.menu_order', 'ASC');

		$result = $query->get()->getResult();

		$menu = [];
		if (isNotNull($result)) {
			foreach ($result as $row) {
				$menu[] = [
					'menu_name' => $row->menu_name,
					'menu_icon' => $row->menu_icon,
					'menu_link' => $this->fastMenuLinkModel($row->menu_type, $row->menu_link, $row->menu_page_id, $row->menu_service_id, $lang_id),
					'menu_target' => $row->menu_target,
					'submenu' => $this->fastMenuManagementModel($lang_id, $row->menu_id)
				];
			}
		}

		return $menu;
	}

	public function fastMenuLinkModel(int $menu_type, string $menu_link, int $page_id, int $service_id, int $lang_id)
	{

		$return = NULL;
		if ($menu_type == MENU_MANAGEMENT_TYPE_PAGES) { // Pages

			$query = $this->db->table($this->tablePages);
			$query->select($this->tablePagesLang . '.page_id,
							' . $this->tablePagesLang . '.page_slug');
			$query->join($this->tablePagesLang, $this->tablePagesLang . '.page_id = ' . $this->tablePages . '.page_id AND ' . $this->tablePagesLang . '.lang_id = ' . $lang_id, 'left');

			$query->where($this->tablePages . '.page_id', $page_id);
			$query->limit(1);

			$result = $query->get()->getRow();
			if (isNotNull($result)) {
				$return = web_url(WEB_URL_PAGES . '/' . $result->page_slug . '/' . $result->page_id);
			}
		} elseif ($menu_type == MENU_MANAGEMENT_TYPE_SERVICES) { // Services

			$query = $this->db->table($this->tableServices);
			$query->select($this->tableServices . '.service_id,
							' . $this->tableServicesLang . '.service_slug');
			$query->join($this->tableServicesLang, $this->tableServicesLang . '.service_id = ' . $this->tableServices . '.service_id AND ' . $this->tableServicesLang . '.lang_id = ' . $lang_id, 'left');

			$query->where($this->tableServices . '.service_id', $service_id);
			$query->limit(1);

			$result = $query->get()->getRow();
			if (isNotNull($result)) {
				$return = web_url(WEB_URL_SERVICES . '/' . $result->service_slug . '/' . $result->service_id);
			}
		} elseif ($menu_type == MENU_MANAGEMENT_TYPE_LINK) { // Link

			if (substr($menu_link, 0, 8) == 'https://' || substr($menu_link, 0, 8) == 'http://') {
				$menu_link = $menu_link;
			} else {
				$menu_link = web_url($menu_link);
			}

			$return = $menu_link;
		}

		return $return;
	}

	public function mapCoorinateDefaultModel(int $lang_id)
	{
		$query = $this->db->table($this->tableMapLocations);
		$query->select($this->tableMapLocations . '.map_type_id,
						' . $this->tableMapLocations . '.map_location_lat_coordinate,
						' . $this->tableMapLocations . '.map_location_long_coordinate,
						' . $this->tableMapLocationsLang . '.map_location_name,
						' . $this->tableMapCategoriesLang . '.map_category_name,
						' . $this->tableProjects . '.project_responsible,
						' . $this->tableProjects . '.project_location_telephone,
						' . $this->tableProjects . '.project_location_map,
						' . $this->tableProjects . '.project_lat_coordinate,
						' . $this->tableProjects . '.project_long_coordinate,
						' . $this->tableProjectsLang . '.project_name');
		$query->join($this->tableMapLocationsLang, $this->tableMapLocationsLang . '.map_location_id = ' . $this->tableMapLocations . '.map_location_id AND ' . $this->tableMapLocationsLang . '.lang_id = ' . $lang_id, 'left');
		$query->join($this->tableMapCategories, $this->tableMapCategories . '.map_category_id = ' . $this->tableMapLocations . '.map_category_id', 'left');
		$query->join($this->tableMapCategoriesLang, $this->tableMapCategoriesLang . '.map_category_id = ' . $this->tableMapCategories . '.map_category_id AND ' . $this->tableMapCategoriesLang . '.lang_id = ' . $lang_id, 'left');
		$query->join($this->tableProjects, $this->tableProjects . '.project_id = ' . $this->tableMapLocations . '.map_project_id AND ' . $this->tableProjects . '.status = ' . FORM_ACTIVE_NUMBER, 'left');
		$query->join($this->tableProjectsLang, $this->tableProjectsLang . '.project_id = ' . $this->tableProjects . '.project_id AND ' . $this->tableProjectsLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableMapLocations . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableMapCategories . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableMapCategories . '.map_category_default', DEFAULT_RECORD_ACTIVE);
		$query->where($this->tableMapLocationsLang . '.map_location_name !=', '');
		$query->orderBy($this->tableMapLocations . '.map_location_created_date', 'DESC');

		return $query->get()->getResult();
	}

	public function mapCategoryDefaultModel(int $lang_id)
	{
		$query = $this->db->table($this->tableMapCategories);
		$query->select($this->tableMapCategoriesLang . '.map_category_name');
		$query->join($this->tableMapCategoriesLang, $this->tableMapCategoriesLang . '.map_category_id = ' . $this->tableMapCategories . '.map_category_id AND ' . $this->tableMapCategoriesLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableMapCategories . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableMapCategories . '.map_category_default', DEFAULT_RECORD_ACTIVE);
		$query->where($this->tableMapCategoriesLang . '.map_category_name !=', '');
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function mapCategoryListModel(int $lang_id)
	{
		$query = $this->db->table($this->tableMapCategories);
		$query->select($this->tableMapCategories . '.map_category_id,
						' . $this->tableMapCategoriesLang . '.map_category_name,
						(SELECT COUNT(1) FROM ' . $this->tableMapLocations . ' WHERE map_category_id = ' . $this->tableMapCategories . '.map_category_id AND ' . $this->tableMapCategories . '.map_category_default = ' . DEFAULT_RECORD_PASSIVE . ') AS total_locations');
		$query->join($this->tableMapCategoriesLang, $this->tableMapCategoriesLang . '.map_category_id = ' . $this->tableMapCategories . '.map_category_id AND ' . $this->tableMapCategoriesLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableMapCategories . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableMapCategoriesLang . '.map_category_name !=', '');
		$query->orderBy($this->tableMapCategoriesLang . '.map_category_name', 'ASC');
		$query->having('total_locations > 0');

		return $query->get()->getResult();
	}

	public function mapCoorinateAllListModel(int $map_category_id, int $lang_id)
	{
		$query = $this->db->table($this->tableMapLocations);
		$query->select($this->tableMapLocations . '.map_type_id,
						' . $this->tableMapLocations . '.map_location_lat_coordinate,
						' . $this->tableMapLocations . '.map_location_long_coordinate,
						' . $this->tableMapLocationsLang . '.map_location_name,
						' . $this->tableMapCategoriesLang . '.map_category_name,
						' . $this->tableProjects . '.project_responsible,
						' . $this->tableProjects . '.project_location_telephone,
						' . $this->tableProjects . '.project_location_map,
						' . $this->tableProjects . '.project_lat_coordinate,
						' . $this->tableProjects . '.project_long_coordinate,
						' . $this->tableProjectsLang . '.project_name');
		$query->join($this->tableMapLocationsLang, $this->tableMapLocationsLang . '.map_location_id = ' . $this->tableMapLocations . '.map_location_id AND ' . $this->tableMapLocationsLang . '.lang_id = ' . $lang_id, 'left');
		$query->join($this->tableMapCategories, $this->tableMapCategories . '.map_category_id = ' . $this->tableMapLocations . '.map_category_id', 'left');
		$query->join($this->tableMapCategoriesLang, $this->tableMapCategoriesLang . '.map_category_id = ' . $this->tableMapCategories . '.map_category_id AND ' . $this->tableMapCategoriesLang . '.lang_id = ' . $lang_id, 'left');
		$query->join($this->tableProjects, $this->tableProjects . '.project_id = ' . $this->tableMapLocations . '.map_project_id AND ' . $this->tableProjects . '.status = ' . FORM_ACTIVE_NUMBER, 'left');
		$query->join($this->tableProjectsLang, $this->tableProjectsLang . '.project_id = ' . $this->tableProjects . '.project_id AND ' . $this->tableProjectsLang . '.lang_id = ' . $lang_id, 'left');

		$query->where($this->tableMapLocations . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableMapCategories . '.status', FORM_ACTIVE_NUMBER);
		$query->where($this->tableMapCategories . '.map_category_default', DEFAULT_RECORD_PASSIVE);
		$query->where($this->tableMapLocations . '.map_category_id', $map_category_id);
		$query->where($this->tableMapLocationsLang . '.map_location_name !=', '');
		$query->orderBy($this->tableMapLocations . '.map_location_created_date', 'DESC');

		return $query->get()->getResult();
	}
}
