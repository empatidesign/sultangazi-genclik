<?php
return [
	// General
	'homePage' => 'Ana Sayfa',
	'defaultFooter' => 'Copyright © '.nowDate('Y').' Empati Design '.anchor('http://www.empatidesign.com', 'www.empatidesign.com', ['target' => '_blank']),
	'loading' => 'Lütfen Bekleyiniz...',
	'close' => 'Kapat',
	'success' => [
		'title' => 'Başarılı',
		'description' => 'İşleminiz gerçekleştirilmiştir.',
		'recordDeleted' => 'Silme işlemi başarılı.'
	],
	'warning' => [
		'title' => 'Uyarı',
		'description' => 'Onaylanan işlem geri alınamaz!'
	],
	'error' => [
		'title' => 'Hata',
		'description' => 'Hata oluştu.',
		'ajax' => 'Doğrudan komut dosyasına erişime izin verilmiyor.',
		'insert' => 'Kayıt ekleme işlemi esnasında hata oluştu.',
		'update' => 'Kayıt güncelleme işlemi esnasında hata oluştu.',
		'delete' => 'Silme işlemi esnasında hata oluştu.',
		'noRecord' => 'Kayıt bulunamadı.'
	],
	'choose' => 'Seçiniz',
	'active' => 'Aktif',
	'passive' => 'Pasif',
	'button' => [
		'save' => 'Kaydet',
		'saveAndStayButton' => 'Kaydet ve Kal',
		'saveAndExitButton' => 'Kaydet ve Çık',
		'sendButton' => 'Gönder',
		'acceptButton' => 'Tamam',
		'cancelButton' => 'İptal',
		'upload' => 'Yükle',
		'transferSelected' => 'Seçilileri Aktar'
	],
	'process' => [
		'title' => 'İşlem',
		'addNewRecord' => 'Yeni Kayıt Ekle',
		'editRecord' => 'Kayıt Düzenle',
		'edit' => 'Düzenle',
		'delete' => 'Sil',
		'detail' => 'Detay',
		'apply' => 'Uygula',
		'clear' => 'Temizle',
		'sortBy' => 'Sırala',
		'sitePreview' => 'Site Önizleme',
		'copy' => 'Kopyala',
		'print' => 'Yazdır',
		'multipleSelection' => 'Çoklu seçim yapabilirsiniz.'
	],
	'filter' => [
		'title' => 'Filtreleme Seçenekleri',
		'button' => [
			'removeFilter' => 'Filtreyi Kaldır',
			'filter' => 'Filtrele'
		]
	],
	'default' => [
		'title' => 'Varsayılan',
		'make' => 'Varsayılan Yap'
	],
	'password' => [
		'show' => 'Şifre Göster',
		'change' => 'Şifre Değiştir'
	],
	'seo' => [
		'title' => 'Sayfa başlığını ifade eder. Boş bırakılır ise genel ayarlar uygulanır.',
		'keywords' => 'Arama motorları için anahtar kelime alanıdır. Boş bırakılır ise genel ayarlar uygulanır.',
		'description' => 'Arama motorları için açıklama alanıdır. Boş bırakılır ise genel ayarlar uygulanır.',
		'slug' => 'Sayfa URL linki olarak kullanılır. Boş bırakılır ise otomatik oluşturulur.'
	],
	'language' => [
		'title' => 'Dil',
		'tr' => 'Türkçe',
		'other' => 'Diğer Diller'
	],
	'id' => 'ID',
	'autoCreate' => 'Otomatik Oluştur',
	'yes' => 'Evet',
	'no' => 'Hayır',
	'noEmailAddress' => 'E-Mail adresi tanımlı olmadığından gönderim işlemi yapılamadı.',
	'tryAgain' => 'Tekrar Dene',
	'min' => 'Min.',
	'max' => 'Max.',
	'lastUpdateDate' => 'Son Güncelleme Tarihi:',
	'notUpdated' => 'Güncelleme yapılmamış',
	'genclik' => 'Gençlik',
	'unidentifiedUserAgent' => 'Unidentified User Agent',
	'channel' => 'Platform & Kanal',
	'or' => 'veya',
	'link' => 'Link',
	'search' => 'Arama',
	'all' => 'Tümü',
	'share' => 'Paylaş',
	'back' => 'Geri',
	'mobile' => 'Mobil Durum',
	'pushNotification' => 'Mobil Uygulamaya Bildirim Olarak Gönderilsin mi?',
	'pushNotificationTitle' => 'Mobil Bildirim',
	'export' => [
		'title' => 'Dosya Aktarımı',
		'selectedTitle' => 'Seçilileri Aktar',
		'pdf' => [
			'selectedExport' => "Seçilileri PDF'e Aktar"
		],
		'alert' => [
			'notSelected' => 'Lütfen aktarım yapılacak kayıt(lar) seçiniz.',
			'success' => 'Dosya aktarımı tamamlandı.'
		]
	],

	// SMTP
	'smtp' => [
		'title' => [
			'contactRequest' => 'İletişim Talep Mesajı',
			'presidentContactRequests' => 'Başkan İletişim Talep Mesajı'
		],
		'messages' => [
			'infoMissing' => 'E-Mail ayarları eksik.'
		]
	],

	// Select2
	'select2' => [
		'noRecord' => 'Sonuç bulunamadı'
	],

	// Multi Select
	'multiSelect' => [
		'allAdd' => 'Tümünü Ekle',
		'allRemove' => 'Tümünü Sil',
		'selected' => 'Seçilileri'
	],

	// Fine Uploader
	'fineUploader' => [
		'messages' => [
			'dragDrop' => 'Resimleri Buraya Sürükleyip, Bırakabilirsiniz',
			'dragDropDesc' => 'Resimleri bu alana sürükleyip, bırakarak yada aşağıdaki butona tıklayarak yükleyebilirsiniz.',
			'uploadError' => 'Yükleme yapabilmek için varsayılan dile ait başlık alanı yazılmalıdır.'
		]
	],

	// File Upload Description
	'fileUpload' => [
		'description1' => '.pdf ve .jpg uzantılı dosya yükleyebilirsiniz. Dosya yükleme limiti: 60 MB ile sınırlıdır.'
	],

	// Dropify
	'dropifyMessages' => [
		'messages' => [
			'default' => 'Dosyayı buraya sürükleyin veya tıklayın',
			'replace' => 'Değiştirmek için sürükleyip bırakın veya tıklayın',
			'remove' => 'Sil',
			'error' => 'Hata, yanlış bir şey eklendi'
		]
	],

	// Moment
	'montName' => 'Ay',
	'moment' => [
		'monthsLong' => [
			'january' => 'Ocak',
			'february' => 'Şubat',
			'march' => 'Mart',
			'april' => 'Nisan',
			'may' => 'Mayıs',
			'june' => 'Haziran',
			'july' => 'Temmuz',
			'august' => 'Ağustos',
			'september' => 'Eylül',
			'october' => 'Ekim',
			'november' => 'Kasım',
			'december' => 'Aralık'
		],
		'monthsShort' => [
			'jan' => 'Oca',
			'feb' => 'Şub',
			'mar' => 'Mar',
			'apr' => 'Nis',
			'may' => 'May',
			'jun' => 'Haz',
			'jul' => 'Tem',
			'aug' => 'Ağu',
			'sep' => 'Eyl',
			'oct' => 'Eki',
			'nov' => 'Kas',
			'dec' => 'Ara'
		]
	],

	// Datatable
	'datatable' => [
		'copy' => 'Panoya kopyalandı',
		'copyTotal' => 'Panoya %d satır kopyalandı',
		'decimal' => ',',
		'noRecord' => 'Tabloda herhangi bir veri mevcut değil',
		'info' => '_TOTAL_ kayıttan _START_ - _END_ arasındaki kayıtlar gösteriliyor',
		'infoEmpty' => 'Kayıt yok',
		'infoFiltered' => '(_MAX_ kayıt içerisinden bulunan)',
		'infoPostFix' => '',
		'infoThousands' => '.',
		'lengthMenu' => 'Sayfada _MENU_ kayıt göster',
		'loadingRecords' => 'Yükleniyor...',
		'processing' => 'Yükleniyor...',
		'search' => 'Ara:',
		'zeroRecords' => 'Eşleşen kayıt bulunamadı',
		'pagination' => [
			'first' => 'İlk',
			'last' => 'Son',
			'next' => 'Sonraki',
			'previous' => 'Önceki'
		]
	],

	// Login Form
	'loginForm' => [
		'title' => 'Hoşgeldiniz!',
		'description' => 'Hesaba erişmek için e-mail adresinizi ve şifrenizi girin.',
		'rememberMe' => 'Beni Hatırla',
		'button' => 'Giriş Yap',
		'userControl' => 'Kullanıcı bulunamadı veya aktif değil.',
		'alert' => [
			'emailError' => 'E-Mail adresi yanlış veya kullanıcı pasif durumdadır.',
			'passwordError' => 'Şifre yanlış.'
		]
	],

	// Forgot My Password
	'forgotMyPassword' => [
		'title' => 'Şifremi Unuttum?'
	],

	// Header
	'headerMenu' => [
		'goToTheSite' => 'Siteye Git',
		'settings' => 'Ayarlar',
		'logout' => 'Çıkış',
		'languageChange' => 'Dil Değiştir'
	],

	// Standart Form
	'form' => [
		'name' => 'Adı',
		'surname' => 'Soyadı',
		'companyName' => 'Firma Adı',
		'telephone' => 'Telefon',
		'emailAddress' => 'E-Mail Adresi',
		'password' => 'Şifre',
		'country' => 'Ülke',
		'city' => 'Şehir',
		'district' => 'İlçe'
	]
];
