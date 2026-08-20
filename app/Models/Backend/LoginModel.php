<?php
namespace App\Models\Backend;
use CodeIgniter\Model;

class LoginModel extends Model {
	public function userControlModel(string $email_address) {
		$query = $this->db->table('users');
		$query->select('user_id,
						user_name_surname,
						user_email_address,
						user_password');

		$query->where('user_email_address', $email_address);
		$query->where('user_status', FORM_ACTIVE_NUMBER);
		$query->limit(1);

		return $query->get()->getRow();
	}
}
