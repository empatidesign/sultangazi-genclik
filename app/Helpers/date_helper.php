<?php
/**
 * @param $date
 * @param $difference
 * @param string $interval
 * @param string $format
 * @return false|string
 * Verilen tarihe ekleme/çıkarma yapar ve son tarihi döndürür.
 */
function dateAdd($date, $difference, string $interval = 'days', string $format = 'Y-m-d') {
	$date = date_create($date);
	date_add($date, date_interval_create_from_date_string($difference." ".$interval));
	return date_format($date, $format);
}

/**
 * @param string $date
 * @param string $format
 * @return false|string
 * Metin olarak gönderilen veriyi tarih olarak döndürür.
 */
function dateCreate(string $date, string $format = 'Y-m-d') {
	$time = strtotime($date);
	return date($format, $time);
}

/**
 * @param string $format
 * @return false|string
 * Şimdiki zamanı döndürür.
 */
function nowDate(string $format = 'Y-m-d H:i:s') {
	return date($format);
}

/**
 * @param $start
 * @param $end
 * @param string $format
 * @return array
 * @throws Exception
 * Verilen iki tarih arasını döndürür.
 */
function dateRange($start, $end, string $format = 'Y-m-d'): array {
	$result = [];
	$interval = new DateInterval('P1D');

	$realEnd = new DateTime($end);
	$realEnd->add($interval);

	$period = new DatePeriod(new DateTime($start), $interval, $realEnd);
	foreach($period as $date){
		$result[] = $date->format($format);
	}

	return $result;
}

/**
 * @param $date
 * @param string $format
 * @return string
 * @throws Exception
 */
function dateFormat($date, string $format = 'Y-m-d H:i:s'): string {
	$date = str_replace(['/'], ['-'], $date);
	$date = new DateTime($date);
	return $date->format($format);
}

/**
 * @param $start_date
 * @param $end_date
 * @return string
 * İki tarih arasındaki farkı döndürür.
 */
function dateDiff($start_date, $end_date): string {
	$datetime1 = new DateTime($start_date);
	$datetime2 = new DateTime($end_date);
	$interval = $datetime1->diff($datetime2);
	return $interval->format('%a');
}

/**
 * @param $timestamp
 * @return string
 * Timestamp değerini tarihe dönüştürür.
 */
function timestampToDate($timestamp, $format = 'Y-m-d H:i:s'): string {
	$date = new \DateTime();
	$date->setTimestamp($timestamp);
	return $date->format($format);
}

/**
 * @param $start_date
 * @param $end_date
 * @return string
 * İki tarih arasındaki saat farkını döndürür.
 */
function hourDiff($start_date, $end_date): string {
	$datetime1 = date_create($start_date);
	$datetime2 = date_create($end_date);
	$interval = date_diff($datetime1, $datetime2);
	return $interval->format('%h');
}

/**
 * @param $start_date
 * @param $end_date
 * @return string
 * İki tarih arasındaki dakika farkını döndürür.
 */
function minuteDiff($start_date, $end_date): string {
	$datetime1 = date_create($start_date);
	$datetime2 = date_create($end_date);
	$interval = date_diff($datetime1, $datetime2);
	return $interval->format('%i');
}

/**
 * @param string $day
 * @return string
 * Gönderilen parametreye göre gün adını döndürür.
 */
function dayName($day): string {
	$names = [
		'Monday' => lang('Web.days.monday'),
		'Tuesday' => lang('Web.days.tuesday'),
		'Wednesday' => lang('Web.days.wednesday'),
		'Thursday' => lang('Web.days.thursday'),
		'Friday' => lang('Web.days.friday'),
		'Saturday' => lang('Web.days.saturday'),
		'Sunday' => lang('Web.days.sunday')
	];

	return strtr($day, $names);
}

/**
 * @param $date
 * @return int
 * @throws Exception
 * Haftanın gün indeksini döndürür.
 * 1: Pazartes,...,7: Pazar
 */
function dayIndex($date): int {
	$index = dateFormat($date, 'w');
	return $index == 0 ? 7 : (int)$index;
}

/**
 * @param string $date
 * @return string
 * Saat'ten saniye siler.
 */
function deleteSeconds($date): string {
	$date = substr($date, 0, -3);
	return $date;
}

/**
 * @param string $month
 * @return string
 * Gönderilen parametreye göre ay adını döndürür.
 */
function monthName($month): string {
  if ($month == 1) {
    return lang('Admin.moment.monthsLong.january');
  } else if ($month == 2) {
    return lang('Admin.moment.monthsLong.february');
  } else if ($month == 3) {
    return lang('Admin.moment.monthsLong.march');
  } else if ($month == 4) {
    return lang('Admin.moment.monthsLong.april');
  } else if ($month == 5) {
    return lang('Admin.moment.monthsLong.may');
  } else if ($month == 6) {
    return lang('Admin.moment.monthsLong.june');
  } else if ($month == 7) {
    return lang('Admin.moment.monthsLong.july');
  } else if ($month == 8) {
    return lang('Admin.moment.monthsLong.august');
  } else if ($month == 9) {
    return lang('Admin.moment.monthsLong.september');
  } else if ($month == 10) {
    return lang('Admin.moment.monthsLong.october');
  } else if ($month == 11) {
    return lang('Admin.moment.monthsLong.november');
  } else if ($month == 12) {
    return lang('Admin.moment.monthsLong.december');
  } else {
    return '';
  }
}

/**
 * @return array
 * Saat listesi verir.
 */
function hoursList() {
	$a = '07:00';
	$b = '23:59';

	$period = new DatePeriod(
		new DateTime($a),
		new DateInterval('PT15M'),
		new DateTime($b)
	);

	$hours = [];
	foreach ($period as $date) {
		$hours[] = $date->format('H:i');
	}

	return $hours;
}
