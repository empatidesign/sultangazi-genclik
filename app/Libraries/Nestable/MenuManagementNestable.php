<?php
namespace App\Libraries\Nestable;

class MenuManagementNestable {

	private $json_string;
	private $return_object = [];
	private $iteration = 1;
	private $normalid = [];
	private $ci_arr_parent = [];
	private $ci_arr_data = [];

	public function set_json($json_string = '') {
		$this->json_string = $json_string;
	}

	public function get_object() {
		$object = json_decode($this->json_string);
		$object = $this->recursive_process($object);

		return $this->return_object;
	}

	private function recursive_process($objects, $parent = NULL) {
		if (is_array($objects)) {
			foreach ($objects as $object) {
				$arr_object = (array) $object;
				$arr_keys = array_keys($arr_object);

				$return = [];

				foreach ($arr_keys as $key) {
					if ($key != 'children')
						$return[$key] = $object->{$key};
				}

				$return['menu_id'] = $this->iteration;
				$this->normalid[$object->menu_id] = $this->iteration;

				$this->iteration++;

				$return['parent'] = @$this->normalid[$parent];
				$this->return_object[] = $return;

				if(isset($object->children))
					$this->recursive_process($object->children, $object->menu_id);
			}
		}
	}

	public function get_nestable($ci_result) {
		if (isNotNull($ci_result)) {
			foreach ($ci_result as $data) {
				$parent = (empty($data->menu_parent_id)) ? 0 : $data->menu_parent_id;
				$this->ci_arr_parent[$parent][] = $data->menu_id;
				$this->ci_arr_data[$data->menu_id]['menu_id'] = $data->menu_id;
				$this->ci_arr_data[$data->menu_id]['menu_name'] = $data->menu_name;
				$this->ci_arr_data[$data->menu_id]['edit'] = '<a href="'.BACKEND_URL.'/'.ADMIN_URL_DESIGNS.'/'.ADMIN_URL_MENU_MANAGEMENT.'/edit/'.$data->menu_id.'" class="float-right mr-1">'.set_primary(lang('Admin.process.edit')).'</a>';
				$this->ci_arr_data[$data->menu_id]['delete'] = '<a href="javascript:void(0)" class="float-right customDelete" data-url="'.BACKEND_URL.'/'.ADMIN_URL_DESIGNS.'/'.ADMIN_URL_MENU_MANAGEMENT.'/delete/'.$data->menu_id.'">'.set_danger(lang('Admin.process.delete')).'</a>';
			}
		}

		return $this->recursive_nestable();
	}

	private function recursive_nestable($parent = 0) {
		$exists = isset($this->ci_arr_parent[$parent]);
		$res = $exists ? "<ol class=\"dd-list\">\n":'';

		foreach ($this->ci_arr_parent[$parent] as $li) {
			$data = $this->ci_arr_data[$li];

			$res .= "<li class=\"dd-item dd3-item\" data-menu_id=\"".$data['menu_id']."\">\n";
				$res .= "\t<div class=\"dd-handle dd3-handle\"><i class=\"fas fa-align-justify\"></i></div>\n";
				$res .= "\t<div class=\"dd3-content\">".$data['menu_name']." ".$data['delete']." ".$data['edit']."</div>\n";
				$res .= @$this->recursive_nestable($li);
			$res .= "</li>\n";
		}
		$res .= $exists ? "</ol>\n":'';

		return $res;
	}

	public function get_generated_menu($ci_result) {
		foreach ($ci_result as $data) {
			$parent = (empty($data->parent)) ? 0 : $data->parent;
			$this->ci_arr_parent[$parent][] = $data->menu_id;
			$this->ci_arr_data[$data->menu_id]['menu_id'] = $data->menu_id;
			$this->ci_arr_data[$data->menu_id]['menu_name'] = $data->menu_name;
		}

		return json_decode(json_encode($this->recursive_generated_menu()));
	}

	private function recursive_generated_menu($parent = 0) {
		$return = [];
		foreach ($this->ci_arr_parent[$parent] as $li) {

			$exists = isset($this->ci_arr_parent[$li]);

			$return[$li] = $this->ci_arr_data[$li];
			$return[$li]['child_exists'] = $exists;
			if ($exists)
				$return[$li]['child'] = @$this->recursive_generated_menu($li);
		}

		return $return;
	}
}
