<?php
/**
 * Simulasi Siklus Hidup Data Sains (Data Pipeline Sederhana)
 * PHP native, berbasis array asosiatif.
 *
 * Alur:
 * Data mentah -> Validasi -> Filter valid -> Proses -> Siap analisis -> Laporan
 */

// ---------------------------------------------------------------
// 1. DATA MENTAH (sengaja dicampur: ada yang valid dan yang rusak)
// ---------------------------------------------------------------
function ambilDataMentah(): array
{
    return [
        ['id' => 1, 'produk' => 'Buku Tulis',  'harga' => 5000,   'qty' => 10],
        ['id' => 2, 'produk' => 'Pulpen',      'harga' => 3000,   'qty' => 5],
        ['id' => 3, 'produk' => '',            'harga' => 15000,  'qty' => 2],      // produk kosong
        ['id' => 4, 'produk' => 'Penggaris',   'harga' => -2000,  'qty' => 3],      // harga negatif
        ['id' => 5, 'produk' => 'Tas',         'harga' => 150000, 'qty' => 'dua'],  // qty bukan angka
        ['id' => 6, 'produk' => 'Botol Minum', 'harga' => 25000,  'qty' => 4],
        ['id' => 7, 'produk' => 'Laptop',      'harga' => 7500000],                 // key qty hilang
        ['id' => 8, 'produk' => 'Mouse',       'harga' => 80000,  'qty' => 0],      // qty nol
        ['id' => 9, 'produk' => 'Headset',     'harga' => 120000, 'qty' => 1],
    ];
}

// ---------------------------------------------------------------
// 2. TAHAP VALIDASI
// Cek struktur baris: key wajib, tipe data, dan nilai masuk akal.
// Mengembalikan daftar pesan error (kosong = valid).
// ---------------------------------------------------------------
function validasiBaris(array $baris): array
{
    $error = [];
    $wajib = ['id', 'produk', 'harga', 'qty'];

    foreach ($wajib as $key) {
        if (!array_key_exists($key, $baris)) {
            $error[] = "field '$key' tidak ada";
        }
    }
    if ($error) {
        return $error; // struktur rusak, tidak perlu cek nilai
    }

    if (!is_int($baris['id']) || $baris['id'] <= 0) {
        $error[] = 'id harus integer > 0';
    }
    if (!is_string($baris['produk']) || trim($baris['produk']) === '') {
        $error[] = 'produk tidak boleh kosong';
    }
    if (!is_numeric($baris['harga']) || $baris['harga'] <= 0) {
        $error[] = 'harga harus angka > 0';
    }
    if (!is_numeric($baris['qty']) || $baris['qty'] <= 0) {
        $error[] = 'qty harus angka > 0';
    }

    return $error;
}

// Memberi label status 'valid' / 'invalid' ke setiap baris
function tahapValidasi(array $data): array
{
    $hasil = [];
    foreach ($data as $i => $baris) {
        $error = validasiBaris($baris);
        $hasil[] = [
            'baris_ke' => $i + 1,
            'data'     => $baris,
            'status'   => $error ? 'invalid' : 'valid',
            'error'    => $error,
        ];
    }
    return $hasil;
}

// ---------------------------------------------------------------
// 3. TAHAP FILTER: pisahkan yang valid dari yang invalid
// ---------------------------------------------------------------
function tahapFilter(array $dataTervalidasi): array
{
    $valid   = array_values(array_filter($dataTervalidasi, fn($r) => $r['status'] === 'valid'));
    $invalid = array_values(array_filter($dataTervalidasi, fn($r) => $r['status'] === 'invalid'));

    return ['valid' => $valid, 'invalid' => $invalid];
}

// ---------------------------------------------------------------
// 4. TAHAP PROSES: transformasi data valid (hitung total transaksi)
// ---------------------------------------------------------------
function tahapProses(array $dataValid): array
{
    return array_map(function ($r) {
        $r['data']['harga'] = (float) $r['data']['harga'];
        $r['data']['qty']   = (int) $r['data']['qty'];
        $r['data']['total'] = $r['data']['harga'] * $r['data']['qty'];
        $r['status']        = 'diproses';
        return $r;
    }, $dataValid);
}

// ---------------------------------------------------------------
// 5. TAHAP SIAP ANALISIS: data final + ringkasan
// ---------------------------------------------------------------
function tahapSiapAnalisis(array $dataDiproses): array
{
    $data = array_map(function ($r) {
        $r['status'] = 'siap analisis';
        return $r;
    }, $dataDiproses);

    $totalOmzet = array_sum(array_map(fn($r) => $r['data']['total'], $data));

    return [
        'data'        => $data,
        'total_omzet' => $totalOmzet,
        'jumlah'      => count($data),
        'rata_rata'   => count($data) ? $totalOmzet / count($data) : 0,
    ];
}

// ---------------------------------------------------------------
// 6. PIPELINE UTAMA: rangkai semua tahap, output tahap = input berikutnya
// ---------------------------------------------------------------
function jalankanPipeline(array $dataMentah): array
{
    $tervalidasi = tahapValidasi($dataMentah);
    $terfilter   = tahapFilter($tervalidasi);
    $diproses    = tahapProses($terfilter['valid']);
    $siap        = tahapSiapAnalisis($diproses);

    return [
        'jumlah_mentah' => count($dataMentah),
        'invalid'       => $terfilter['invalid'],
        'diproses'      => $diproses,
        'siap_analisis' => $siap,
    ];
}

// ---------------------------------------------------------------
// 7. LAPORAN STATUS TAHAPAN PIPELINE
// ---------------------------------------------------------------
function rupiah(float $angka): string
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function cetakLaporan(array $hasil): void
{
    $garis = str_repeat('=', 60) . PHP_EOL;

    echo $garis;
    echo "LAPORAN STATUS TAHAPAN PIPELINE" . PHP_EOL;
    echo $garis;

    // Ringkasan per tahap
    $jumlahValid   = count($hasil['diproses']);
    $jumlahInvalid = count($hasil['invalid']);

    echo "Ringkasan:" . PHP_EOL;
    echo "  Data mentah masuk : {$hasil['jumlah_mentah']} baris" . PHP_EOL;
    echo "  Status valid      : $jumlahValid baris" . PHP_EOL;
    echo "  Status invalid    : $jumlahInvalid baris (dibuang)" . PHP_EOL;
    echo "  Status diproses   : $jumlahValid baris" . PHP_EOL;
    echo "  Siap analisis     : {$hasil['siap_analisis']['jumlah']} baris" . PHP_EOL;
    echo PHP_EOL;

    // Detail data invalid
    echo "Detail data INVALID:" . PHP_EOL;
    foreach ($hasil['invalid'] as $r) {
        echo "  Baris {$r['baris_ke']} (id: " . ($r['data']['id'] ?? '-') . ") -> "
            . implode('; ', $r['error']) . PHP_EOL;
    }
    echo PHP_EOL;

    // Detail data siap analisis
    echo "Detail data SIAP ANALISIS:" . PHP_EOL;
    foreach ($hasil['siap_analisis']['data'] as $r) {
        $d = $r['data'];
        echo sprintf(
            "  #%d %-12s %12s x %-3d = %14s  [%s]",
            $d['id'],
            $d['produk'],
            rupiah($d['harga']),
            $d['qty'],
            rupiah($d['total']),
            $r['status']
        ) . PHP_EOL;
    }
    echo PHP_EOL;

    // Ringkasan analisis
    echo "Ringkasan analisis:" . PHP_EOL;
    echo "  Total omzet          : " . rupiah($hasil['siap_analisis']['total_omzet']) . PHP_EOL;
    echo "  Rata-rata/transaksi  : " . rupiah($hasil['siap_analisis']['rata_rata']) . PHP_EOL;
    echo $garis;
}

// ---------------------------------------------------------------
// EKSEKUSI
// ---------------------------------------------------------------
// Supaya rapi di browser, bungkus dengan <pre> kalau bukan CLI
$cli = (PHP_SAPI === 'cli');
if (!$cli) {
    echo '<pre>';
}

$hasil = jalankanPipeline(ambilDataMentah());
cetakLaporan($hasil);

if (!$cli) {
    echo '</pre>';
}
