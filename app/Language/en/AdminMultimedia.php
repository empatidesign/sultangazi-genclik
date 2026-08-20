<?php
return [
	// Multimedia
	'multimediaTitle' => 'İçerikler',

	// Gallery Categories
	'galleryCategories' => [
		'title' => 'Fotoğraf Galerisi Kategorileri',
		'general' => [
			'status' => 'Onay',
			'image' => 'Resim',
			'name' => 'Kategori Adı',
			'createdDate' => 'Ekleme Tarihi',
			'updatedDate' => 'Güncelleme Tarihi'
		]
	],

	// Gallery
	'gallery' => [
		'title' => 'Fotoğraf Galerisi',
		'general' => [
			'status' => 'Onay',
			'category' => 'Kategori',
			'name' => 'Galeri Adı',
			'image' => [
				'title' => 'Resim',
				'total' => 'Toplam Resim'
			],
			'createdDate' => 'Ekleme Tarihi',
			'updatedDate' => 'Güncelleme Tarihi'
		],
		'alert' => [
			'order' => 'Sıralama yapmak için sürükle bırak yapınız.'
		]
	],

	// Video Gallery
	'videoGallery' => [
		'title' => 'Video Galeri',
		'general' => [
			'status' => 'Onay',
			'image' => 'Resim',
			'name' => 'Video Galeri Adı',
			'link' => 'Video Link',
			'date' => 'Video Tarih',
			'createdDate' => 'Ekleme Tarihi',
			'updatedDate' => 'Güncelleme Tarihi'
		]
	],

	// Result
	'result' => [
		'add' => [
			'galleryCategories' => '{0} adlı fotoğraf kategori bilgisi eklendi.',
			'gallery' => '{0} adlı kategori için galeri oluşturuldu.',
			'videoGallery' => '{0} adlı video galeri bilgisi eklendi.'
		],
		'edit' => [
			'galleryCategories' => '{0} adlı fotoğraf kategori bilgisi güncellendi.',
			'gallery' => '{0} adlı kategori için galeri bilgisi güncellendi.',
			'videoGallery' => '{0} adlı video galeri bilgisi güncellendi.'
		]
	]
];
