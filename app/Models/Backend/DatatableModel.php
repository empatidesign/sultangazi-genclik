<?php

namespace App\Models\Backend;

use CodeIgniter\Model;

class DatatableModel extends Model
{

	protected $general;
	protected $defaultLangId;
	protected $settings;

	public function initialize()
	{
		$this->general = new GeneralModel();
		$this->defaultLangId = $this->general->languagesDefaultModel()[0];
		if (isNotNull($this->defaultLangId)) {
			$this->settings = $this->general->getSettingsModel($this->defaultLangId);
		}
	}

	public function GetDatatables(string $table, array $column = [], array $search = [], array $orderBy = [], array $where = [], string $result, string $group_by = NULL, bool $appDb = false)
	{
		if ($appDb) {
			$appDbCon = db_connect('application');
			$query = $appDbCon->table($table);
			$query->where($table . '.status_genclik', FORM_ACTIVE_NUMBER);
		} else $query = $this->db->table($table);

		/*****************************************************/

		if ($table == 'users') {
			$query->select($table . '.*,
						  user_types.user_type_name');
			$query->join('user_types', 'user_types.user_type_id = ' . $table . '.user_type_id', 'left');
		}

		/*****************************************************/

		if ($table == 'cities') {
			$query->select($table . '.*,
						  countries.country_name');
			$query->join('countries', 'countries.country_id = ' . $table . '.country_id', 'left');
		}

		/*****************************************************/

		if ($table == 'districts') {
			$query->select($table . '.*,
							countries.country_name,
							cities.city_name');
			$query->join('countries', 'countries.country_id = ' . $table . '.country_id', 'left');
			$query->join('cities', 'cities.city_id = ' . $table . '.city_id', 'left');
		}

		/*****************************************************/

		if ($table == 'languages') {
			$query->select($table . '.*,
							f.frontend_lang_default,
							b.backend_lang_default');
			$query->join('settings AS f', 'f.frontend_lang_default = ' . $table . '.lang_id', 'left');
			$query->join('settings AS b', 'b.backend_lang_default = ' . $table . '.lang_id', 'left');
		}

		/*****************************************************/

		if ($table == 'president_contents') {
			$query->select($table . '.*,
						  president_contents_lang.president_content_name,
						  president_contents_lang.president_content_description,
						  president_contents_lang.president_content_meta_title,
						  president_contents_lang.president_content_meta_keywords,
						  president_contents_lang.president_content_meta_description');
			$query->join('president_contents_lang', 'president_contents_lang.president_content_id = ' . $table . '.president_content_id AND president_contents_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'pages') {
			$query->select($table . '.*,
						  pages_lang.page_name,
						  pages_lang.page_description,
						  pages_lang.page_meta_title,
						  pages_lang.page_meta_keywords,
						  pages_lang.page_meta_description');
			$query->join('pages_lang', 'pages_lang.page_id = ' . $table . '.page_id AND pages_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'contracts') {
			$query->select($table . '.*,
						  contracts_lang.contract_name,
						  contracts_lang.contract_description,
						  contracts_lang.contract_meta_title,
						  contracts_lang.contract_meta_keywords,
						  contracts_lang.contract_meta_description');
			$query->join('contracts_lang', 'contracts_lang.contract_id = ' . $table . '.contract_id AND contracts_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'contact_information') {
			$query->select($table . '.*,
						  contact_information_lang.contact_title,
							contact_information_lang.contact_address,
							contact_information_lang.contact_working_hours');
			$query->join('contact_information_lang', 'contact_information_lang.contact_id = ' . $table . '.contact_id AND contact_information_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'referances') {
			$query->select($table . '.*,
						  referances_lang.referance_name');
			$query->join('referances_lang', 'referances_lang.referance_id = ' . $table . '.referance_id AND referances_lang.lang_id = ' . $this->defaultLangId, 'left');
		}


		/*****************************************************/

		if ($table == 'statistics') {
			$query->select($table . '.*,
						  statistics_lang.statistic_name');
			$query->join('statistics_lang', 'statistics_lang.statistic_id = ' . $table . '.statistic_id AND statistics_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'services') {
			$query->select($table . '.*,
						  services_lang.service_name');
			$query->join('services_lang', 'services_lang.service_id = ' . $table . '.service_id AND services_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'municipal_councils') {
			$query->select($table . '.*,
						  municipal_councils_lang.municipal_council_sub_title');
			$query->join('municipal_councils_lang', 'municipal_councils_lang.municipal_council_id = ' . $table . '.municipal_council_id AND municipal_councils_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'council_members') {
			$query->select($table . '.*,
						  council_members_lang.council_member_sub_title');
			$query->join('council_members_lang', 'council_members_lang.council_member_id = ' . $table . '.council_member_id AND council_members_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'vice_presidents') {
			$query->select($table . '.*,
						  vice_presidents_lang.vice_president_sub_title');
			$query->join('vice_presidents_lang', 'vice_presidents_lang.vice_president_id = ' . $table . '.vice_president_id AND vice_presidents_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'parliamentary_agenda') {
			$query->select($table . '.*,
						  parliamentary_agenda_lang.parliamentary_agenda_name');
			$query->join('parliamentary_agenda_lang', 'parliamentary_agenda_lang.parliamentary_agenda_id = ' . $table . '.parliamentary_agenda_id AND parliamentary_agenda_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'activity_report') {
			$query->select($table . '.*,
						  activity_report_lang.activity_report_name');
			$query->join('activity_report_lang', 'activity_report_lang.activity_report_id = ' . $table . '.activity_report_id AND activity_report_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'strategic_plan_and_performance') {
			$query->select($table . '.*,
						  strategic_plan_and_performance_lang.strategic_plan_name');
			$query->join('strategic_plan_and_performance_lang', 'strategic_plan_and_performance_lang.strategic_plan_id = ' . $table . '.strategic_plan_id AND strategic_plan_and_performance_lang.lang_id = ' . $this->defaultLangId, 'left');
		}
		if ($table == 'plan_and_program') {
			$query->select($table . '.*,
						  plan_and_program_lang.strategic_plan_name');
			$query->join('plan_and_program_lang', 'plan_and_program_lang.strategic_plan_id = ' . $table . '.strategic_plan_id AND plan_and_program_lang.lang_id = ' . $this->defaultLangId, 'left');
		}
		if ($table == 'press_release') {
			$query->select($table . '.*,
						  press_release_lang.strategic_plan_name');
			$query->join('press_release_lang', 'press_release_lang.strategic_plan_id = ' . $table . '.strategic_plan_id AND press_release_lang.lang_id = ' . $this->defaultLangId, 'left');
		}
		if ($table == 'internal_control') {
			$query->select($table . '.*,
						  internal_control_lang.strategic_plan_name');
			$query->join('internal_control_lang', 'internal_control_lang.strategic_plan_id = ' . $table . '.strategic_plan_id AND internal_control_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'directorates') {
			$query->select($table . '.*,
						  directorates_lang.directorates_name');
			$query->join('directorates_lang', 'directorates_lang.directorates_id = ' . $table . '.directorates_id AND directorates_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'directorates_file') {
			$query->select($table . '.*,
						  directorates_file_lang.directorates_file_name,
							directorate_categories_lang.directorate_category_name');
			$query->join('directorates_file_lang', 'directorates_file_lang.directorates_file_id = ' . $table . '.directorates_file_id AND directorates_file_lang.lang_id = ' . $this->defaultLangId, 'left');
			$query->join('directorate_categories_lang', 'directorate_categories_lang.directorate_category_id = ' . $table . '.directorate_category_id AND directorate_categories_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'directorate_categories') {
			$query->select($table . '.*,
						  directorate_categories_lang.directorate_category_name');
			$query->join('directorate_categories_lang', 'directorate_categories_lang.directorate_category_id = ' . $table . '.directorate_category_id AND directorate_categories_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'organization_chart') {
			$query->select($table . '.*,
						  organization_chart_lang.organization_chart_name,
						  organization_chart_lang.organization_chart_link');
			$query->join('organization_chart_lang', 'organization_chart_lang.organization_chart_id = ' . $table . '.organization_chart_id AND organization_chart_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'sultangazi_contents') {
			$query->select($table . '.*,
						  sultangazi_contents_lang.content_name,
						  sultangazi_contents_lang.content_description,
						  sultangazi_contents_lang.content_meta_title,
						  sultangazi_contents_lang.content_meta_keywords,
						  sultangazi_contents_lang.content_meta_description');
			$query->join('sultangazi_contents_lang', 'sultangazi_contents_lang.content_id = ' . $table . '.content_id AND sultangazi_contents_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'sultangazi_video_gallery') {
			$query->select($table . '.*,
						  sultangazi_video_gallery_lang.sultangazi_video_gallery_name');
			$query->join('sultangazi_video_gallery_lang', 'sultangazi_video_gallery_lang.sultangazi_video_gallery_id = ' . $table . '.sultangazi_video_gallery_id AND sultangazi_video_gallery_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'city_guide_categories') {
			$query->select($table . '.*,
						  city_guide_categories_lang.city_guide_category_name');
			$query->join('city_guide_categories_lang', 'city_guide_categories_lang.city_guide_category_id = ' . $table . '.city_guide_category_id AND city_guide_categories_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'city_guide_contents') {
			$query->select($table . '.*,
						  city_guide_contents_lang.city_guide_content_name,
						  city_guide_categories_lang.city_guide_category_name');
			$query->join('city_guide_contents_lang', 'city_guide_contents_lang.city_guide_content_id = ' . $table . '.city_guide_content_id AND city_guide_contents_lang.lang_id = ' . $this->defaultLangId, 'left');
			$query->join('city_guide_categories_lang', 'city_guide_categories_lang.city_guide_category_id = ' . $table . '.city_guide_content_category_id AND city_guide_categories_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'projects_category') {
			$query->select($table . '.*,
						  projects_category_lang.project_category_name,
						  (SELECT COUNT(*) FROM projects where project_category_id = ' . $table . '.project_category_id) AS project_total');
			$query->join('projects_category_lang', 'projects_category_lang.project_category_id = ' . $table . '.project_category_id AND projects_category_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'projects_status') {
			$query->select($table . '.*,
						  projects_status_lang.project_status_name,
						  (SELECT COUNT(*) FROM projects where project_status_id = ' . $table . '.project_status_id) AS project_total');
			$query->join('projects_status_lang', 'projects_status_lang.project_status_id = ' . $table . '.project_status_id AND projects_status_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'projects') {
			$query->select($table . '.*,
						  projects_lang.project_name,
							projects_image.project_image');
			$query->join('projects_lang', 'projects_lang.project_id = ' . $table . '.project_id AND projects_lang.lang_id = ' . $this->defaultLangId, 'left');
			$query->join('projects_image', 'projects_image.project_id = ' . $table . '.project_id AND projects_image.project_image_default = 1', 'left');
		}

		/*****************************************************/

		if ($table == 'news') {
			$query->select($table . '.*,
						  news_lang.news_name');
			$query->join('news_lang', 'news_lang.news_id = ' . $table . '.news_id AND news_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'news_paragraphs') {
			$query->select($table . '.*,
						  news_paragraphs_lang.news_paragraph_name,
							news_paragraphs_lang.news_paragraph_description');
			$query->join('news_paragraphs_lang', 'news_paragraphs_lang.news_paragraph_id = ' . $table . '.news_paragraph_id AND news_paragraphs_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'announcements') {
			$query->select($table . '.*,
						  announcements_lang.announcement_name');
			$query->join('announcements_lang', 'announcements_lang.announcement_id = ' . $table . '.announcement_id AND announcements_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'events') {
			$query->select($table . '.*,
						  events_lang.event_name');
			$query->join('events_lang', 'events_lang.event_id = ' . $table . '.event_id AND events_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'events_paragraphs') {
			$query->select($table . '.*,
						  events_paragraphs_lang.event_paragraph_name,
							events_paragraphs_lang.event_paragraph_description');
			$query->join('events_paragraphs_lang', 'events_paragraphs_lang.event_paragraph_id = ' . $table . '.event_paragraph_id AND events_paragraphs_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'events_category') {
			$query->select($table . '.*,
						  events_category_lang.event_category_name,
						  (SELECT COUNT(events.event_category_id) FROM events where event_category_id = ' . $table . '.event_category_id) AS event_total');
			$query->join('events_category_lang', 'events_category_lang.event_category_id = ' . $table . '.event_category_id AND events_category_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'map_categories') {
			$query->select($table . '.*,
						  map_categories_lang.map_category_name');
			$query->join('map_categories_lang', 'map_categories_lang.map_category_id = ' . $table . '.map_category_id AND map_categories_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'map_locations') {
			$query->select($table . '.*,
						  map_locations_lang.map_location_name,
							map_categories_lang.map_category_name');
			$query->join('map_locations_lang', 'map_locations_lang.map_location_id = ' . $table . '.map_location_id AND map_locations_lang.lang_id = ' . $this->defaultLangId, 'left');
			$query->join('map_categories_lang', 'map_categories_lang.map_category_id = ' . $table . '.map_category_id AND map_categories_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'gallery_categories') {
			$query->select($table . '.*,
						  gallery_categories_lang.gallery_category_name');
			$query->join('gallery_categories_lang', 'gallery_categories_lang.gallery_category_id = ' . $table . '.gallery_category_id AND gallery_categories_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'gallery') {
			$query->select($table . '.*,
							gallery_lang.gallery_name,
						  gallery_categories_lang.gallery_category_name,
						  (SELECT COUNT(*) FROM gallery_pictures where gallery_id = ' . $table . '.gallery_id) AS pictures_total');
			$query->join('gallery_lang', 'gallery_lang.gallery_id = ' . $table . '.gallery_id AND gallery_lang.lang_id = ' . $this->defaultLangId, 'left');
			$query->join('gallery_categories_lang', 'gallery_categories_lang.gallery_category_id = ' . $table . '.gallery_category_id AND gallery_categories_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'video_gallery') {
			$query->select($table . '.*,
						  video_gallery_lang.video_gallery_name');
			$query->join('video_gallery_lang', 'video_gallery_lang.video_gallery_id = ' . $table . '.video_gallery_id AND video_gallery_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'menus') {
			$query->select($table . '.*,
						  menus_lang.menu_name,
						  menus_lang.menu_link');
			$query->join('menus_lang', 'menus_lang.menu_id = ' . $table . '.menu_id AND menus_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		if ($table == 'fast_menus') {
			$query->select($table . '.*,
						  fast_menus_lang.menu_name,
						  fast_menus_lang.menu_link');
			$query->join('fast_menus_lang', 'fast_menus_lang.menu_id = ' . $table . '.menu_id AND fast_menus_lang.lang_id = ' . $this->defaultLangId, 'left');
		}

		/*****************************************************/

		// Where
		if (isNotNull($where)) {
			$query->where($where);
		}

		// Search
		if (isNotNull($search) && is_array($search)) {
			foreach ($search as $item) {

				// Countries
				if ($table == 'countries') {
					if ($item == 'country_code' && isNotNull($_GET['country_code'])) {
						$query->where($item, $_GET['country_code']);
					}

					if ($item == 'country_name' && isNotNull($_GET['country_name'])) {
						$query->like($item, $_GET['country_name']);
					}
				}

				/*****************************************************/

				// Cities
				if ($table == 'cities') {
					if ($item == 'country_id' && isNotNull($_GET['country_id'])) {
						$query->where($table . '.' . $item, $_GET['country_id']);
					}

					if ($item == 'city_name' && isNotNull($_GET['city_name'])) {
						$query->like($table . '.' . $item, $_GET['city_name']);
					}
				}

				/*****************************************************/

				// Districts
				if ($table == 'districts') {
					if ($item == 'country_id' && isNotNull($_GET['country_id'])) {
						$query->where($table . '.' . $item, $_GET['country_id']);
					}

					if ($item == 'city_id' && isNotNull($_GET['city_id'])) {
						$query->where($table . '.' . $item, $_GET['city_id']);
					}

					if ($item == 'district_name' && isNotNull($_GET['district_name'])) {
						$query->like($table . '.' . $item, $_GET['district_name']);
					}
				}

				/*****************************************************/

				// Neighbourhoods
				if ($table == 'neighbourhoods') {
					if ($item == 'neighbourhood_code' && isNotNull($_GET['neighbourhood_code'])) {
						$query->where($item, $_GET['neighbourhood_code']);
					}

					if ($item == 'neighbourhood_name' && isNotNull($_GET['neighbourhood_name'])) {
						$query->like($item, $_GET['neighbourhood_name']);
					}
				}

				/*****************************************************/

				// Organization Chart
				if ($table == 'organization_chart') {
					if ($item == 'status' && isNotNull($_GET['status'])) {
						$query->where($item, $_GET['status']);
					}

					if ($item == 'organization_chart_name' && isNotNull($_GET['organization_chart_name'])) {
						$query->like($item, $_GET['organization_chart_name']);
					}
				}

				/*****************************************************/

				// Menu Management
				if ($table == 'menus') {
					if ($item == 'status' && isNotNull($_GET['status'])) {
						$query->where($item, $_GET['status']);
					}

					if ($item == 'menu_name' && isNotNull($_GET['menu_name'])) {
						$query->like($item, $_GET['menu_name']);
					}

					if ($item == 'menu_location' && isNotNull($_GET['menu_location'])) {
						$query->where($item, $_GET['menu_location']);
					}
				}

				/*****************************************************/

				// Fast Menu Management
				if ($table == 'fast_menus') {
					if ($item == 'status' && isNotNull($_GET['status'])) {
						$query->where($item, $_GET['status']);
					}

					if ($item == 'menu_name' && isNotNull($_GET['menu_name'])) {
						$query->like($item, $_GET['menu_name']);
					}
				}

				/*****************************************************/

				// Contact Requests
				if ($table == 'contact_requests') {
					if ($item == 'contact_form_name_surname' && isNotNull($_GET['contact_form_name_surname'])) {
						$query->GroupStart();
						$query->like('contact_form_name', $_GET['contact_form_name_surname']);
						$query->orLike('contact_form_surname', $_GET['contact_form_name_surname']);
						$query->GroupEnd();
					}

					if ($item == 'contact_form_telephone' && isNotNull($_GET['contact_form_telephone'])) {
						$query->where($item, $_GET['contact_form_telephone']);
					}

					if ($item == 'contact_form_email' && isNotNull($_GET['contact_form_email'])) {
						$query->where($item, $_GET['contact_form_email']);
					}

					if ($item == 'contact_form_created_date' && isNotNull($_GET['contact_form_created_date'])) {
						$date_explode = explode(' - ', $_GET['contact_form_created_date']);
						$start_date = dateFormat($date_explode[0], 'Y-m-d');
						$end_date = dateFormat($date_explode[1], 'Y-m-d');

						$query->where('LEFT(' . $table . '.contact_form_created_date, 10) >=', $start_date);
						$query->where('LEFT(' . $table . '.contact_form_created_date, 10) <=', $end_date);
					}
				}

				/*****************************************************/

				// President Contact Requests
				if ($table == 'president_contact_requests') {
					if ($item == 'president_contact_request_name_surname' && isNotNull($_GET['president_contact_request_name_surname'])) {
						$query->GroupStart();
						$query->like('president_contact_request_name', $_GET['president_contact_request_name_surname']);
						$query->orLike('president_contact_request_surname', $_GET['president_contact_request_name_surname']);
						$query->GroupEnd();
					}

					if ($item == 'president_contact_request_telephone' && isNotNull($_GET['president_contact_request_telephone'])) {
						$query->where($item, $_GET['president_contact_request_telephone']);
					}

					if ($item == 'president_contact_request_email' && isNotNull($_GET['president_contact_request_email'])) {
						$query->where($item, $_GET['president_contact_request_email']);
					}

					if ($item == 'president_contact_request_created_date' && isNotNull($_GET['president_contact_request_created_date'])) {
						$date_explode = explode(' - ', $_GET['president_contact_request_created_date']);
						$start_date = dateFormat($date_explode[0], 'Y-m-d');
						$end_date = dateFormat($date_explode[1], 'Y-m-d');

						$query->where('LEFT(' . $table . '.president_contact_request_created_date, 10) >=', $start_date);
						$query->where('LEFT(' . $table . '.president_contact_request_created_date, 10) <=', $end_date);
					}
				}
			}
		}

		if (isset($_GET['order'])) {
			$query->orderBy($column[$_GET['order']['0']['column']], $_GET['order']['0']['dir']);
		} elseif (isset($orderBy)) {
			$query->orderBy(key($orderBy), $orderBy[key($orderBy)]);
		}

		if (isset($_GET['length'])) {
			$query->limit($_GET['length'], $_GET['start']);
		}

		if ($result == 'getResult') {
			return $query->get()->getResult();
		} elseif ($result == 'getNumRows') {
			return $query->get()->getNumRows();
		} elseif ($result == 'countAllResults') {
			return $query->countAllResults();
		}
	}
}
