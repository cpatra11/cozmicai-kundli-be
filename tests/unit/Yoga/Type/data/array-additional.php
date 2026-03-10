<?php
$dataSource = new stdClass();

// 1. configuration where Jupiter and Moon are kendra apart (Gajakesari)
$dataSource->Gajakesari = [
    'graha' => [
        'Sy' => ['longitude'=>0],
        'Ch' => ['longitude'=>90],   // 4th from gu
        'Ma' => ['longitude'=>200],
        'Bu' => ['longitude'=>150],
        'Gu' => ['longitude'=>0],
        'Sk' => ['longitude'=>300],
        'Sa' => ['longitude'=>250],
        'Ra' => ['longitude'=>45],
        'Ke' => ['longitude'=>225],
    ],
    'lagna' => ['Lg' => ['longitude'=>0]]
];

// 2. configuration with Kemadruma (moon alone, no neighbours)
$dataSource->Kemadruma = [
    'graha' => [
        'Sy' => ['longitude'=>180],
        'Ch' => ['longitude'=>30],   // Aries, alone
        'Ma' => ['longitude'=>300],
        'Bu' => ['longitude'=>220],
        'Gu' => ['longitude'=>240],
        'Sk' => ['longitude'=>320],
        'Sa' => ['longitude'=>100],
        'Ra' => ['longitude'=>140],
        'Ke' => ['longitude'=>260],
    ],
    'lagna' => ['Lg' => ['longitude'=>45]]
];

// 3. configuration for Vipareeta Raja: lord of dusthana occupies dusthana
// set 6th house rashi = Leo (rashi 5) so lord = Sun; place Sun in a dusthana bhava
$dataSource->VipareetaRaja = [
    'graha' => [
        'Sy' => ['longitude'=>150],
        'Ch' => ['longitude'=>50],
        'Ma' => ['longitude'=>100],
        'Bu' => ['longitude'=>200],
        'Gu' => ['longitude'=>250],
        'Sk' => ['longitude'=>300],
        'Sa' => ['longitude'=>350],
        'Ra' => ['longitude'=>10],
        'Ke' => ['longitude'=>60],
    ],
    'lagna' => ['Lg' => ['longitude'=>0]]
];

// Configuration where 9th and 10th lords (Jupiter and Saturn) are conjunct
// somewhere in the chart. Lagna at 0 ensures house9 = Sagittarius (lord Ju),
// house10 = Capricorn (lord Sa). We place both Ju and Sa at same longitude.
$dataSource->DharmaKarmadhipati = [
    'graha' => [
        'Sy' => ['longitude'=>10],
        'Ch' => ['longitude'=>40],
        'Ma' => ['longitude'=>70],
        'Bu' => ['longitude'=>100], // Jupiter
        'Gu' => ['longitude'=>130],
        'Sk' => ['longitude'=>160],
        'Sa' => ['longitude'=>100], // Saturn shares with Jupiter -> conjunction
        'Ra' => ['longitude'=>190],
        'Ke' => ['longitude'=>220],
    ],
    'lagna' => ['Lg' => ['longitude'=>0]]
];

// Configuration for Neecha-Bhang Raja: Sun debilitated in Libra (rashi 7), lord
// of Libra is Venus; place Venus in kendra (lagna) by giving it longitude
// corresponding to Aries.
$dataSource->NeechaBhangRaja = [
    'graha' => [
        'Sy' => ['longitude'=>210], // Libra -> debilitated Sun
        'Ch' => ['longitude'=>40],
        'Ma' => ['longitude'=>70],
        'Bu' => ['longitude'=>100],
        'Gu' => ['longitude'=>130],
        'Sk' => ['longitude'=>160],
        'Sa' => ['longitude'=>190],
        'Ra' => ['longitude'=>220],
        'Ke' => ['longitude'=>250],
        'Ve' => ['longitude'=>10], // Aries, kendra from lagna
    ],
    'lagna' => ['Lg' => ['longitude'=>0]]
];
