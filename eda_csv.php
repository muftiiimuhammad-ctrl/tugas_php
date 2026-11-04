<?php
/**
 * Pengambilan & Eksplorasi Awal Dataset (EDA Ekstraksi CSV)
 * Membaca dataset_mahasiswa.csv dengan fopen() dan fgetcsv().
 */

$namaFile = __DIR__ . '/dataset_mahasiswa.csv';

// ---------------------------------------------------------------
// Fungsi bantu
// ---------------------------------------------------------------

// Tentukan tipe data dari satu nilai (fgetcsv selalu mengembalikan string)
function tipeNilai(string $nilai): string
{
    $nilai = trim($nilai);
    if ($nilai === '') {
        return 'kosong';
    }
    if (is_numeric($nilai)) {
        // integer jika tidak ada titik/e, selain itu float
        return preg_match('/^[+-]?\d+$/', $nilai) ? 'integer' : 'float';
    }
    return 'string';
}

// Tentukan tipe kolom dari seluruh nilainya
function tipeKolom(array $nilaiKolom): string
{
    $tipe = [];
    foreach ($nilaiKolom as $n) {
        $t = tipeNilai($n);
        if ($t !== 'kosong') {
            $tipe[$t] = true;
        }
    }
    $tipe = array_keys($tipe);

    if (count($tipe) === 0) {
        return 'kosong';
    }
    if (count($tipe) === 1) {
        return $tipe[0];
    }
    // campuran integer + float dianggap float
    if (!in_array('string', $tipe, true)) {
        return 'float';
    }
    return 'campuran (' . implode(', ', $tipe) . ')';
}

// ---------------------------------------------------------------
// Baca file CSV
// ---------------------------------------------------------------
if (!file_exists($namaFile)) {
    die("File tidak ditemukan: $namaFile\n");
}

$handle = fopen($namaFile, 'r');
if ($handle === false) {
    die("Gagal membuka file.\n");
}

// Baris pertama = header (nama kolom)
$header = fgetcsv($handle);
if ($header === false) {
    die("File kosong.\n");
}
// Buang BOM UTF-8 jika ada (sering muncul dari Excel)
$header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
$header = array_map('trim', $header);

// Baris berikutnya = data
$data = [];
while (($baris = fgetcsv($handle)) !== false) {
    // lewati baris kosong
    if (count($baris) === 1 && trim((string) $baris[0]) === '') {
        continue;
    }
    // samakan jumlah kolom dengan header
    $baris = array_pad(array_slice($baris, 0, count($header)), count($header), '');
    $data[] = array_combine($header, $baris);
}
fclose($handle);

// ---------------------------------------------------------------
// Tampilkan hasil EDA
// ---------------------------------------------------------------
$cli = (PHP_SAPI === 'cli');
if (!$cli) {
    echo '<pre>';
}

$garis = str_repeat('=', 60) . PHP_EOL;

echo $garis;
echo "EDA DATASET: " . basename($namaFile) . PHP_EOL;
echo $garis . PHP_EOL;

// 1. Jumlah total baris/rekaman (tanpa header)
echo "1. Jumlah total baris/rekaman data: " . count($data) . PHP_EOL . PHP_EOL;

// 2. Daftar nama kolom
echo "2. Daftar nama kolom (" . count($header) . " kolom):" . PHP_EOL;
foreach ($header as $i => $kolom) {
    echo "   " . ($i + 1) . ". $kolom" . PHP_EOL;
}
echo PHP_EOL;

// 3. Pratinjau 5 baris pertama (head)
echo "3. Pratinjau 5 baris pertama (head):" . PHP_EOL;
$lebar = [];
foreach ($header as $kolom) {
    $lebar[$kolom] = max(
        strlen($kolom),
        ...array_map(fn($r) => strlen($r[$kolom]), array_slice($data, 0, 5)) ?: [0]
    );
}
$cetakBaris = function (array $r) use ($header, $lebar) {
    $out = [];
    foreach ($header as $kolom) {
        $out[] = str_pad($r[$kolom], $lebar[$kolom]);
    }
    echo "   | " . implode(' | ', $out) . " |" . PHP_EOL;
};
$cetakBaris(array_combine($header, $header));
echo "   |-" . implode('-|-', array_map(fn($k) => str_repeat('-', $lebar[$k]), $header)) . "-|" . PHP_EOL;
foreach (array_slice($data, 0, 5) as $r) {
    $cetakBaris($r);
}
echo PHP_EOL;

// 4. Pengecekan tipe data tiap kolom
echo "4. Tipe data pada masing-masing kolom:" . PHP_EOL;
foreach ($header as $kolom) {
    $nilaiKolom = array_column($data, $kolom);
    $kosong = count(array_filter($nilaiKolom, fn($n) => trim($n) === ''));
    echo sprintf("   %-14s : %-10s (nilai kosong: %d)", $kolom, tipeKolom($nilaiKolom), $kosong) . PHP_EOL;
}
echo PHP_EOL . $garis;

if (!$cli) {
    echo '</pre>';
}
