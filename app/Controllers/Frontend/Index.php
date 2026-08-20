<?php

namespace App\Controllers\Frontend;

use CodeIgniter\Controller;

use App\Models\Frontend\IndexModel;
use App\Libraries\PopupModule;

class Index extends BaseController
{

	protected $IndexModel;
	protected $PopupModule;

	public function __construct()
	{
		$this->IndexModel = new IndexModel();
		$this->PopupModule = new PopupModule();
	}

	public function index()
	{
		$projects = $this->projects(7);
		$main_project = $projects[0];
		$projects = array_splice($projects, 1, 4);

		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH . '/index.html', [
			'page_name' => 'index',
			'head' => [
				'title' => $this->settings->site_title,
				'keywords' => $this->settings->site_keywords,
				'description' => $this->settings->site_description
			],
			'result' => [
				'president' => [
					'informations' => $this->informations()
				]
			],
			'list' => [
				'events' => $this->events(),
				'banner' => [
					'main' => $this->mainBanner(),
					'mini' => $this->miniBanner()
				],
				'announcements' => $this->announcements(),
				'fast_menu' => $this->IndexModel->fastMenuManagementModel($this->defaultLangId),
				'projects' => [
					'categories' => $this->IndexModel->projectCategoriesListModel($this->defaultLangId),
					'contents' => $projects,
					'main_project' => $main_project,
					'sport' => $this->IndexModel->sportProjectsModel($this->defaultLangId),
				],
				'news' => [
					'all' => array_merge($this->newsSlider(), []),
					'slider' => $this->newsSlider(),
					'boxes' => $this->newsBoxes()
				],
				'general_services' => $this->generalServices(),
				'referances' => $this->referances(),
				'statistics' => $this->statistics(),
				'map' => [
					'default' => $this->mapDefaultList(),
					'category' => [
						'default' => $this->IndexModel->mapCategoryDefaultModel($this->defaultLangId),
						'list' => $this->mapCategoryList()
					],
					'all' => $this->mapAllList()
				],
				'multimedia' => [
					'gallery_categories' => $this->galleryCategories(),
					'video_gallery' => $this->videoGallery()
				],
				// Eğitim kurumları: tanımlar Constants.php, metinler WebIndex dil dosyasında
				'education' => $this->educationInstitutions()
			],
			'PARAMETER' => [
				'WEB_URL_ANNOUNCEMENTS' => WEB_URL_ANNOUNCEMENTS,
				'WEB_URL_SERVICES' => WEB_URL_SERVICES,
				'WEB_URL_PROJECTS' => WEB_URL_PROJECTS,
				'WEB_URL_PROJECTS_DETAIL' => WEB_URL_PROJECTS_DETAIL,
				'WEB_URL_EVENTS' => WEB_URL_EVENTS,
				'WEB_URL_NEWS' => WEB_URL_NEWS,
				'WEB_URL_NEWS_DETAIL' => WEB_URL_NEWS_DETAIL,
				'WEB_URL_GALLERY' => WEB_URL_GALLERY,
				'WEB_URL_VIDEO_GALLERY' => WEB_URL_VIDEO_GALLERY
			],
			'footer' => [
				'popup' => $this->PopupModule->index()
			]
		]);
	}

	/**
	 * Anasayfadaki "Eğitim Kurumlarımız" alanı için kurum listesi.
	 * URL/görsel bilgileri Constants.php, metinler dil dosyalarından gelir.
	 */
	private function educationInstitutions(): array
	{
		$list = [];

		foreach (EDUCATION_INSTITUTIONS as $item) {
			$key = $item['key'];

			$list[] = [
				'url'         => $item['url'],
				'theme'       => $item['theme'],
				'image'       => FILE_PATH_ASSETS.'/'.FILE_PATH_IMAGES.'/education/'.$item['image'],
				'name'        => lang('WebIndex.education.items.'.$key.'.name'),
				'subtitle'    => lang('WebIndex.education.items.'.$key.'.subtitle'),
				'description' => lang('WebIndex.education.items.'.$key.'.description'),
			];
		}

		return $list;
	}

	public function informations()
	{
		$data = [];
		$sql = $this->general->getPresidentGeneralInformationsModel($this->defaultLangId);
		if (isNotNull($sql)) {
			$data = [
				'president_name_surname' => $sql->president_name_surname,
				'president_sub_title' => $sql->president_general_information_sub_title,
				'president_link' => $sql->president_general_information_link,
				'president_image' => [
					'base' => isNotNull($sql->president_image) ? $this->imageControl(FILE_PATH_PRESIDENT, $sql->president_image) : NULL
				],
				'president_facebook' => $sql->president_facebook,
				'president_twitter' => $sql->president_twitter,
				'president_instagram' => $sql->president_instagram,
				'president_youtube' => $sql->president_youtube
			];
		}

		return $data;
	}

	public function mainBanner()
	{
		$banner_array = [];
		$banner = $this->IndexModel->mainBannerModel($this->defaultLangId);
		if (isNotNull($banner)) {
			foreach ($banner as $row) {
				$banner_array[] = [
					'banner_name' => $row->banner_name,
					'banner_description' => $row->banner_description,
					'banner_web_image' => [
						'normal' => $row->banner_web_image,
						'base' => $row->appBanner ? $this->sultanImageControl(FILE_PATH_BANNER_MANAGEMENT, $row->banner_web_image) : $this->imageControl(FILE_PATH_BANNER_MANAGEMENT, $row->banner_web_image)
					],
					'banner_mobile_image' => [
						'normal' => $row->banner_mobile_image,
						'base' => $row->appBanner ? $this->sultanImageControl(FILE_PATH_BANNER_MANAGEMENT, $row->banner_mobile_image) : $this->imageControl(FILE_PATH_BANNER_MANAGEMENT, $row->banner_mobile_image)
					],
					'banner_link' => $row->banner_link,
					'banner_link_target' => $row->banner_link_target
				];
			}
		}

		return $banner_array;
	}

	public function miniBanner()
	{
		$banner_array = [];
		$banner = $this->IndexModel->miniBannerModel($this->defaultLangId);
		if (isNotNull($banner)) {
			foreach ($banner as $row) {
				$banner_array[] = [
					'banner_name' => $row->banner_name,
					'banner_description' => $row->banner_description,
					'banner_web_image' => [
						'normal' => $row->banner_web_image,
						'base' => $this->imageControl(FILE_PATH_BANNER_MANAGEMENT, $row->banner_web_image)
					],
					'banner_mobile_image' => [
						'normal' => $row->banner_mobile_image,
						'base' => $this->imageControl(FILE_PATH_BANNER_MANAGEMENT, $row->banner_mobile_image)
					],
					'banner_link' => $row->banner_link,
					'banner_link_target' => $row->banner_link_target
				];
			}
		}

		return $banner_array;
	}

	public function announcements()
	{
		$array = [];
		$sql = $this->IndexModel->announcementsModel($this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'announcement_name' => $row->announcement_name
				];
			}
		}

		return $array;
	}

	public function projects($category_id = null)
	{
		$array = [];
		$sql = $this->IndexModel->projectsModel($this->defaultLangId, $category_id);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'project_id' => $row->project_id,
					'project_name' => $row->project_name,
					'project_category_name' => $row->project_category_name,
					'project_slug' => $row->project_slug,
					'image' => [
						'base' => $this->imageControl(FILE_PATH_PROJECT_MEDIUM, $row->project_image)
					]
				];
			}
		}

		return $array;
	}

	public function events()
	{
		$array = [];
		$sql = $this->IndexModel->eventsModel($this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {

				// Date
				$day = NULL;
				$month = NULL;
				if ($row->event_date != '0000-00-00') {
					$date = explode('-', $row->event_date);
					$day = $date[2];
					$month = monthName($date[1]);
				}

				$array[] = [
					'event_id' => $row->event_id,
					'event_name' => $row->event_name,
					'event_date' => [
						'day' => $day,
						'month' => $month
					],
					'event_hour' => $row->event_hour != '00:00:00' ? deleteSeconds($row->event_hour) : NULL,
					'event_location' => $row->event_location,
					'event_category_name' => $row->event_category_name,
					'event_slug' => $row->event_slug,
					'image' => [
						'base' => $this->sultanImageControl(FILE_PATH_EVENTS_THUMB, $row->event_image)
					]
				];
			}
		}

		return $array;
	}

	public function newsSlider()
	{
		$array = [];
		$sql = $this->IndexModel->newsSliderModel($this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'news_id' => $row->news_id,
					'news_name' => $row->news_name,
					'news_slug' => $row->news_slug,
					'image' => [
						'thumb' => $this->sultanImageControl(FILE_PATH_NEWS, $row->news_image),
						'medium' => $this->sultanImageControl(FILE_PATH_NEWS, $row->news_image)
					]
				];
			}
		}

		return $array;
	}

	public function newsBoxes()
	{
		$array = [];
		$sql = $this->IndexModel->newsBoxesModel($this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'news_id' => $row->news_id,
					'news_name' => $row->news_name,
					'news_slug' => $row->news_slug,
					'image' => [
						'list' => $this->sultanImageControl(FILE_PATH_NEWS, $row->news_image),
						'medium' => $this->sultanImageControl(FILE_PATH_NEWS, $row->news_image)
					]
				];
			}
		}

		return $array;
	}

	public function generalServices()
	{
		$array = [];
		$sql = $this->IndexModel->generalServicesModel($this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'service_id' => $row->service_id,
					'service_name' => $row->service_name,
					'service_link' => $row->service_link,
					'service_slug' => $row->service_slug,
					'icon' => [
						'normal' => $row->service_icon,
						'base' => $this->imageControl(FILE_PATH_SERVICES, $row->service_icon)
					]
				];
			}
		}

		return $array;
	}

	public function referances()
	{
		$array = [];
		$sql = $this->IndexModel->referancesModel($this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'referance_name' => $row->referance_name,
					'referance_link' => $row->referance_link,
					'image' => [
						'base' => $this->imageControl(FILE_PATH_REFERANCES, $row->referance_image)
					]
				];
			}
		}

		return $array;
	}

	public function statistics()
	{
		$array = [];
		$sql = $this->IndexModel->statisticsModel($this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'statistic_name' => $row->statistic_name,
					'statistic_number' => $row->statistic_number
				];
			}
		}

		return $array;
	}

	public function galleryCategories()
	{
		$array = [];
		$sql = $this->IndexModel->galleryCategoriesModel($this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'gallery_category_name' => $row->gallery_category_name,
					'image' => [
						'normal' => $row->gallery_category_image,
						'base' => $this->imageControl(FILE_PATH_GALLERY, $row->gallery_category_image)
					],
					'gallery_category_slug' => slug($row->gallery_category_name),
					'gallery_category_created_date' => substr(dateFormat($row->gallery_category_created_date, 'd/m/Y'), 0, 10),
					'link' => web_url(WEB_URL_GALLERY_CATEGORY . '/' . slug($row->gallery_category_name) . '/' . $row->gallery_category_id)
				];
			}
		}

		return $array;
	}

	public function videoGallery()
	{
		$array = [];
		$sql = $this->IndexModel->videoGalleryModel($this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'video_gallery_name' => $row->video_gallery_name,
					'video_gallery_link' => $row->video_gallery_link,
					'image' => [
						'normal' => $row->video_gallery_image,
						'base' => $this->imageControl(FILE_PATH_VIDEO_GALLERY, $row->video_gallery_image)
					],
					'video_gallery_date' => dateFormat($row->video_gallery_date, 'd/m/Y')
				];
			}
		}

		return $array;
	}

	public function mapDefaultList()
	{
		$array = [];
		$coordinate_sql = $this->IndexModel->mapCoorinateDefaultModel($this->defaultLangId);
		if (isNotNull($coordinate_sql)) {
			foreach ($coordinate_sql as $key => $row) {
				$key = $key + 1;

				if ($row->map_type_id == MAP_LOCATIONS_TYPE_1) {
					$array[] = [
						'key' => $key,
						'category_slug' => str_replace('-', '_', slug($row->map_category_name)),
						'name' => $row->map_location_name,
						'responsible' => '',
						'telephone' => '',
						'map' => '',
						'coordinate' => [
							'lat' => $row->map_location_lat_coordinate,
							'long' => $row->map_location_long_coordinate
						]
					];
				} else if ($row->map_type_id == MAP_LOCATIONS_TYPE_2) {
					$array[] = [
						'key' => $key,
						'category_slug' => str_replace('-', '_', slug($row->map_category_name)),
						'name' => $row->project_name,
						'responsible' => isNotNull($row->project_responsible) ? $row->project_responsible : NULL,
						'telephone' => isNotNull($row->project_location_telephone) ? $row->project_location_telephone : NULL,
						'map' => isNotNull($row->project_location_map) ? anchor($row->project_location_map, lang('Web.map.link'), ['target' => '_blank']) : NULL,
						'coordinate' => [
							'lat' => isNotNull($row->project_lat_coordinate) ? $row->project_lat_coordinate : $row->map_location_lat_coordinate,
							'long' => isNotNull($row->project_long_coordinate) ? $row->project_long_coordinate : $row->map_location_long_coordinate
						]
					];
				}
			}
		}

		return $array;
	}

	public function mapAllList()
	{
		$array = [];
		$sql = $this->IndexModel->mapCategoryListModel($this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {

				$layer_group = NULL;
				$coordinate = [];
				$coordinate_sql = $this->IndexModel->mapCoorinateAllListModel($row->map_category_id, $this->defaultLangId);
				if (isNotNull($coordinate_sql)) {
					foreach ($coordinate_sql as $key => $value) {
						$key = $key + 1;
						$layer_group .= str_replace('-', '_', slug($value->map_category_name)) . $key . ', ';

						if ($value->map_type_id == MAP_LOCATIONS_TYPE_1) {
							$coordinate[] = [
								'key' => $key,
								'category_slug' => str_replace('-', '_', slug($value->map_category_name)),
								'name' => $value->map_location_name,
								'responsible' => '',
								'telephone' => '',
								'map' => '',
								'coordinate' => [
									'lat' => $value->map_location_lat_coordinate,
									'long' => $value->map_location_long_coordinate
								]
							];
						} else if ($value->map_type_id == MAP_LOCATIONS_TYPE_2) {
							$coordinate[] = [
								'key' => $key,
								'category_slug' => str_replace('-', '_', slug($value->map_category_name)),
								'name' => $value->project_name,
								'responsible' => isNotNull($value->project_responsible) ? $value->project_responsible : NULL,
								'telephone' => isNotNull($value->project_location_telephone) ? $value->project_location_telephone : NULL,
								'map' => isNotNull($value->project_location_map) ? anchor($value->project_location_map, lang('Web.map.link'), ['target' => '_blank']) : NULL,
								'coordinate' => [
									'lat' => isNotNull($value->project_lat_coordinate) ? $value->project_lat_coordinate : $value->map_location_lat_coordinate,
									'long' => isNotNull($value->project_long_coordinate) ? $value->project_long_coordinate : $value->map_location_long_coordinate
								]
							];
						}
					}

					$layer_group = rtrim($layer_group, ', ');
				}

				$array[] = [
					'category_id' => $row->map_category_id,
					'category_name' => $row->map_category_name,
					'category_slug' => str_replace('-', '_', slug($row->map_category_name)),
					'coordinate' => $coordinate,
					'layer_group' => $layer_group
				];
			}
		}

		return $array;
	}

	public function mapCategoryList()
	{
		$array = [];
		$sql = $this->IndexModel->mapCategoryListModel($this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'category_id' => $row->map_category_id,
					'category_name' => $row->map_category_name,
					'category_slug' => str_replace('-', '_', slug($row->map_category_name))
				];
			}
		}

		return $array;
	}
}
