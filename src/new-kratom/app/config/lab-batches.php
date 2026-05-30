<?php

/*
|--------------------------------------------------------------------------
| Lab Batches — VŠCHT Praha COA data
|--------------------------------------------------------------------------
| Per-batch lab test data parsed from accredited laboratory protocols.
| Keyed by batch ID (lot number printed on packaging / encoded in QR).
|
| Status: V = vyhovuje (pass), X = nehodnoceno (not evaluated),
|         N = nevyhovuje (fail), Vn = pass within uncertainty.
*/

return [

    'L08202504' => [
        'product_name' => 'Rurut Kratom prášek',
        'strains'      => ['Bílý Slon', 'Zelený Rurut Nano'],
        'lot'          => 'L08202504',
        'package'      => 'Doypack ZIP, PE sáček',
        'mass'         => '400 g',
        'received_at'  => '2026-01-16',
        'issued_at'    => '2026-01-30',
        'protocols'    => [
            ['file' => 'lab-tests.pdf',   'no' => 'ML 63/26',   'label' => 'Aktivní látky + těžké kovy', 'date' => '2026-01-22'],
            ['file' => 'lab-tests-2.pdf', 'no' => '50P/2026',   'label' => 'Mikrobiologie',             'date' => '2026-01-27'],
            ['file' => 'lab-tests-3.pdf', 'no' => 'ML 131/26',  'label' => 'Mykotoxiny + PAU',          'date' => '2026-01-30'],
        ],
        'tests' => [
            'active' => [
                ['name' => 'Mitragynin',          'value' => 1.5,    'uncertainty' => 0.2,    'unit' => '% hm.', 'limit' => 2.5, 'status' => 'V'],
                ['name' => '7-hydroxymitragynin', 'value' => 0.0067, 'uncertainty' => 0.0010, 'unit' => '% hm.', 'limit' => 0.1, 'status' => 'V'],
            ],
            'metals' => [
                ['name' => 'Rtuť',    'symbol' => 'Hg', 'value' => 0.011, 'uncertainty' => 0.001, 'unit' => 'mg/kg', 'limit' => 0.10, 'status' => 'V'],
                ['name' => 'Olovo',   'symbol' => 'Pb', 'value' => 0.64,  'uncertainty' => 0.08,  'unit' => 'mg/kg', 'limit' => 3,    'status' => 'V'],
                ['name' => 'Kadmium', 'symbol' => 'Cd', 'value' => 0.02,  'uncertainty' => 0.003, 'unit' => 'mg/kg', 'limit' => 1,    'status' => 'V'],
                ['name' => 'Arsen',   'symbol' => 'As', 'value' => 0.13,  'uncertainty' => 0.02,  'unit' => 'mg/kg', 'limit' => 0.50, 'status' => 'V'],
                ['name' => 'Nikl',    'symbol' => 'Ni', 'value' => 1.28,  'uncertainty' => 0.19,  'unit' => 'mg/kg', 'limit' => null, 'status' => 'X'],
            ],
            'mycotoxins' => [
                ['name' => 'Aflatoxin B1',          'value' => 2.0, 'below_loq' => true, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'V'],
                ['name' => 'Aflatoxin B2',          'value' => 2.0, 'below_loq' => true, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'V'],
                ['name' => 'Aflatoxin G1',          'value' => 2.0, 'below_loq' => true, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'V'],
                ['name' => 'Aflatoxin G2',          'value' => 5.0, 'below_loq' => true, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'V'],
                ['name' => 'Aflatoxiny (suma B1+B2+G1+G2)', 'value' => 5.0, 'below_loq' => true, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'V'],
            ],
            'pah' => [
                ['name' => 'Suma 4 PAU (BaP, BaA, BbF, Chrysen)', 'value' => 2.1, 'uncertainty' => 1.1, 'unit' => 'µg/kg', 'limit' => 50, 'status' => 'V'],
                ['name' => 'Benzo[a]pyren',     'value' => 0.44, 'uncertainty' => 0.22, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'X'],
                ['name' => 'Benz[a]anthracen',  'value' => 0.62, 'uncertainty' => 0.31, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'X'],
                ['name' => 'Chrysen',           'value' => 0.62, 'uncertainty' => 0.31, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'X'],
            ],
            'microbiology' => [
                ['name' => 'Celkový počet mikroorganismů (CPM)', 'value' => 1_000,   'unit' => 'cfu/g',   'limit' => null, 'status' => 'V'],
                ['name' => 'Kvasinky',                           'value' => 50,      'below_loq' => true, 'unit' => 'cfu/g',   'limit' => null, 'status' => 'V'],
                ['name' => 'Plísně',                             'value' => 50,      'below_loq' => true, 'unit' => 'cfu/g',   'limit' => null, 'status' => 'V'],
                ['name' => 'Escherichia coli',                   'value' => 10,      'below_loq' => true, 'unit' => 'cfu/g',   'limit' => null, 'status' => 'V'],
                ['name' => 'Salmonella spp.',                    'value' => 'negativní', 'unit' => 'v 25 g', 'limit' => 'negativní', 'status' => 'V'],
            ],
        ],
    ],

    'L10202501' => [
        'product_name' => 'SW Kratom prášek',
        'strains'      => ['Super White'],
        'lot'          => 'L10202501',
        'package'      => 'Doypack ZIP, PE sáček',
        'mass'         => '400 g',
        'received_at'  => '2026-01-28',
        'issued_at'    => '2026-02-04',
        'protocols'    => [
            ['file' => 'lab-tests-4.pdf', 'no' => 'ML 138/26', 'label' => 'Plný panel (kovy, aktivní látky, PAU, mykotoxiny)', 'date' => '2026-02-04'],
            ['file' => 'lab-tests-5.pdf', 'no' => '81P/2026',  'label' => 'Mikrobiologie',                                   'date' => '2026-02-03'],
        ],
        'tests' => [
            'active' => [
                ['name' => 'Mitragynin',          'value' => 1.6,   'uncertainty' => 0.2,   'unit' => '% hm.', 'limit' => 2.5, 'status' => 'V'],
                ['name' => '7-hydroxymitragynin', 'value' => 0.016, 'uncertainty' => 0.002, 'unit' => '% hm.', 'limit' => 0.1, 'status' => 'V'],
            ],
            'metals' => [
                ['name' => 'Rtuť',    'symbol' => 'Hg', 'value' => 0.012, 'uncertainty' => 0.001, 'unit' => 'mg/kg', 'limit' => 0.10, 'status' => 'V'],
                ['name' => 'Olovo',   'symbol' => 'Pb', 'value' => 1.13,  'uncertainty' => 0.14,  'unit' => 'mg/kg', 'limit' => 3,    'status' => 'V'],
                ['name' => 'Kadmium', 'symbol' => 'Cd', 'value' => 0.024, 'uncertainty' => 0.004, 'unit' => 'mg/kg', 'limit' => 1,    'status' => 'V'],
                ['name' => 'Arsen',   'symbol' => 'As', 'value' => 0.11,  'uncertainty' => 0.01,  'unit' => 'mg/kg', 'limit' => 0.50, 'status' => 'V'],
                ['name' => 'Nikl',    'symbol' => 'Ni', 'value' => 0.98,  'uncertainty' => 0.15,  'unit' => 'mg/kg', 'limit' => null, 'status' => 'X'],
            ],
            'mycotoxins' => [
                ['name' => 'Aflatoxin B1',          'value' => 2.0, 'below_loq' => true, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'V'],
                ['name' => 'Aflatoxin B2',          'value' => 2.0, 'below_loq' => true, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'V'],
                ['name' => 'Aflatoxin G1',          'value' => 2.0, 'below_loq' => true, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'V'],
                ['name' => 'Aflatoxin G2',          'value' => 5.0, 'below_loq' => true, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'V'],
                ['name' => 'Aflatoxiny (suma B1+B2+G1+G2)', 'value' => 5.0, 'below_loq' => true, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'V'],
            ],
            'pah' => [
                ['name' => 'Suma 4 PAU (BaP, BaA, BbF, Chrysen)', 'value' => 4.5, 'uncertainty' => 2.3, 'unit' => 'µg/kg', 'limit' => 50, 'status' => 'V'],
                ['name' => 'Benzo[a]pyren',     'value' => 0.48, 'uncertainty' => 0.24, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'X'],
                ['name' => 'Benz[a]anthracen',  'value' => 1.1,  'uncertainty' => 0.55, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'X'],
                ['name' => 'Chrysen',           'value' => 2.1,  'uncertainty' => 1.1,  'unit' => 'µg/kg', 'limit' => null, 'status' => 'X'],
            ],
            'microbiology' => [
                ['name' => 'Celkový počet mikroorganismů (CPM)', 'value' => 72_000, 'unit' => 'cfu/g', 'limit' => null, 'status' => 'V'],
                ['name' => 'Kvasinky',                           'value' => 50,     'below_loq' => true, 'unit' => 'cfu/g', 'limit' => null, 'status' => 'V'],
                ['name' => 'Plísně',                             'value' => 6_000,  'unit' => 'cfu/g', 'limit' => null, 'status' => 'V'],
                ['name' => 'Escherichia coli',                   'value' => 10,     'below_loq' => true, 'unit' => 'cfu/g', 'limit' => null, 'status' => 'V'],
                ['name' => 'Salmonella spp.',                    'value' => 'negativní', 'unit' => 'v 25 g', 'limit' => 'negativní', 'status' => 'V'],
            ],
        ],
    ],

    'L08202501' => [
        'product_name' => 'Kratom Red',
        'strains'      => ['Red Vein'],
        'lot'          => 'L08202501',
        'package'      => 'Doypack ZIP, PE sáček',
        'mass'         => '400 g',
        'received_at'  => '2026-01-23',
        'issued_at'    => '2026-02-05',
        'protocols'    => [
            ['file' => 'lab-tests-6.pdf', 'no' => 'ML 106/26', 'label' => 'Plný panel (kovy, aktivní látky, PAU, mykotoxiny)', 'date' => '2026-02-05'],
            ['file' => 'lab-tests-7.pdf', 'no' => '74P/2026',  'label' => 'Mikrobiologie',                                   'date' => '2026-02-03'],
        ],
        'tests' => [
            'active' => [
                ['name' => 'Mitragynin',          'value' => 0.60,   'uncertainty' => 0.09,   'unit' => '% hm.', 'limit' => 2.5, 'status' => 'V'],
                ['name' => '7-hydroxymitragynin', 'value' => 0.0081, 'uncertainty' => 0.0012, 'unit' => '% hm.', 'limit' => 0.1, 'status' => 'V'],
            ],
            'metals' => [
                ['name' => 'Rtuť',    'symbol' => 'Hg', 'value' => 0.024, 'uncertainty' => 0.002, 'unit' => 'mg/kg', 'limit' => 0.10, 'status' => 'V'],
                ['name' => 'Olovo',   'symbol' => 'Pb', 'value' => 0.22,  'uncertainty' => 0.04,  'unit' => 'mg/kg', 'limit' => 3,    'status' => 'V'],
                ['name' => 'Kadmium', 'symbol' => 'Cd', 'value' => 0.025, 'uncertainty' => 0.004, 'unit' => 'mg/kg', 'limit' => 1,    'status' => 'V'],
                ['name' => 'Arsen',   'symbol' => 'As', 'value' => 0.35,  'uncertainty' => 0.04,  'unit' => 'mg/kg', 'limit' => 0.50, 'status' => 'V'],
                ['name' => 'Nikl',    'symbol' => 'Ni', 'value' => 1.27,  'uncertainty' => 0.19,  'unit' => 'mg/kg', 'limit' => null, 'status' => 'X'],
            ],
            'mycotoxins' => [
                ['name' => 'Aflatoxin B1',          'value' => 2.0, 'below_loq' => true, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'V'],
                ['name' => 'Aflatoxin B2',          'value' => 2.0, 'below_loq' => true, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'V'],
                ['name' => 'Aflatoxin G1',          'value' => 2.0, 'below_loq' => true, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'V'],
                ['name' => 'Aflatoxin G2',          'value' => 5.0, 'below_loq' => true, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'V'],
                ['name' => 'Aflatoxiny (suma B1+B2+G1+G2)', 'value' => 5.0, 'below_loq' => true, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'V'],
            ],
            'pah' => [
                ['name' => 'Suma 4 PAU (BaP, BaA, BbF, Chrysen)', 'value' => 6.4, 'uncertainty' => 2.9, 'unit' => 'µg/kg', 'limit' => 50, 'status' => 'V'],
                ['name' => 'Benzo[a]pyren',     'value' => 0.88, 'uncertainty' => 0.44, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'X'],
                ['name' => 'Benz[a]anthracen',  'value' => 1.4,  'uncertainty' => 0.70, 'unit' => 'µg/kg', 'limit' => null, 'status' => 'X'],
                ['name' => 'Chrysen',           'value' => 2.3,  'uncertainty' => 1.2,  'unit' => 'µg/kg', 'limit' => null, 'status' => 'X'],
            ],
            'microbiology' => [
                ['name' => 'Celkový počet mikroorganismů (CPM)', 'value' => 96_000, 'unit' => 'cfu/g', 'limit' => null, 'status' => 'V'],
                ['name' => 'Kvasinky',                           'value' => 50,     'below_loq' => true, 'unit' => 'cfu/g', 'limit' => null, 'status' => 'V'],
                ['name' => 'Plísně',                             'value' => 630,    'unit' => 'cfu/g', 'limit' => null, 'status' => 'V'],
                ['name' => 'Escherichia coli',                   'value' => 10,     'below_loq' => true, 'unit' => 'cfu/g', 'limit' => null, 'status' => 'V'],
                ['name' => 'Salmonella spp.',                    'value' => 'negativní', 'unit' => 'v 25 g', 'limit' => 'negativní', 'status' => 'V'],
            ],
        ],
    ],

];
