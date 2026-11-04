<?php
/**
 * Pembersihan Data, Penanganan Missing Value, & Transformasi
 * 1. Hitung rata-rata dari nilai yang valid
 * 2. Imputasi nilai kosong (null / "") dengan rata-rata
 * 3. Normalisasi Min-Max ke rentang 0 sampai 1
 */

$data = [80, null, 75, "", 90, null, 65, 85, 100];

// Nilai dianggap valid jika bukan null, bukan string kosong, dan numerik.
// (Tidak pakai empty() karena angka 0 adalah nilai valid.)
function nilaiValid($x): bool
{
    return $x !== null && $x !== '' && is_numeric($x);
}

// 1. Rata-rata dari nilai valid saja
function hitungMean(array $data): float
{
    $valid = array_filter($data, 'nilaiValid');
    if (count($valid) === 0) {
        throw new RuntimeException('Tidak ada nilai valid untuk dihitung.');
    }
    return array_sum($valid) / count($valid);
}

// 2. Imputasi: ganti elemen kosong dengan mean
function imputasi(array $data, float $mean): array
{
    return array_map(fn($x) => nilaiValid($x) ? (float) $x : $mean, $data);
}

// 3. Normalisasi Min-Max: x' = (x - min) / (max - min)
function minMax(array $data): array
{
    $min = min($data);
    $max = max($data);
    $rentang = $max - $min;

    if ($rentang == 0) {
        return array_map(fn($x) => 0.0, $data); // semua nilai sama
    }
    return array_map(fn($x) => ($x - $min) / $rentang, $data);
}

// Fungsi bantu untuk menampilkan array
function tampil(array $arr, int $desimal = 2): string
{
    return '[' . implode(', ', array_map(function ($x) use ($desimal) {
        if ($x === null)  return 'null';
        if ($x === '')    return '""';
        return is_numeric($x) ? number_format((float) $x, $desimal, '.', '') : $x;
    }, $arr)) . ']';
}

// ---------------------------------------------------------------
// Eksekusi
// ---------------------------------------------------------------
$cli = (PHP_SAPI === 'cli');
if (!$cli) echo '<pre>';

$mean       = hitungMean($data);
$terimputasi = imputasi($data, $mean);
$ternormal   = minMax($terimputasi);

echo "Data awal            : " . tampil($data) . PHP_EOL;
echo "Jumlah nilai valid   : " . count(array_filter($data, 'nilaiValid')) . " dari " . count($data) . PHP_EOL;
echo PHP_EOL;
echo "1. Rata-rata (mean)  : " . number_format($mean, 2, '.', '') . PHP_EOL;
echo PHP_EOL;
echo "2. Setelah imputasi  : " . tampil($terimputasi) . PHP_EOL;
echo PHP_EOL;
echo "3. Setelah Min-Max   : " . tampil($ternormal, 4) . PHP_EOL;
echo "   (min = " . min($terimputasi) . ", max = " . max($terimputasi) . ")" . PHP_EOL;

if (!$cli) echo '</pre>';
