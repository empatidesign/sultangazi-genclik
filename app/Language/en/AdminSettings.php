<?php
return [
	// Settings
	'settingsTitle' => 'Ayarlar',

	// General Settings
	'generalSettings' => [
		'title' => 'Genel Ayarlar',
		'siteTitle' => 'Varsayılan Title (Site Başlığı)',
		'siteKeywords' => 'Varsayılan Keywords (Site Anahtar Kelimeleri)',
		'siteDescription' => 'Varsayılan Description (Site Açıklaması)',
		'siteFooter' => 'Footer',
		'sslStatus' => 'SSL Durumu',
		'siteTelephone' => 'Genel Site Telefon Numarası',
		'siteEmailAddress' => 'Genel Site E-Mail Adresi',
		'company' => [
			'title' => 'Firma Ayarları',
			'name' => 'Firma Ünvanı',
			'owner' => 'Firma Sahibi',
			'emailAddress' => 'Firma E-Mail Adresi',
			'telephone' => 'Firma Telefon Numarası',
			'taxAdministration' => 'Vergi Dairesi',
			'taxNumber' => 'Vergi No',
			'kep' => 'Kep Adresi',
			'address' => 'Adres',
		],
		'language' => [
			'title' => 'Varsayılan Dil',
			'frontend' => 'Web Varsayılan Dil',
			'backend' => 'Kontrol Paneli Varsayılan Dil'
		],
		'seo' => [
			'title' => 'Seo Ayarları',
			'googleMeta' => 'Google Meta Tagı Aktif',
			'canonicalUrl' => 'Canonical Url Aktif',
			'facebook' => 'Facebook Open Graph Meta Etiketler Aktif',
			'twitter' => 'Twitter Meta Etiketler Aktif',
			'siteMapUrl' => 'Site Haritası Linki',
			'siteMapRobots' => 'Robots.txt'
		],
		'googleReCaptcha' => [
			'title' => 'Google ReCaptcha V3',
			'description' => 'CAPTCHA, sınama-yanıt doğrulaması olarak bilinen bir güvenlik önlemidir. CAPTCHA spam ve şifre çözme koruması sağlanmasına yardımcı olur. Bunun için sizden basit bir testi yanıtlamanızı isteyerek şifre korumalı bir hesaba girmeye çalışan bir bilgisayar değil insan olduğunuzu kanıtlamanızı sağlar. <a target="_blank" href="https://www.google.com/recaptcha/about/">reCAPTCHA</a>.',
			'status' => 'Durum',
			'key' => 'Site Anahtarı',
			'secret' => 'Gizli Anahtar',
			'page' => [
				'contact' => 'İletişim Formu',
				'presidentContact' => ' Başkan İletişim Formu'
			]
		],
		'googleMap' => [
			'title' => 'Google Map API',
			'key' => 'Google Map API Key'
		],
		'cookies' => [
			'title' => 'Çerez Bildirimi',
			'status' => 'Durum',
			'link' => 'Detay Bilgi Bağlantısı',
			'description' => 'Açıklama'
		]
	],

	// Social Media Settings
	'socialMedia' => [
		'title' => 'Sosyal Medya Ayarları',
		'links' => [
			'facebook' => 'Facebook',
			'twitter' => 'Twitter',
			'instagram' => 'Instagram',
			'youtube' => 'Youtube',
			'linkedin' => 'Linkedin',
			'dribbble' => 'Dribbble',
			'vimeo' => 'Vimeo',
			'pinterest' => 'Pinterest',
			'behance' => 'Behance',
			'reddit' => 'Reddit'
		]
	],

	// Tracking Codes
	'trackingCodes' => [
		'title' => 'Takip Kodları',
		'general' => [
			'googleAnalytics' => [
				'title' => 'Google Analytics',
				'description' => '(UA-00000000-1)'
			],
			'googleWebConsole' => 'Google Web Console',
			'googleTagManager' => 'Google Tag Manager',
			'googleRemarketing' => 'Google Remarketing',
			'googleConversion' => 'Google Conversion (Ödeme Sayfası)',
			'facebookPixel' => 'Facebook Pixel',
			'mailchimp' => 'Mailchimp',
			'headTags' => 'Head Tagları',
			'other' => 'Diğer'
		]
	],

	// Countries
	'countries' => [
		'title' => 'Ülkeler',
		'general' => [
			'status' => 'Onay',
			'code' => 'Ülke Kodu',
			'name' => 'Ülke Adı',
			'order' => 'Sıra'
		]
	],

	// Cities
	'cities' => [
		'title' => 'Şehirler',
		'general' => [
			'status' => 'Onay',
			'countryName' => 'Ülke Adı',
			'cityName' => 'Şehir Adı'
		]
	],

	// Districts
	'districts' => [
		'title' => 'İlçeler',
		'general' => [
			'status' => 'Onay',
			'countryName' => 'Ülke Adı',
			'cityName' => 'Şehir Adı',
			'districtName' => 'İlçe Adı'
		]
	],

	// Neighbourhoods
	'neighbourhoods' => [
		'title' => 'Mahalleler',
		'general' => [
			'status' => 'Onay',
			'code' => 'Mahalle Kodu',
			'name' => 'Mahalle Adı',
			'order' => 'Sıra'
		]
	],

	// Maintenance Mode
	'maintenanceMode' => [
		'title' => 'Bakım Modu',
		'titleForm' => 'Web Sitesini Bakım Moduna Alma',
		'description' => 'Web sitesini bakım moduna aldıktan sonra kullanıcılara gösterilecek açıklama alanıdır.',
		'form' => [
			'title' => 'Başlık',
			'description' => 'Açıklama'
		],
		'button' => [
			'active' => 'Bakım Modunu Aktif Et',
			'passive' => 'Bakım Modunu Pasif Et',
			'status' => [
				'active' => 'Şu anda web sitesi <strong>AKTİF</strong> durumdadır.',
				'passive' => 'Şu anda web sitesi <strong>PASİF</strong> durumdadır.'
			]
		],
		'databaseRepair' => [
			'title' => 'Veritabanı Onarımı',
			'button' => 'Veritabanı Onarım İşlemini Başlat',
			'description' => 'Veritabanı onarım işlemi için kullanılır.',
			'dateError' => 'Onarım yapılmamış'
		],
		'cacheClearing' => [
			'title' => 'Ön Bellek Temizleme',
			'button' => 'Ön Bellek Temizleme İşlemini Başlat',
			'description' => 'Yaptığınız değişiklikleri görüntülemek için ön bellek dosyalarını temizleyiniz.',
			'dateError' => 'Cache temizleme yapılmamış'
		]
	],

	// Templates Variable List
	'templatesVariableList' => [
		'title' => 'Değişken Listesi',
		'email' => [
			'contactRequests' => [
				'title' => 'İletişim Talepleri',
				'name' => 'Adı',
				'surname' => 'Soyadı',
				'telephone' => 'Telefon',
				'email' => 'E-Mail Adresi',
				'message' => 'Mesaj',
				'date' => 'Tarih'
			],
			'offers' => [
				'title' => 'Teklif Mesajı',
				'name' => 'Adı',
				'surname' => 'Soyadı',
				'telephone' => 'Telefon',
				'email' => 'E-Mail Adresi',
				'message' => 'Mesaj',
				'date' => 'Tarih'
			],
			'general' => [
				'title' => 'Genel Değişkenler',
				'siteTelephone' => 'Site Telefon',
				'siteEmail' => 'Site E-Mail Adresi'
			]
		],
		'alert' => [
			'recordNotFoundOrInactive' => 'Şablon kaydı bulunamadı veya pasif durumdadır.'
		]
	],

	// E-Mail Settings
	'emailSettings' => [
		'title' => 'E-Mail Ayarları',
		'general' => [
			'subject' => 'SMTP Başlık',
			'subjectDesc' => "E-Mail'in hangi başlıkla gönderileceği alanını ifade eder.",
			'server' => 'SMTP Sunucu',
			'emailAddress' => 'SMTP E-Mail Adresi',
			'password' => 'SMTP Şifre',
			'port' => 'SMTP Port',
			'crypto' => 'SMTP Crypto',
			'serverAddress' => 'Sunucu Adresi:',
			'port' => 'Port:'
		]
	],

	// E-Mail Templates
	'emailTemplates' => [
		'title' => 'E-Mail Şablonları',
		'general' => [
			'choose' => 'E-Mail Şablonu Seçiniz',
			'status' => 'Onay',
			'name' => 'E-Mail Şablon Adı',
			'sendCustomer' => [
				'title' => 'Müşteriye Gönder',
				'description' => 'Şablonun içeriğinin müşteriye gönderilip/gönderilmeyeceğini bildirimini ifade eder.'
			],
			'sendAdmin' => [
				'title' => 'Yöneticiye Gönder',
				'description' => 'Şablonun içeriğinin yöneticiye gönderilip/gönderilmeyeceğini bildirimini ifade eder.'
			],
			'description' => 'Açıklama',
			'detail' => 'Şablon Detayları',
			'alert' => [
				'title' => 'Yukarıdaki menüden seçeceğiniz şablonun detaylarını bu alandan görebilirsiniz.'
			]
		]
	],

	// Language Management
	'languageManagement' => [
		'title' => 'Dil Yönetimi',
		'general' => [
			'status' => 'Onay',
			'code' => 'Dil Kodu',
			'title' => 'Dil Adı',
			'setLocale' => 'Dil Kültürü',
			'flag' => 'Bayrak',
			'percentageLocation' => [
				'title' => 'Yüzde İşaret Konumu',
				'left' => 'Sol Konum',
				'right' => 'Sağ Konum'
			],
			'default' => [
				'frontend' => 'Web Varsayılan Dil',
				'backend' => 'Kontrol Paneli Varsayılan Dil'
			]
		],
		'alert' => [
			'systemError' => 'Sistemde tanımlı dil bulunamadı. Sistemin açılması için dil tanımlanmalıdır.'
		]
	],

	// Manager Accounts
	'managerAccounts' => [
		'title' => 'Yönetici Hesapları',
		'changePassword' => [
			'title' => 'Şifre değiştirmek için tıklayın.',
			'description' => '{0} kullanıcının şifresini değiştiriyorsunuz.',
			'newPassword' => [
				'title' => 'Yeni Şifre',
				'again' => 'Yeni Şifre (Tekrar)'
			]
		],
		'general' => [
			'status' => 'Onay',
			'type' => 'Tip',
			'nameSurname' => 'Adı Soyadı',
			'telephone' => 'Telefon',
			'emailAddress' => 'E-Mail Adresi',
			'password' => [
				'title' => 'Şifre',
				'description' => 'Şifre min. {0} karakter, max. {1} karakter olmalıdır.',
				'again' => 'Şifre (Tekrar)',
			],
			'lastLoginTime' => 'Son Giriş Zamanı',
			'notSignedIn' => 'Giriş Yapmamış',
			'image' => 'Resim'
		]
	],

	// Result
	'result' => [
		'add' => [
			'countries' => '{0} adlı ülke bilgisi eklendi.',
			'cities' => '{0} adlı şehir bilgisi eklendi.',
			'districts' => '{0} adlı ilçe bilgisi eklendi.',
			'neighbourhoods' => '{0} adlı mahalle bilgisi eklendi.',
			'managerAccounts' => '{0} adlı kullanıcı bilgisi eklendi.'
		],
		'edit' => [
			'generalSettings' => 'Genel ayarlar kaydedildi.',
			'socialMediaSettings' => 'Sosyal medya ayarları kaydedildi.',
			'trackingCodes' => 'Takip kodları kaydedildi.',
			'countries' => '{0} adlı ülke bilgisi güncellendi.',
			'cities' => '{0} adlı şehir bilgisi güncellendi.',
			'districts' => '{0} adlı ilçe bilgisi güncellendi.',
			'neighbourhoods' => '{0} adlı mahalle bilgisi güncellendi.',
			'databaseRepair' => 'Veritabanı başarılı bir şekilde onarıldı.',
			'cacheClearing' => 'Ön bellek temizleme işlemi yapılmıştır.',
			'maintenanceModeActive' => 'Web siteniz aktif edilmiştir.',
			'maintenanceModePassive' => 'Web siteniz pasif edilmiştir.',
			'emailSettings' => 'E-Mail ayarları kaydedildi.',
			'emailTemplates' => '{0} adlı e-mail şablonu kaydedildi.',
			'languageManagement' => '{0} adlı dil bilgisi güncellendi.',
			'managerAccounts' => '{0} adlı kullanıcı bilgisi güncellendi.',
			'managerAccountsChangePassword' => '{0} adlı kullanıcı şifresi değiştirildi.'
		]
	]
];
