<?php
return [
	// News
	'newsTitle' => 'Haberler',

	// News
	'news' => [
		'title' => 'Haber İçerikleri',
		'tabMenu' => [
			'tab1' => 'Genel Bilgiler',
			'tab2' => 'Seo Bilgileri',
			'tab3' => 'Paragraflar',
			'tab4' => 'Galeri',
			'tab5' => 'İlgili Müdürlük(ler)',
			'tab6' => 'Mobil İçerikler'
		],
		'general' => [
			'status' => 'Onay',
			'name' => 'Başlık',
			'description' => 'Genel Açıklama',
			'seo' => [
				'title' => 'Seo Title',
				'keywords' => 'Seo Keywords',
				'description' => 'Seo Description',
				'slug' => 'Slug'
			],
			'image' => [
				'title' => 'Resim'
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
		]
	],

	// Announcements
	'announcements' => [
		'title' => 'Duyurular',
		'tabMenu' => [
			'tab1' => 'Genel Bilgiler',
			'tab2' => 'İlgili Müdürlük(ler)',
			'tab3' => 'Mobil İçerikler'
		],
		'general' => [
			'status' => 'Onay',
			'name' => 'Duyuru Adı',
			'link' => 'Duyuru Link',
			'department' => 'İlgili Birim',
			'description' => 'Duyuru Açıklama',
			'seo' => [
				'slug' => 'Slug'
			],
			'createdDate' => 'Ekleme Tarihi',
			'updatedDate' => 'Güncelleme Tarihi'
		]
	],

	// Result
	'result' => [
		'add' => [
			'news' => '{0} adlı haber bilgisi eklendi.',
			'announcements' => '{0} adlı duyuru bilgisi eklendi.'
		],
		'edit' => [
			'news' => '{0} adlı haber bilgisi güncellendi.',
			'announcements' => '{0} adlı duyuru bilgisi güncellendi.'
		]
	]
];
