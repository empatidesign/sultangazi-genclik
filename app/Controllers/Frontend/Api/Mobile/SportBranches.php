<?php
namespace App\Controllers\Frontend\Api\Mobile;

use App\Controllers\Frontend\BaseController;
use App\Libraries\SportAcademyApi;
use App\Models\Frontend\IndexModel;

/**
 * Mobil API - Spor Branslari
 *
 * Genclik sitesine ozgu uctur (ana sitede karsiligi yoktur).
 * Yerel proje kayitlarini dondurur ve her bransa Spor Akademisi'ndeki
 * detay adresini ekler.
 */
class SportBranches extends BaseController
{
    protected $IndexModel;
    protected $sportAcademy;

    public function __construct()
    {
        $this->IndexModel = new IndexModel();
        $this->sportAcademy = new SportAcademyApi();
    }

    public function index()
    {
        $array = [];

        $sql = $this->IndexModel->sportProjectsModel($this->defaultLangId);

        if (isNotNull($sql)) {
            foreach ($sql as $row) {
                $array[] = [
                    'id'          => (int) $row->project_id,
                    'name'        => $row->project_name,
                    'slug'        => $row->project_slug,
                    'image'       => isNotNull($row->project_image)
                        ? webp_url(FILE_PATH_PROJECT . '/thumb/' . $row->project_image)
                        : NULL,
                    'web_url'     => base_url(WEB_URL_PROJECTS . '/' . $row->project_slug . '/' . $row->project_id),
                    'academy_url' => $this->sportAcademy->branchUrl($row->project_slug ?? NULL),
                ];
            }
        }

        return json($array);
    }
}
