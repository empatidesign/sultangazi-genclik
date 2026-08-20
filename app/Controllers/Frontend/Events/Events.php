<?php
namespace App\Controllers\Frontend\Events;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Events\EventsModel;

class Events extends BaseController {

	protected $EventsModel;
	protected $folder;

	public function __construct() {
		$this->EventsModel = new EventsModel();
		$this->folder = 'events';
	}

	/**
	 * Takvim günlerini oluşturur
	 */
	public function calendarDays($date = NULL, $month_code = NULL, $year_code = NULL) {
		$array = [];
		$month = $month_code;
		$year = $year_code;

		$start_date = '01-'.$month.'-'.$year;
		$start_time = strtotime($start_date);
		$end_time = strtotime('+1 month', $start_time);

		for ($i = $start_time; $i < $end_time; $i += 86400) {

			// Active
			if (isNotNull($date)) {
				$active = date('d-m-Y', $i) == dateFormat($date, 'd-m-Y') ? TRUE : FALSE;
			} else {
				$active = date('d-m-Y', $i) == date('d-m-Y') ? TRUE : FALSE;
			}

		   $array[] = [
				 'day' => [
					 'original' => date('d', $i),
					 'number' => date('j', $i),
					 'name' => dayName(date('l', $i)),
					 'active' => $active
				 ],
				 'month' => date('m', $i),
				 'year' => date('Y', $i)
			 ];
		}

		return $array;
	}

	public function index() {

		$pager = service('pager');
		$page_input = $this->request->getVar('page');
		$page = 1; 
		
		if (is_numeric($page_input) && $page_input > 0) {
			$page = (int) $page_input; 
		}
		
		$per_page = $this->designSettings->paging_count;
		$page_start = $page ? ($page * $per_page) - $per_page : 1;
		$total = count($this->allEvents());
		$pagination = $pager->makeLinks($page, $per_page, $total, 'classic');

		$before_month = new \DateTime('1 month ago');
		$before_month = $before_month->format('m-Y');

		$month_input = trim($this->request->getGet('month'));
		if (!empty($month_input) && preg_match('/^(0[1-9]|1[0-2])\-([0-9]{4})$/', $month_input)) {
			$month = $month_input;
			$month_explode = explode('-', $month);
			$calendar_name = monthName($month_explode[0]);
			$month_code = $month_explode[0];
			$year_code = $month_explode[1];
		} else {
			$month = null;
			$calendar_name = monthName(date('m'));
			$month_code = date('m');
			$year_code = date('Y');
		}

		$date_input = trim($this->request->getGet('date'));
		if (!empty($date_input) && preg_match('/^([0-9]{4})\-(0[1-9]|1[0-2])\-(0[1-9]|[1-2][0-9]|3[0-1])$/', $date_input)) {
			$date = $date_input;
		} else {
			if (isNotNull($month) && $month != nowDate('m-Y')) {
				$date = $year_code.'-'.$month_code.'-01';
			} else {
				$date = nowDate('Y-m-d');
			}
		}

		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/'.$this->folder.'/events-index.html', [
			'head' => [
				'title' => lang('WebEvents.title'),
				'keywords' => $this->settings->site_keywords,
				'description' => $this->settings->site_description
			],
			'calendar' => [
				'month' => $calendar_name,
				'days' => $this->calendarDays($date, $month_code, $year_code)
			],
			'months' => [
				'before' => [
					'name' => monthName($before_month),
					'number' => $before_month
				],
				'now' => [
					'name' => monthName(date('m')),
					'number' => date('m-Y')
				]
			],
			'list' => [
				'all_events' => $this->allEvents($date, $page_start, $per_page)
			],
			'pagination' => [
				'list' => $pagination
			],
			'PARAMETER' => [
				'WEB_URL_EVENTS' => WEB_URL_EVENTS
			]
		]);

	}

	public function detail($slug, $event_id) {
		$sql = $this->EventsModel->eventsInfoModel($slug, $event_id, $this->defaultLangId);
		if (isNotNull($sql)) {

			// Date
			$event_date = NULL;
			if ($sql->event_date != '0000-00-00') {
				$date = explode('-', $sql->event_date);
				$event_date = $date[2].' '.monthName($date[1]).' '.$date[0];
			}

			return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/'.$this->folder.'/events-detail.html', [
				'head' => [
					'title' => isNotNull($sql->event_meta_title) ? $sql->service_meta_title : $sql->event_name,
					'keywords' => isNotNull($sql->event_meta_keywords) ? $sql->event_meta_keywords : $this->settings->site_keywords,
					'description' => isNotNull($sql->event_meta_description) ? $sql->event_meta_description : $this->settings->site_description
				],
				'result' => [
					'event_id' => $sql->event_id,
					'event_name' => $sql->event_name,
					'event_date' => $event_date,
					'event_hour' => $sql->event_hour != '00:00:00' ? deleteSeconds($sql->event_hour) : NULL,
					'image' => [
						'normal' => $sql->event_image,
						'base' => base_url(FILE_PATH_EVENTS_BIG.'/'.$sql->event_image)
					],
					'event_location' => $sql->event_location,
					'event_age_group' => $sql->event_age_group,
					'event_quota' => $sql->event_quota,
					'event_location_address' => $sql->event_location_address,
					'event_location_telephone' => $sql->event_location_telephone,
					'event_location_map' => $sql->event_location_map,
					'event_lat_coordinate' => $sql->event_lat_coordinate,
					'event_long_coordinate' => $sql->event_long_coordinate,
					'event_description' => $sql->event_description
				],
				'list' => [
					'other_events' => $this->otherEvents()
				],
				'PARAMETER' => [
					'WEB_URL_EVENTS' => WEB_URL_EVENTS
				]
			]);

		} else {
			return redirect()->to('404');
		}
	}

	public function allEvents($date = NULL, $page_start = NULL, $per_page = NULL) {
		$array = [];
		$sql = $this->EventsModel->eventsListModel($this->defaultLangId, $date, $page_start, $per_page);
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
						'base' => $this->imageControl(FILE_PATH_EVENTS_THUMB, $row->event_image)
					]
				];
			}
		}

		return $array;
	}

	public function otherEvents() {
		$array = [];
		$sql = $this->EventsModel->eventsListModel($this->defaultLangId);
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
						'base' => $this->imageControl(FILE_PATH_EVENTS_THUMB, $row->event_image)
					]
				];
			}
		}

		return $array;
	}
}
