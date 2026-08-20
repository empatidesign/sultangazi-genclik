<?php
namespace App\Controllers\Frontend\Api\Mobile;

use App\Controllers\Frontend\BaseController;
use App\Models\Frontend\Events\NexoraEventsModel;

/**
 * Mobil API - Etkinlikler
 *
 * Genclik sitesinde etkinlikler eski yerel `events` tablosunda degil,
 * Nexora servisinden cron ile senkron edilen `nexora_events` tablosunda
 * tutulur (bkz. _tools/sync_events.php). Bu uc de ayni kaynagi kullanir,
 * boylece mobil uygulama ile site ayni icerigi gosterir.
 *
 * Gecmis etkinlikler dondurulmez.
 */
class Events extends BaseController
{
    protected $NexoraEventsModel;

    public function __construct()
    {
        $this->NexoraEventsModel = new NexoraEventsModel();
    }

    public function index()
    {
        $array = [];

        foreach ($this->NexoraEventsModel->upcoming(100) as $row) {
            $array[] = [
                'id'          => (int) $row->event_id,
                'name'        => $row->name,
                'slug'        => $row->slug,
                'category'    => $row->category_name,
                'date' => [
                    'start' => $row->start_date,
                    'end'   => $row->end_date,
                ],
                'hour' => [
                    'start' => isNotNull($row->start_time) ? substr($row->start_time, 0, 5) : NULL,
                    'end'   => isNotNull($row->end_time) ? substr($row->end_time, 0, 5) : NULL,
                ],
                'age_group' => [
                    'min' => $row->min_age,
                    'max' => $row->max_age,
                ],
                'gender'        => $row->gender,
                'quota'         => $row->capacity,
                'available'     => $row->available_capacity,
                'registration_open' => (bool) $row->registration_open,
                'is_paid'       => (bool) $row->is_paid,
                'price_info'    => $row->price_info,
                'resident_only' => (bool) $row->resident_only,
                'image'         => $row->image_url,
                'locations' => [
                    'name'            => $row->facility_name ?: ($row->hall_name ?: $row->location_name),
                    'lat_coordinate'  => $row->latitude,
                    'long_coordinate' => $row->longitude,
                ],
                'description' => $row->description,
                'web_url'     => base_url(WEB_URL_EVENTS . '/' . $row->slug . '/' . $row->event_id),
            ];
        }

        return json($array);
    }
}
