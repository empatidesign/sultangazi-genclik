<?php
namespace App\Libraries\Nestable;

class OrganizationChartNestable {

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

				$return['organization_chart_id'] = $this->iteration;
				$this->normalid[$object->organization_chart_id] = $this->iteration;

				$this->iteration++;

				$return['parent'] = @$this->normalid[$parent];
				$this->return_object[] = $return;

				if(isset($object->children))
					$this->recursive_process($object->children, $object->organization_chart_id);
			}
		}
	}

	public function get_nestable($ci_result) {
		if (isNotNull($ci_result)) {
			foreach ($ci_result as $data) {
				$parent = (empty($data->organization_chart_parent_id)) ? 0 : $data->organization_chart_parent_id;
				$this->ci_arr_parent[$parent][] = $data->organization_chart_id;
				$this->ci_arr_data[$data->organization_chart_id]['organization_chart_id'] = $data->organization_chart_id;
				$this->ci_arr_data[$data->organization_chart_id]['organization_chart_name'] = isNotNull($data->organization_chart_name) ? $data->organization_chart_name.'<br />'.$data->organization_chart_sub_title : set_danger(lang('AdminContents.organizationChart.general.empty'));
				$this->ci_arr_data[$data->organization_chart_id]['edit'] = '<a href="'.BACKEND_URL.'/'.ADMIN_URL_CONTENTS.'/'.ADMIN_URL_ORGANIZATION_CHART.'/edit/'.$data->organization_chart_id.'" class="mr-1">'.set_primary(lang('Admin.process.edit')).'</a>';
				$this->ci_arr_data[$data->organization_chart_id]['delete'] = '<a href="javascript:void(0)" class="customDelete" data-url="'.BACKEND_URL.'/'.ADMIN_URL_CONTENTS.'/'.ADMIN_URL_ORGANIZATION_CHART.'/delete/'.$data->organization_chart_id.'">'.set_danger(lang('Admin.process.delete')).'</a>';
			}
		}

		return $this->recursive_nestable();
	}

	private function recursive_nestable($parent = 0) {
		$exists = isset($this->ci_arr_parent[$parent]);
		$res = $exists ? "<ol class=\"dd-list\">\n":'';

		foreach ($this->ci_arr_parent[$parent] as $li) {
			$data = $this->ci_arr_data[$li];

			$res .= "<li class=\"dd-item dd3-item\" data-organization_chart_id=\"".$data['organization_chart_id']."\">\n";
				$res .= "\t<div class=\"dd-handle dd3-handle\" style=\"height: 54px;\"><i class=\"fas fa-align-justify\"></i></div>\n";
				$res .= "\t<div class=\"dd3-content\">\n";
					$res .= "\t<div class=\"row\">\n";
						$res .= "\t<div class=\"col-md-9\">".$data['organization_chart_name']."</div>\n";
						$res .= "\t<div class=\"col-md-3 text-right\">".$data['delete']." ".$data['edit']."</div>\n";
					$res .= "\t</div>\n";
				$res .= "\t</div>\n";
				$res .= @$this->recursive_nestable($li);
			$res .= "</li>\n";
		}
		$res .= $exists ? "</ol>\n":'';

		return $res;
	}

	public function get_generated_chart($ci_result) {
		foreach ($ci_result as $data) {
			$parent = (empty($data->parent)) ? 0 : $data->parent;
			$this->ci_arr_parent[$parent][] = $data->organization_chart_id;
			$this->ci_arr_data[$data->organization_chart_id]['organization_chart_id'] = $data->organization_chart_id;
			$this->ci_arr_data[$data->organization_chart_id]['organization_chart_name'] = $data->organization_chart_name;
		}

		return json_decode(json_encode($this->recursive_generated_chart()));
	}

	private function recursive_generated_chart($parent = 0) {
		$return = [];
		foreach ($this->ci_arr_parent[$parent] as $li) {

			$exists = isset($this->ci_arr_parent[$li]);

			$return[$li] = $this->ci_arr_data[$li];
			$return[$li]['child_exists'] = $exists;
			if ($exists)
				$return[$li]['child'] = @$this->recursive_generated_chart($li);
		}

		return $return;
	}
}
