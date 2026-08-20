<?php
function set_image(string $value, int $width = NULL, int $height = NULL, bool $app = false): string {
	$explode = explode('/', $value);
	helper('sultangazi');

	if (isNotNull(end($explode))) {

		$imageProperties = [
			'src' => $app ? sultangazi_url($value) : base_url($value),
			'width' => $width,
			'height' => $height,
			'alt' => ''
		];
		return img($imageProperties);

	}else{
		return FALSE;
	}
}

function set_status(string $value = NULL, array $other = NULL): string {
	if (isNotNull($other)) {
		return $other[0] == FORM_ACTIVE_NUMBER ? '<div class="badge badge-soft-success">'.$other[1][0].'</div>' : '<div class="badge badge-soft-danger">'.$other[1][1].'</div>';
	} else {
		return $value == FORM_ACTIVE_NUMBER ? '<div class="badge badge-soft-success">'.lang('Admin.active').'</div>' : '<div class="badge badge-soft-danger">'.lang('Admin.passive').'</div>';
	}
}

function set_status_icon(string $value): string {
	return $value == FORM_ACTIVE_NUMBER ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>';
}

function set_process(string $value): string {
	return $value === ''.FORM_ACTIVE_NUMBER.'' ? '<div class="badge badge-soft-success">'.lang('Admin.yes').'</div>' : '<div class="badge badge-soft-danger">'.lang('Admin.no').'</div>';
}

function action_links(string $value, array $button, string $pageUrl, string $parameter = NULL, string $slug = NULL, array $modal = NULL): string {
	$result = NULL;

	if (isNotNull($button)) {
		foreach ($button as $row) {

			if ($row == 'change-password') {
				$result .= '<a href="'.base_url(BACKEND_URL.'/'.$pageUrl.'/change-password/'.$value).'" class="btn btn-success text-white btn-icon" data-placement="top" data-toggle="tooltip" title="" data-original-title="'.lang('Admin.password.change').'"><i class="fas fa-lock-open"></i></a>';
			}

			if ($row == 'edit') {
				$result .= '<a href="'.base_url(BACKEND_URL.'/'.$pageUrl.'/edit/'.$value).'" class="btn btn-primary text-white btn-icon" data-placement="top" data-toggle="tooltip" title="" data-original-title="'.lang('Admin.process.edit').'"><i class="far fa-edit"></i></a>';
			}

			if (isNotNull($modal) && $modal[0] == 'modal') {
				if ($row == 'modal-edit') {
					$result .= "<a href='javascript:void(0);' onclick=\"modalPage('".$modal[1]."', '".$value."')\" class='btn btn-primary text-white btn-icon' data-placement='top' data-toggle='tooltip' title='' data-original-title='".lang('Admin.process.edit')."'><i class='far fa-edit'></i></a>";
				}

				if ($row == 'modal-copy') {
					$result .= "<a href='javascript:void(0);' onclick=\"modalPage('".$modal[1]."', '".$value."')\" class='btn btn-success text-white btn-icon' data-placement='top' data-toggle='tooltip' title='' data-original-title='".lang('Admin.process.copy')."'><i class='far fa-copy'></i></a>";
				}
			}

			if ($row == 'delete') {
				$result .= '<a href="javascript:void(0);" class="btn btn-danger text-white btn-icon datatableDelete" data-url="'.base_url(BACKEND_URL.'/'.$pageUrl.'/delete/'.$value).'" data-placement="top" data-toggle="tooltip" title="" data-original-title="'.lang('Admin.process.delete').'"><i class="far fa-trash-alt"></i></a>';
			}

			if ($row == 'custom-delete') {
				$result .= '<a href="javascript:void(0);" class="btn btn-danger text-white btn-icon customDelete" data-url="'.base_url(BACKEND_URL.'/'.$pageUrl.'/delete/'.$value).'" data-placement="top" data-toggle="tooltip" title="" data-original-title="'.lang('Admin.process.delete').'"><i class="far fa-trash-alt"></i></a>';
			}

			if ($row == 'confirmation') {
				if ($parameter == DEFAULT_RECORD_PASSIVE) {
					$result .= '<a href="javascript:void(0);" class="btn btn-success text-white btn-icon confirmation" data-url="'.base_url(BACKEND_URL.'/'.$pageUrl.'/confirmation/'.$value).'" data-placement="top" data-toggle="tooltip" title="" data-original-title="'.lang('Admin.default.make').'"><i class="fas fa-check"></i></a>';
				}

				if ($parameter == DEFAULT_RECORD_ACTIVE) {
					//$result .= '<a href="javascript:void(0);" class="btn btn-success text-white btn-icon" data-placement="top" data-toggle="tooltip" title="" data-original-title="'.lang('Admin.default.title').'"><i class="fas fa-check"></i></a>';
				}
			}

			if ($row == 'detail') {
				$result .= '<a href="'.base_url(BACKEND_URL.'/'.$pageUrl.'/detail/'.$value).'" class="btn btn-info text-white btn-icon" data-placement="top" data-toggle="tooltip" title="" data-original-title="'.lang('Admin.process.detail').'"><i class="fas fa-info-circle"></i></a>';
			}

		}
	}

	return $result;
}

function set_primary(string $text): string {
	return '<div class="badge badge-soft-primary">'.$text.'</div>';
}

function set_danger(string $text): string {
	return '<div class="badge badge-soft-danger">'.$text.'</div>';
}

function set_success(string $text): string {
	return '<div class="badge badge-soft-success">'.$text.'</div>';
}

function set_purple(string $text): string {
	return '<div class="badge badge-soft-purple">'.$text.'</div>';
}

function set_button(string $value, int $id, string $lang, int $total = NULL, $popup = FALSE): string {
	// Total
	if (isNotNull($total) && $total != 0) {
		$total = ' ('.$total.')';
	} elseif ($total == 0) {
		$total = ' (0)';
	}

	return '<a href="'.base_url($value.'/'.$id).'" class="btn btn-sm btn-purple"><i class="ti-angle-double-right"></i> '.$lang.$total.'</a>';
}

function set_checkbox_button(int $id): string {
	return '<div class="checkbox checkbox-primary"><input type="checkbox" name="choose[]" id="'.$id.'" class="checkbox-control" value="'.$id.'"><label for="'.$id.'"></label></div>';
}
