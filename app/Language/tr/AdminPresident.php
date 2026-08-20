<?php
return [
	// President
	'presidentTitle' => 'Başkan',

	// General Information
	'generalInformation' => [
		'title' => 'Başkan Genel Bilgiler',
		'general' => [
			'nameSurname' => 'Başkan Adı Soyadı',
			'subTitle' => 'Alt Başlık',
			'link' => 'Link',
			'image' => [
				'web' => 'Web Resim',
				'mobile' => 'Mobil Resim'
			],
			'facebook' => 'Başkan Facebook Adresi',
			'twitter' => 'Başkan Twitter Adresi',
			'instagram' => 'Başkan Instagram Adresi',
			'youtube' => 'Başkan Youtube Adresi'
		]
	],

	// Contents
	'contents' => [
		'title' => 'Başkan İçerikleri',
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
		'title' => 'Başkan Galeri',
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

	// Result
	'result' => [
		'add' => [
			'contents' => '{0} adlı başkan içerik bilgisi eklendi.',
			'gallery' => 'Galeriye yeni fotoğraf eklendi.'
		],
		'edit' => [
			'generalInformation' => 'Başkan genel bilgileri güncellendi.',
			'contents' => '{0} adlı başkan içerik bilgisi güncellendi.'
		]
	]
];
