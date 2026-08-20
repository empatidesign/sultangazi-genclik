<?php
return [
	// Sultangazi
	'sultangaziTitle' => 'Sultangazi',

	// Contents
	'contents' => [
		'title' => 'Sultangazi İçerikleri',
		'tabMenu' => [
			'tab1' => 'Genel Bilgiler',
			'tab2' => 'Seo Bilgileri'
		],
		'general' => [
			'status' => 'Onay',
			'name' => 'İçerik Adı',
			'image' => 'Resim',
			'description' => 'Açıklama',
			'seo' => [
				'title' => 'Seo Title',
				'keywords' => 'Seo Keywords',
				'description' => 'Seo Description',
				'slug' => 'Slug'
			],
			'createdDate' => 'Ekleme Tarihi',
			'updatedDate' => 'Güncelleme Tarihi'
		]
	],

	// Gallery
	'gallery' => [
		'title' => 'Sultangazi Fotoğraf Galerisi',
		'general' => [
			'status' => 'Onay',
			'image' => 'Resim',
			'createdDate' => 'Ekleme Tarihi',
			'updatedDate' => 'Güncelleme Tarihi'
		],
		'alert' => [
			'order' => 'Sıralama yapmak için {0} işaretini tutup, sürükleyebilirsiniz.'
		]
	],

	// Video Gallery
	'videoGallery' => [
		'title' => 'Sultangazi Video Galeri',
		'general' => [
			'status' => 'Onay',
			'image' => 'Resim',
			'categories' => [
				'title' => 'Kategori',
				'category1' => 'Sultangazi Tanıtım Videoları',
				'category2' => 'Mağlova Su Kanalı Belgeseli'
			],
			'name' => 'Video Galeri Adı',
			'link' => 'Video Link',
			'createdDate' => 'Ekleme Tarihi',
			'updatedDate' => 'Güncelleme Tarihi'
		]
	],

	// City Guide Categories
	'cityGuideCategories' => [
		'title' => 'Şehir Rehberi Kategorileri',
		'general' => [
			'status' => 'Onay',
			'name' => 'Rehber Kategori Adı',
			'createdDate' => 'Ekleme Tarihi',
			'updatedDate' => 'Güncelleme Tarihi'
		],
		'alert' => [
			'contents' => 'Silmek istediğiniz kategoriye bağlı şehir rehber içeriği olduğundan silinemez!'
		]
	],

	// City Guide Contents
	'cityGuideContents' => [
		'title' => 'Şehir Rehberi',
		'general' => [
			'status' => 'Onay',
			'name' => 'Rehber Adı',
			'category' => 'Kategori',
			'person' => [
				'nameSurname' => 'Yetkili Adı Soyadı',
				'subTitle' => 'Yetkili Alt Başlık'
			],
			'telephone' => 'Telefon Numarası',
			'fax' => 'Fax',
			'emailAddress' => 'E-Mail Adresi',
			'webAddress' => 'Web Adresi',
			'address' => 'Adres',
			'map' => [
				'latCoordinate' => 'Lat Koordinat',
				'longCoordinate' => 'Long Koordinat'
			],
			'description' => 'Açıklama',
			'logo' => 'Logo',
			'createdDate' => 'Ekleme Tarihi',
			'updatedDate' => 'Güncelleme Tarihi'
		],
		'alert' => [
			'coordinate' => 'Şehir rehberi için harita üzerinden ilgili lokasyonu seçerek otomatik koordinat oluşturabilirsiniz.'
		]
	],

	// Result
	'result' => [
		'add' => [
			'contents' => '{0} adlı içerik bilgisi eklendi.',
			'gallery' => 'Galeriye yeni fotoğraf eklendi.',
			'videoGallery' => '{0} adlı video galeri bilgisi eklendi.',
			'cityGuideCategories' => '{0} adlı şehir rehberi kategori bilgisi eklendi.',
			'cityGuideContents' => '{0} adlı şehir rehberi bilgisi eklendi.'
		],
		'edit' => [
			'contents' => '{0} adlı içerik bilgisi güncellendi.',
			'videoGallery' => '{0} adlı video galeri bilgisi güncellendi.',
			'cityGuideCategories' => '{0} adlı şehir rehberi kategori bilgisi güncellendi.',
			'cityGuideContents' => '{0} adlı şehir rehberi bilgisi güncellendi.'
		]
	]
];
