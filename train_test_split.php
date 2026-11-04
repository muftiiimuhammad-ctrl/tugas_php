<?php
/**
 * Pembagian Data Latih dan Data Uji (Train-Test Split Algorithm)
 * Dataset array 2 dimensi (50 sampel) dibagi 80% train dan 20% test.
 * Pengacakan dilakukan pada INDEKS, sehingga setiap baris masuk ke
 * tepat satu subset (tidak ada duplikasi).
 */

/**
 * @param array $data       dataset 2 dimensi (array of rows)
 * @param float $test_size  proporsi data uji (0 < test_size < 1)
 * @param bool  $shuffle    acak indeks sebelum dibagi
 * @return array            ['train' => [...], 'test' => [...]]
 */
function train_test_split(array $data, float $test_size = 0.2, bool $shuffle = true): array
{
    if ($test_size <= 0 || $test_size >= 1) {
        throw new InvalidArgumentException('test_size harus di antara 0 dan 1.');
    }

    $data = array_values($data);          // pastikan indeks 0..n-1
    $n = count($data);
    $jumlahTest = (int) round($n * $test_size);

    // 1. Buat daftar indeks 0..n-1
    $indeks = range(0, $n - 1);

    // 2. Acak indeks (bukan datanya)
    if ($shuffle) {
        shuffle($indeks);
    }

    // 3. Potong indeks: sebagian untuk test, sisanya untuk train
    $indeksTest  = array_slice($indeks, 0, $jumlahTest);
    $indeksTrain = array_slice($indeks, $jumlahTest);

    // 4. Ambil baris data berdasarkan indeks
    $train = array_map(fn($i) => $data[$i], $indeksTrain);
    $test  = array_map(fn($i) => $data[$i], $indeksTest);

    return ['train' => $train, 'test' => $test];
}

// ---------------------------------------------------------------
// Dataset contoh: 50 baris, tiap baris [id, fitur, label]
// ---------------------------------------------------------------
$dataset = [];
for ($i = 1; $i <= 50; $i++) {
    $dataset[] = [
        'id'    => $i,
        'fitur' => round(mt_rand(500, 1000) / 10, 1),
        'label' => ($i % 2 === 0) ? 'A' : 'B',
    ];
}

// ---------------------------------------------------------------
// Eksekusi
// ---------------------------------------------------------------
$cli = (PHP_SAPI === 'cli');
if (!$cli) echo '<pre>';

$hasil = train_test_split($dataset, 0.2, true);
$train = $hasil['train'];
$test  = $hasil['test'];

$total = count($dataset);
$garis = str_repeat('=', 50) . PHP_EOL;

echo $garis . "TRAIN-TEST SPLIT" . PHP_EOL . $garis;
echo "Total data       : $total baris" . PHP_EOL;
echo "Data latih (train): " . count($train) . " baris (" . round(count($train) / $total * 100) . "%)" . PHP_EOL;
echo "Data uji (test)   : " . count($test)  . " baris (" . round(count($test)  / $total * 100) . "%)" . PHP_EOL . PHP_EOL;

// Verifikasi: tidak ada duplikasi antar subset & tidak ada data hilang
$idTrain = array_column($train, 'id');
$idTest  = array_column($test, 'id');
$irisan  = array_intersect($idTrain, $idTest);
$semua   = array_merge($idTrain, $idTest);

echo "Verifikasi:" . PHP_EOL;
echo "  Irisan train & test    : " . count($irisan) . " baris " . (count($irisan) === 0 ? "(OK, tidak ada duplikasi)" : "(GAGAL!)") . PHP_EOL;
echo "  Train + test = total   : " . (count($semua) === $total && count(array_unique($semua)) === $total ? "OK" : "GAGAL") . PHP_EOL . PHP_EOL;

sort($idTest);
echo "ID data uji (terurut): " . implode(', ', $idTest) . PHP_EOL;
echo $garis;

if (!$cli) echo '</pre>';
