<?php
return [
	// Projects
	'projectsTitle' => 'Projeler',

	// Categories
	'categories' => [
		'title' => 'Proje Kategorileri',
		'general' => [
			'status' => 'Onay',
			'homePage' => 'Ana Sayfa',
			'icon' => [
				'title' => 'İkon',
				'title2' => 'İkon HTML Kodu (Font Awesome)'
			],
			'name' => 'Kategori',
			'total' => 'Toplam Proje',
			'order' => 'Sıra',
			'seo' => [
				'slug' => 'Slug'
			],
			'createdDate' => 'Ekleme Tarihi',
			'updatedDate' => 'Güncelleme Tarihi'
		],
		'alert' => [
			'cannotBeDeleted' => 'Silmeye çalıştığınız kategori projeye bağlı olduğu için silinemez.'
		]
	],

	// Contents
	'contents' => [
		'title' => 'Proje İçerikleri',
		'tabMenu' => [
			'tab1' => 'Genel Bilgiler',
			'tab2' => 'Seo Bilgileri',
			'tab3' => 'Konum Bilgileri',
			'tab4' => 'Proje Resimleri'
		],
		'general' => [
			'status' => 'Onay',
			'homePage' => 'Ana Sayfa',
			'projectStatus' => 'Proje Durumu',
			'category' => 'Proje Kategorisi',
			'name' => 'Proje Adı',
			'date' => [
				'start' => 'Proje Başlangıç Tarihi',
				'end' => 'Proje Bitiş Tarihi'
			],
			'location' => [
				'neighbourhoods' => 'Mahalle',
				'address' => 'Proje Adresi',
				'responsible' => 'Tesis Sorumlusu',
				'telephone' => 'Proje Telefon Numarası',
				'map' => 'Harita Link',
				'coordinate' => [
					'lat' => 'Lat Konum',
					'long' => 'Long Konum'
				]
			],
			'description' => 'Genel Açıklama',
			'seo' => [
				'title' => 'Seo Title',
				'keywords' => 'Seo Keywords',
				'description' => 'Seo Description',
				'slug' => 'Slug'
			],
			'image' => [
				'title' => 'Resim',
				'defaultImage' => 'Kapak Resim'
			],
			'createdDate' => 'Ekleme Tarihi',
			'updatedDate' => 'Güncelleme Tarihi'
		],
		'alert' => [
			'coordinate' => 'Proje lokasyonu için harita üzerinden ilgili lokasyonu seçerek otomatik koordinat oluşturabilirsiniz.'
		]
	],

	// Status
	'status' => [
		'title' => 'Proje Durumları',
		'general' => [
			'status' => 'Onay',
			'name' => 'Durum',
			'total' => 'Toplam Proje',
			'order' => 'Sıra',
			'createdDate' => 'Ekleme Tarihi',
			'updatedDate' => 'Güncelleme Tarihi'
		],
		'alert' => [
			'cannotBeDeleted' => 'Silmeye çalıştığınız durum projeye bağlı olduğu için silinemez.'
		]
	],

	// Result
	'result' => [
		'add' => [
			'categories' => '{0} adlı proje kategori bilgisi eklendi.',
			'contents' => '{0} adlı proje bilgisi eklendi.',
			'status' => '{0} adlı proje durum bilgisi eklendi.'
		],
		'edit' => [
			'categories' => '{0} adlı proje kategori bilgisi güncellendi.',
			'contents' => '{0} adlı proje bilgisi güncellendi.',
			'status' => '{0} adlı proje durum bilgisi güncellendi.'
		]
	]
];
