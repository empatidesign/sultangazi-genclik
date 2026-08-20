<?php
namespace App\Libraries;
use App\Controllers\Frontend\BaseController;

use App\Models\Frontend\GeneralModel;

class PopupModule extends BaseController {

	protected $general;
	protected $defaultLangId;

	public function index() {
		$this->general = new GeneralModel();
		$this->defaultLangId = $this->general->languagesDefaultModel()[0];

		/*****************************************************/

		$array = NULL;
		$popup = $this->general->popupModuleModel($this->defaultLangId);
    if (isNotNull($popup)) {
      foreach ($popup as $row) {

				// Date
        $date = TRUE;
        if ($row->popup_module_start_date != '0000-00-00 00:00:00' && $row->popup_module_end_date != '0000-00-00 00:00:00') {
					$date = FALSE;
          if (nowDate() >= $row->popup_module_start_date && nowDate() <= $row->popup_module_end_date) {
            $date = TRUE;
          }
        }

        // Timing
        $timing = FALSE;
        if (isNotNull($row->popup_module_timing)) {
          if ($row->popup_module_timing == POPUP_TIMING_24_HOURS) {
            setcookie('popup_time', TRUE, time() + 86400, '/', $_SERVER['HTTP_HOST']);
            if (!isset($_COOKIE['popup_time'])) {
              $timing = TRUE;
            }
          }
          if ($row->popup_module_timing == POPUP_TIMING_1_HOUR) {
            setcookie('popup_time', TRUE, time() + 3600, '/', $_SERVER['HTTP_HOST']);
            if (!isset($_COOKIE['popup_time'])) {
              $timing = TRUE;
            }
          }
          if ($row->popup_module_timing == POPUP_TIMING_1_WEEK) {
            setcookie('popup_time', TRUE, time() + 604800, '/', $_SERVER['HTTP_HOST']);
            if (!isset($_COOKIE['popup_time'])) {
              $timing = TRUE;
            }
          }
          if ($row->popup_module_timing == POPUP_TIMING_1_MONTH) {
            setcookie('popup_time', TRUE, time() + 60 * 60 * 24 * 30, '/', $_SERVER['HTTP_HOST']);
            if (!isset($_COOKIE['popup_time'])) {
              $timing = TRUE;
            }
          }
          if ($row->popup_module_timing == POPUP_TIMING_AT_EVERY_ENTRY) {
            if (isset($_COOKIE['popup_time'])) {
              setcookie('popup_time', NULL, -1, '/', $_SERVER['HTTP_HOST']);
            }

            $timing = TRUE;
          }
        }

        // Display Field
        $display_field = NULL;
        if (isNotNull($row->popup_module_display_field) && $row->popup_module_display_field != POPUP_DISPLAY_FIELD_DESKTOP.','.POPUP_DISPLAY_FIELD_MOBILE) {
          $display_field_explode = explode(',', $row->popup_module_display_field);
          foreach ($display_field_explode as $display_field_row) {
            if ($display_field_row == POPUP_DISPLAY_FIELD_DESKTOP) { // Desktop
              $display_field = 'popup-desktop';
            } elseif($display_field_row == POPUP_DISPLAY_FIELD_MOBILE) { // Mobile
              $display_field = 'popup-mobile';
            }
          }
        }

        if ($date == TRUE) {
          if ($timing !== FALSE) {
            $array = [
              'display_field' => $display_field,
              'html' => $row->popup_module_type == POPUP_TYPE_HTML ? $row->popup_module_html : NULL,
              'image' => $row->popup_module_type == POPUP_TYPE_IMAGE && isNotNull($row->popup_module_image) ? $this->imageControl(FILE_PATH_POPUP, $row->popup_module_image) : NULL,
              'image_link' => $row->popup_module_image_link
            ];
          }
        }

      }
    }

    return $array;
	}
}
