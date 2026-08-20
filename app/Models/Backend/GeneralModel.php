<?php
namespace App\Models\Backend;
use CodeIgniter\Model;

class GeneralModel extends Model {
	public function getSettingsModel(int $lang_id) {
		$query = $this->db->table('settings');
		$query->select('settings.*,
						settings_lang.site_footer');
		$query->join('settings_lang', 'settings_lang.setting_id = settings.setting_id AND settings_lang.lang_id = '.$lang_id, 'left');
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function getDesignSettingsModel() {
		$query = $this->db->table('design_settings');
		$query->select('*');
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function getUsersModel() {
		$query = $this->db->table('users');
		$query->select('user_id,
						user_name_surname,
						user_image');

		$query->where('user_status', FORM_ACTIVE_NUMBER);
		$query->orderBy('user_name_surname', 'ASC');

		return $query->get()->getResult();
	}

	public function languagesInfoModel(string $lang_code) {
		$query = $this->db->table('languages');
		$query->select('*');

		$query->where('lang_code', $lang_code);
		$query->where('lang_status', FORM_ACTIVE_NUMBER);
		$query->limit(1);

		return $query->get()->getRow();
	}

	public function languagesDefaultModel() {
		$query = $this->db->table('languages');
		$query->select('languages.*');
		$query->join('settings', 'settings.backend_lang_default = languages.lang_id', 'left');

		if (session()->has('lang')) {
			$query->where('languages.lang_code', session()->get('lang'));
		} else {
			$query->where('languages.lang_id = (select backend_lang_default from settings where backend_lang_default = languages.lang_id)');
		}

		$query->where('languages.lang_status', FORM_ACTIVE_NUMBER);
		$query->orderBy('languages.lang_id', 'ASC');
		$query->limit(1);

		$result = $query->get()->getRow();

		if ($result) {
			return [$result->lang_id, $result->lang_code];
		}
	}

	public function languagesListModel() {
		$query = $this->db->table('languages');
		$query->select('languages.*,
						settings.backend_lang_default');
		$query->join('settings', 'settings.backend_lang_default = languages.lang_id', 'left');

		$query->where('languages.lang_status', FORM_ACTIVE_NUMBER);
		$query->orderBy('languages.lang_id', 'ASC');

		return $query->get()->getResult();
	}

	/*****************************************************/

	public function contactDefaultModel(int $lang_id) {
		$query = $this->db->table('contact_information');
		$query->select('contact_information.contact_telephone,
						contact_information.contact_email,
						contact_information_lang.contact_address');
		$query->join('contact_information_lang', 'contact_information_lang.contact_id = contact_information.contact_id AND contact_information_lang.lang_id = '.$lang_id, 'left');

		$query->where('contact_information.contact_default', FORM_ACTIVE_NUMBER);

		return $query->get()->getRow();
	}

	public function adminMenuModel(int $lang_id, int $parent_id = 0) {
		$query = $this->db->table('dashboard_menu');
		$query->select('dashboard_menu.dashboard_menu_id,
						dashboard_menu.dashboard_menu_icon,
						dashboard_menu.dashboard_menu_url,
						dashboard_menu_lang.dashboard_menu_name');
		$query->join('dashboard_menu_lang', 'dashboard_menu_lang.dashboard_menu_id = dashboard_menu.dashboard_menu_id AND dashboard_menu_lang.lang_id = '.$lang_id, 'left');

		$query->where('dashboard_menu.status', FORM_ACTIVE_NUMBER);
		$query->where('dashboard_menu.dashboard_menu_parent_id', $parent_id);
		$query->orderBy('dashboard_menu.dashboard_menu_order', 'ASC');

		$result = $query->get()->getResult();

		$menu = [];
		if (isNotNull($result)) {
			foreach ($result as $row) {

				$menu[] = [
					'dashboard_menu_name' => $row->dashboard_menu_name,
					'dashboard_menu_icon' => $row->dashboard_menu_icon,
					'dashboard_menu_url' => $row->dashboard_menu_url,
					'submenu' => $this->adminMenuModel($lang_id, $row->dashboard_menu_id),
        ];

			}
		}

		return $menu;
	}

	public function removeDropifyImageModel(string $table, array $fileColons, array $where, string $filePath) {
		$query = $this->db->table($table);

		$query->where($where);
		$query->limit(1);

		$sql = $query->get()->getRow();

		if (isNotNull($sql) ) {

			$result = $this->updateModel($table, $fileColons, $where);
			if ($result !== FALSE) {
				foreach ($fileColons as $key => $row) {
					unlinkFile($filePath, $sql->$key);
				}

				$status = TRUE;
			} else {
				$status = FALSE;
			}

		}else{
			$status = FALSE;
		}

		return $status;
	}

	public function totalRecordModal(string $table, array $where = NULL) {
		$query = $this->db->table($table);

		if (isNotNull($where)) {
			$query->where($where);
		}

		return $query->countAllResults();
	}

	public function lastIDModel(string $table, string $columnID) {
		$query = $this->db->table($table);
		$query->select($columnID);

		$query->orderBy($columnID, 'DESC');
		$query->limit(1);

		$result = $query->get()->getRow();

		if (isNotNull($result)) {
			$last_id = $result->$columnID + 1;
		} else {
			$last_id = 1;
		}

		return $last_id;
	}

	public function insertModel(string $table, array $where) {
		$this->db->transStart();
		$this->db->transStrict(FALSE);
		$this->db->table($table)->insert($where);
		$last_id = $this->db->insertID();
		$this->db->transComplete();

		if ($this->db->transStatus() === FALSE) {
			$this->db->transRollback();
			return FALSE;
		} else {
			$this->db->transCommit();
			return $last_id;
		}
	}

	public function updateModel(string $table, array $where, array $data) {
		$this->db->transBegin();
		$this->db->table($table)->update($where, $data);

		if ($this->db->transStatus() === FALSE) {
			$this->db->transRollback();
			return FALSE;
		} else {
			$this->db->transCommit();
			return TRUE;
		}
	}

	public function deleteModel(string $table, array $where) {
		$delete = $this->db->table($table)->delete($where);
		$this->tableReset($table);
		return $delete;
	}

	public function tableReset(string $table) {
		$this->db->query("ALTER TABLE `$table` AUTO_INCREMENT = 1");
	}
}
