<?php
return [
	// Map Module
	'mapModuleTitle' => 'Harita Modülü',

	// Map Categories
	'mapCategories' => [
		'title' => 'Harita Kategorileri',
		'general' => [
			'status' => 'Onay',
			'default' => 'Default',
			'name' => 'Kategori Adı',
			'createdDate' => 'Ekleme Tarihi',
			'updatedDate' => 'Güncelleme Tarihi'
		],
		'alert' => [
			'default' => [
				'title' => 'Default olan kayıt haritada otomatik olarak konumlandırmaları gösterir.',
				'delete' => 'Default kayıt silinemez.'
			]
		]
	],

	// Map Locations
	'mapLocations' => [
		'title' => 'Harita Konumları',
		'general' => [
			'status' => 'Onay',
			'category' => 'Harita Kategorisi',
			'types' => [
				'title' => 'Konum Tipi',
				'type1' => 'Standart Konum',
				'type2' => 'Proje Konumu',
				'projects' => 'Proje Tanımı'
			],
			'name' => 'Konum Adı',
			'coordinate' => [
				'lat' => 'Lat Konum',
				'long' => 'Long Konum'
			],
			'createdDate' => 'Ekleme Tarihi',
			'updatedDate' => 'Güncelleme Tarihi'
		]
	],

	// Result
	'result' => [
		'add' => [
			'mapCategories' => '{0} adlı harita kategori bilgisi eklendi.',
			'mapLocations' => '{0} adlı harita konum bilgisi eklendi.'
		],
		'edit' => [
			'mapCategories' => '{0} adlı harita kategori bilgisi güncellendi.',
			'mapLocations' => '{0} adlı harita konum bilgisi güncellendi.'
		]
	]
];
