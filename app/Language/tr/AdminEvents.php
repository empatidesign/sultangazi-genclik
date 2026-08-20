<?php
return [
	// Events
	'eventsTitle' => 'Etkinlikler',

	// Categories
	'categories' => [
		'title' => 'Etkinlik Kategorileri',
		'general' => [
			'status' => 'Onay',
			'name' => 'Kategori',
			'total' => 'Toplam Etkinlik',
			'order' => 'Sıra',
			'createdDate' => 'Ekleme Tarihi',
			'updatedDate' => 'Güncelleme Tarihi'
		],
		'alert' => [
			'cannotBeDeleted' => 'Silmeye çalıştığınız kategori etkinliğe bağlı olduğu için silinemez.'
		]
	],

	// Contents
	'contents' => [
		'title' => 'Etkinlik İçerikleri',
		'tabMenu' => [
			'tab1' => 'Genel Bilgiler',
			'tab2' => 'Seo Bilgileri',
			'tab3' => 'Konum Bilgileri',
			'tab4' => 'Paragraflar',
			'tab5' => 'Mobil İçerikler'
		],
		'general' => [
			'status' => 'Onay',
			'category' => 'Etkinlik Kategorisi',
			'image' => 'Resim',
			'name' => 'Etkinlik Adı',
			'ageGroup' => 'Yaş Grubu',
			'quota' => 'Kontenjan',
			'date' => 'Etkinlik Tarihi',
			'hour' => 'Etkinlik Saati',
			'location' => [
				'title' => 'Etkinlik Yeri Adı',
				'address' => 'Etkinlik Adresi',
				'telephone' => 'Etkinlik Telefon Numarası',
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
			'createdDate' => 'Ekleme Tarihi',
			'updatedDate' => 'Güncelleme Tarihi'
		],
		'paragraphs' => [
			'title' => 'Paragraflar',
			'image' => 'Paragraf Resim',
			'name' => 'Paragraf Adı',
			'createdDate' => 'Kayıt Tarihi',
			'description' => 'Paragraf Açıklaması'
		],
		'alert' => [
			'coordinate' => 'Etkinlik lokasyonu için harita üzerinden ilgili lokasyonu seçerek otomatik koordinat oluşturabilirsiniz.'
		]
	],

	// Result
	'result' => [
		'add' => [
			'categories' => '{0} adlı etkinlik kategori bilgisi eklendi.',
			'contents' => '{0} adlı etkinlik bilgisi eklendi.'
		],
		'edit' => [
			'categories' => '{0} adlı etkinlik kategori bilgisi güncellendi.',
			'contents' => '{0} adlı etkinlik bilgisi güncellendi.'
		]
	]
];
