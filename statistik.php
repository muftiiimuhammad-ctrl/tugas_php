<?php
/**
 * Kalkulator Statistik Deskriptif & Visualisasi Karakter ASCII
 * - Mean, Median, Modus
 * - Standar Deviasi
 * - ASCII Bar Chart frekuensi per rentang nilai
 *
 * Data bisa diberikan lewat:
 *   CLI     : php statistik.php 80 75 90 65 ...
 *   Browser : statistik.php?data=80,75,90,65
 * Jika tidak ada input, dipakai data contoh di bawah.
 */

// ---------------------------------------------------------------
// Ambil input
// ---------------------------------------------------------------
$dataContoh = [78, 85, 92, 67, 74, 88, 95, 61, 83, 79,
               90, 72, 85, 68, 99, 81, 76, 85, 64, 93];

if (PHP_SAPI === 'cli' && $argc > 1) {
    $input = array_slice($argv, 1);
} elseif (isset($_GET['data'])) {
    $input = explode(',', $_GET['data']);
} else {
    $input = $dataContoh;
}

// Buang nilai yang bukan angka
$data = array_values(array_map('floatval', array_filter($input, 'is_numeric')));

// ---------------------------------------------------------------
// Fungsi statistik
// ---------------------------------------------------------------
function mean(array $d): float
{
    return array_sum($d) / count($d);
}

function median(array $d): float
{
    sort($d);
    $n = count($d);
    $tengah = intdiv($n, 2);
    return ($n % 2 === 0) ? ($d[$tengah - 1] + $d[$tengah]) / 2 : $d[$tengah];
}

// Modus bisa lebih dari satu; kembalikan [] jika semua nilai unik
function modus(array $d): array
{
    $frek = array_count_values(array_map('strval', $d));
    $maks = max($frek);
    if ($maks === 1) {
        return [];
    }
    $hasil = [];
    foreach ($frek as $nilai => $f) {
        if ($f === $maks) {
            $hasil[] = (float) $nilai;
        }
    }
    sort($hasil);
    return $hasil;
}

// Standar deviasi: $sampel = true -> bagi (n-1), false -> bagi n
function standarDeviasi(array $d, bool $sampel = false): float
{
    $n = count($d);
    if ($sampel && $n < 2) {
        return 0.0;
    }
    $m = mean($d);
    $jumlahKuadrat = array_sum(array_map(fn($x) => ($x - $m) ** 2, $d));
    return sqrt($jumlahKuadrat / ($sampel ? $n - 1 : $n));
}

// Hitung frekuensi per rentang
function frekuensiRentang(array $d, array $rentang): array
{
    $hasil = [];
    foreach ($rentang as $label => [$bawah, $atas]) {
        $hasil[$label] = count(array_filter($d, fn($x) => $x >= $bawah && $x < $atas));
    }
    return $hasil;
}

// Cetak ASCII bar chart (1 karakter # = 1 data)
function barChart(array $frekuensi, string $karakter = '#'): void
{
    foreach ($frekuensi as $label => $f) {
        echo sprintf("  %-7s | %-20s %d", $label, str_repeat($karakter, $f), $f) . PHP_EOL;
    }
}

function fmt(float $x): string
{
    return rtrim(rtrim(number_format($x, 2, '.', ''), '0'), '.');
}

// ---------------------------------------------------------------
// Eksekusi
// ---------------------------------------------------------------
$cli = (PHP_SAPI === 'cli');
if (!$cli) echo '<pre>';

if (count($data) === 0) {
    echo "Tidak ada data numerik yang valid.\n";
    if (!$cli) echo '</pre>';
    exit;
}

$garis = str_repeat('=', 50) . PHP_EOL;
$mod   = modus($data);

echo $garis . "KALKULATOR STATISTIK DESKRIPTIF" . PHP_EOL . $garis;
echo "Data (" . count($data) . " nilai): " . implode(', ', array_map('fmt', $data)) . PHP_EOL . PHP_EOL;

echo "Mean                    : " . fmt(mean($data)) . PHP_EOL;
echo "Median                  : " . fmt(median($data)) . PHP_EOL;
echo "Modus                   : " . ($mod ? implode(', ', array_map('fmt', $mod)) : 'tidak ada (semua nilai unik)') . PHP_EOL;
echo "Standar deviasi (populasi): " . fmt(standarDeviasi($data, false)) . PHP_EOL;
echo "Standar deviasi (sampel)  : " . fmt(standarDeviasi($data, true)) . PHP_EOL . PHP_EOL;

// Rentang: batas atas eksklusif, jadi 90-100 memakai batas 101 agar 100 ikut terhitung
$rentang = [
    '<60'    => [PHP_INT_MIN, 60],
    '60-69'  => [60, 70],
    '70-79'  => [70, 80],
    '80-89'  => [80, 90],
    '90-100' => [90, 101],
];

echo "Frekuensi per rentang nilai (# = 1 data):" . PHP_EOL;
barChart(frekuensiRentang($data, $rentang));
echo $garis;

if (!$cli) echo '</pre>';
