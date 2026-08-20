<?php
namespace App\Controllers\Frontend\Events;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Events\EventsModel;
use App\Models\Frontend\Events\NexoraEventsModel;

class Events extends BaseController {

	protected $EventsModel;
	protected $NexoraEventsModel;
	protected $folder;

	public function __construct() {
		$this->EventsModel = new EventsModel();
		$this->NexoraEventsModel = new NexoraEventsModel();
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

	/**
	 * Etkinlik listesi.
	 *
	 * Icerik Nexora servisinden cron ile yerel nexora_events tablosuna
	 * aktarilir (bkz. _tools/sync_events.php); burada yalnizca yerel tablo
	 * okunur. Gecmis etkinlikler listelenmez.
	 */
	public function index() {
		$pager = service('pager');
		$page_input = $this->request->getVar('page');
		$page = is_numeric($page_input) && $page_input > 0 ? (int) $page_input : 1;

		$per_page = 12;
		$offset = ($page - 1) * $per_page;
		$total = $this->NexoraEventsModel->upcomingCount();
		$pagination = $pager->makeLinks($page, $per_page, $total, 'classic');

		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/'.$this->folder.'/events.html', [
			'page_name' => 'events',
			'head' => [
				'title' => lang('WebEvents.title'),
				'keywords' => $this->settings->site_keywords,
				'description' => lang('WebEvents.description')
			],
			'list' => [
				'all' => $this->formatEvents($this->NexoraEventsModel->upcoming($per_page, $offset))
			],
			'total' => $total,
			'folder' => $this->folder,
			'pagination' => [
				'links' => $pagination
			],
			'PARAMETER' => [
				'WEB_URL_EVENTS' => WEB_URL_EVENTS
			]
		]);
	}

	/**
	 * Etkinlik detayi. Gecmis veya bulunamayan etkinlikte 404.
	 */
	public function detail($slug = NULL, $event_id = NULL) {
		$row = $this->NexoraEventsModel->findBySlug((string) $slug, (int) $event_id);

		if ($row === NULL) {
			return redirect()->to('404');
		}

		$result = $this->formatEvent($row);

		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/'.$this->folder.'/events-detail.html', [
			'page_name' => 'events-detail',
			'head' => [
				'title' => $row->name,
				'keywords' => $this->settings->site_keywords,
				'description' => isNotNull($row->description)
					? mb_substr(trim(strip_tags($row->description)), 0, 160)
					: $this->settings->site_description
			],
			'result' => $result,
			'list' => [
				'related' => $this->formatEvents($this->NexoraEventsModel->related($row->category_name, (int) $row->event_id))
			],
			'folder' => $this->folder,
			'PARAMETER' => [
				'WEB_URL_EVENTS' => WEB_URL_EVENTS
			]
		]);
	}

	/**
	 * Kayit listesini gorunum icin bicimlendirir.
	 */
	private function formatEvents(array $rows): array {
		$list = [];
		foreach ($rows as $row) {
			$list[] = $this->formatEvent($row);
		}

		return $list;
	}

	/**
	 * Tek kaydi gorunum icin bicimlendirir.
	 */
	private function formatEvent(object $row): array {
		$baslangic = strtotime($row->start_date);

		return [
			'id' => $row->event_id,
			'slug' => $row->slug,
			'url' => base_url(WEB_URL_EVENTS.'/'.$row->slug.'/'.$row->event_id),
			'name' => $row->name,
			'category' => $row->category_name,
			'place' => $row->facility_name ?: ($row->hall_name ?: $row->location_name),
			'latitude' => $row->latitude,
			'longitude' => $row->longitude,
			'date' => [
				'day' => date('j', $baslangic),
				'month' => monthName((int) date('n', $baslangic)),
				'full' => $this->longDate($row->start_date),
				'range' => $this->dateRange($row->start_date, $row->end_date)
			],
			'time' => [
				'start' => isNotNull($row->start_time) ? substr($row->start_time, 0, 5) : NULL,
				'end' => isNotNull($row->end_time) ? substr($row->end_time, 0, 5) : NULL
			],
			'age' => [
				'min' => $row->min_age,
				'max' => $row->max_age
			],
			'gender' => $row->gender,
			'capacity' => $row->capacity,
			'available' => $row->available_capacity,
			'open' => (bool) $row->registration_open,
			'is_paid' => (bool) $row->is_paid,
			'price_info' => $row->price_info,
			'resident_only' => (bool) $row->resident_only,
			'description' => $row->description,
			'image' => $row->image_url,
			'apply_url' => $this->applyUrl($row->remote_id)
		];
	}

	/**
	 * Basvuru adresi: vatandas portalindaki etkinlik derin baglantisi.
	 */
	private function applyUrl(?string $remote_id): string {
		$portal = rtrim(env('nexora.portalUrl', NEXORA_PORTAL_URL), '/').'/etkinlikler';

		return isNotNull($remote_id) ? $portal.'?eventId='.rawurlencode($remote_id) : $portal;
	}

	/**
	 * "20 Agustos 2026" bicimi.
	 */
	private function longDate(?string $date): ?string {
		if (!isNotNull($date)) {
			return NULL;
		}

		$t = strtotime($date);

		return date('j', $t).' '.monthName((int) date('n', $t)).' '.date('Y', $t);
	}

	/**
	 * Tek gunluk degilse "20 - 25 Agustos 2026" bicimi.
	 */
	private function dateRange(?string $start, ?string $end): ?string {
		$bas = $this->longDate($start);

		if (!isNotNull($end) || $end === $start) {
			return $bas;
		}

		return $bas.' - '.$this->longDate($end);
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
