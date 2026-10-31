<?php

return [
    'demo' => env('APP_DEMO', false),
    'site' => [
        'name' => 'Dolanan Nalar', 'slogan' => 'Cilik-Cilik, Nalar Apik!',
        'whatsapp' => '6289508534721', 'address' => 'Jebres, Surakarta', 'email' => '', 'instagram' => '',
        'headline' => 'Rasa ingin tahu, tumbuh jadi bisa.',
        'description' => 'Les privat di rumah yang memberi ruang untuk bertanya, mencoba, dan memahami. Dari langkah kecil hari ini, untuk nalar yang tumbuh setiap hari.',
        'transport' => 8000, 'distance' => 5, 'extra_20' => 10000, 'extra_60' => 15000,
        'prices' => [['label' => 'TK & SD kelas 1–2', 'price' => 25000], ['label' => 'SD kelas 3–4', 'price' => 30000], ['label' => 'SD kelas 5–6', 'price' => 40000], ['label' => 'SMP kelas 7–8', 'price' => 45000], ['label' => 'SMP kelas 9', 'price' => 50000]],
        'services' => [
            ['title' => 'Langkah pertama', 'level' => 'TK & SD AWAL', 'text' => 'Mengenal huruf, angka, dan kebiasaan belajar lewat aktivitas yang dekat dengan dunia anak.', 'image' => 'jelajah-cerita'],
            ['title' => 'Berani bilang “aku bisa”', 'level' => 'SD KELAS 3–6', 'text' => 'Mengurai materi sekolah dan berlatih memahami soal, dengan pendampingan sesuai kebutuhan anak.', 'image' => 'bermain-angka'],
            ['title' => 'Nalar makin terasah', 'level' => 'SMP KELAS 7–9', 'text' => 'Mendalami konsep dan melatih cara berpikir. Bisa fokus satu mata pelajaran atau beberapa sekaligus.', 'image' => 'tumbuh-bersama'],
        ],
    ],
];
