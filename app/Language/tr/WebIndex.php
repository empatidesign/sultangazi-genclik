<?php
return [
  'world' => [
    'title' => 'Yüksek Çalışma Kapasitesi'
  ],

  /**
   * Anasayfadaki "Eğitim Kurumlarımız" alanı.
   * Kartlar kendi alan adlarına yönlendirir; içerik buradan düzenlenir.
   */
  'education' => [
    'title'       => 'EĞİTİM',
    'title_bold'  => 'KURUMLARIMIZ',
    'description' => 'Okul öncesinden gençliğe uzanan eğitim yolculuğunda çocuklarımızın ve gençlerimizin yanındayız.',
    'visit'       => 'Siteyi Ziyaret Et',

    'items' => [
      'anakucagi' => [
        'name'        => 'Ana Kucağı',
        'subtitle'    => 'Okul Öncesi Eğitim',
        'description' => 'Okul öncesi çağdaki çocuklarımıza güvenli, sıcak ve oyun temelli bir eğitim ortamı sunar.',
      ],
      'kasifcocuk' => [
        'name'        => 'Kaşif Çocuk',
        'subtitle'    => 'Bilim ve Sanat Merkezi',
        'description' => 'Çocukların merak duygusunu bilim, sanat ve tasarım atölyeleriyle keşfe dönüştürür.',
      ],
      'seda' => [
        'name'        => 'SEDA',
        'subtitle'    => 'Sultangazi Eğitim Akademisi',
        'description' => 'Öğrencilerimizi sınavlara hazırlayan, akademik başarıyı destekleyen ücretsiz eğitim akademisi.',
      ],
      'bilimmerkezi' => [
        'name'        => 'Bilim Merkezi',
        'subtitle'    => 'Deney ve Keşif Alanı',
        'description' => 'İnteraktif deney düzenekleri ve sergi alanlarıyla bilimi yaparak yaşayarak öğretir.',
      ],
    ],
  ],

  /**
   * Spor branşları alanı (anasayfa).
   * Detay bağlantıları Spor Akademisi sitesine gider.
   */
  'sport' => [
    'title'       => 'SPOR',
    'title_bold'  => 'BRANŞLARIMIZ',
    'description' => 'Sultangazi Belediyesi olarak 16 branşta, alanında uzman antrenörler eşliğinde ücretsiz spor eğitimi veriyoruz. Tüm branşların ayrıntılı programı, antrenman saatleri ve başvuru koşulları Sultangazi Spor Akademisi\'nde yer alıyor.',
    'detail'      => 'Akademide İncele',
    'all'         => 'TÜM BRANŞLAR',
  ],

  /**
   * Hizmet tesisleri alanı (anasayfa).
   * Veriler Spor Akademisi servisinden gelir.
   */
  'facilities' => [
    'title'       => 'HİZMET',
    'title_bold'  => 'TESİSLERİMİZ',
    'description' => 'Olimpik havuzdan atletizm pistine, dövüş sanatları salonundan gençlik merkezlerine kadar tüm tesislerimiz sporcularımızın hizmetinde.',
    'capacity'    => 'Kapasite',
    'detail'      => 'Tesisi İncele',
    'all'         => 'TÜM TESİSLER',
  ],

  /**
   * Spor Akademisi etkinlik / kurs programı (anasayfa).
   */
  'academy_events' => [
    'title'       => 'AKADEMİ',
    'title_bold'  => 'PROGRAMI',
    'description' => 'Sultangazi Spor Akademisi bünyesinde devam eden kurs ve antrenman programları.',
    'age_group'   => 'Yaş Grubu',
    'all'         => 'TÜM PROGRAMLAR',
  ],

  /**
   * Etkinlikler alani (anasayfa) - Nexora genel katalog servisi.
   */
  'nexora_events' => [
    'title'       => 'ETKİNLİK',
    'title_bold'  => 'TAKVİMİMİZ',
    'description' => 'Sultangazi Belediyesi bünyesinde düzenlenen etkinliklere katılın; başvurular Sultanşehir üzerinden alınır.',
    'date'        => 'Tarih',
    'available'   => 'Kalan Kontenjan',
    'free'        => 'Ücretsiz',
    'all'         => 'TÜM ETKİNLİKLER',
  ],
];
