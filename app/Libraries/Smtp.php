<?php
namespace App\Libraries;

use App\Models\Backend\SettingModel;

class Smtp {

	protected $SettingModel;

	public function index(string $title, string $html, string $to_email, string $attachment = NULL) {
		$this->SettingModel = new SettingModel();

		$sql = $this->SettingModel->smtpModel();
		if (isNotNull($sql)) {
			if (isNotNull($sql->smtp_email_title) && isNotNull($sql->smtp_host) && isNotNull($sql->smtp_email) && isNotNull($sql->smtp_password) && isNotNull($sql->smtp_port)) {

				$email_config = [
					'protocol' => 'smtp',
					'SMTPHost' => $sql->smtp_host,
					'SMTPUser' => $sql->smtp_email,
					'SMTPPass' => $sql->smtp_password,
					'SMTPPort' => $sql->smtp_port,
					'SMTPCrypto' => $sql->smtp_crypto,
					'mailType' => 'html'
				];

				$email = \Config\Services::email();
				$email->initialize($email_config);

				$email->setFrom($sql->smtp_email, $sql->smtp_email_title);
				$email->setTo($to_email);

				$email->setSubject($title);
				$email->setMessage($html);

				if (isNotNull($attachment)) {
					$email->attach($attachment);
				}

				if ($email->send()) {
					return [TRUE, ''];
				} else {
					//echo $email->printDebugger();
					return [FALSE, lang('Admin.error.description')];
				}

			} else {
				return [FALSE, lang('Admin.smtp.messages.infoMissing')];
			}

		} else {
			return [FALSE, lang('Admin.error.description')];
		}
	}
}
