<?php
/**
 * Halaman utama: satu link untuk mengakses kelima soal.
 * Buka: index.php            -> daftar soal
 *       index.php?soal=1..5  -> hasil soal tersebut
 * Semua file soal harus berada di folder yang sama dengan index.php.
 */

$soal = [
    1 => ['file' => 'pipeline.php',         'judul' => 'Pipeline Data Sains',        'ket' => 'Validasi, filter, proses, dan laporan status transaksi penjualan.'],
    2 => ['file' => 'eda_csv.php',          'judul' => 'EDA Ekstraksi CSV',          'ket' => 'Baca dataset_mahasiswa.csv: jumlah baris, kolom, head, tipe data.'],
    3 => ['file' => 'cleaning.php',         'judul' => 'Pembersihan Data',           'ket' => 'Mean, imputasi missing value, dan normalisasi Min-Max.'],
    4 => ['file' => 'statistik.php',        'judul' => 'Kalkulator Statistik',       'ket' => 'Mean, median, modus, standar deviasi, dan ASCII bar chart.'],
    5 => ['file' => 'train_test_split.php', 'judul' => 'Train-Test Split',           'ket' => 'Bagi 50 data menjadi 80% latih dan 20% uji tanpa duplikasi.'],
];

$aktif = isset($_GET['soal']) ? (int) $_GET['soal'] : 0;
if (!isset($soal[$aktif])) {
    $aktif = 0;
}

// Jalankan skrip soal dan tangkap outputnya
$hasil = '';
$pesan = '';
if ($aktif) {
    $path = __DIR__ . '/' . $soal[$aktif]['file'];
    $dataInput = isset($_GET['data']) ? trim($_GET['data']) : '';

    if (!file_exists($path)) {
        $pesan = 'File ' . $soal[$aktif]['file'] . ' tidak ditemukan. Upload ke folder yang sama dengan index.php.';
    } elseif ($aktif === 2 && !file_exists(__DIR__ . '/dataset_mahasiswa.csv')) {
        $pesan = 'File dataset_mahasiswa.csv tidak ditemukan. Upload ke folder yang sama dengan index.php.';
    } elseif ($aktif === 4 && $dataInput !== '' && count(array_filter(explode(',', $dataInput), 'is_numeric')) === 0) {
        $pesan = 'Data tidak valid. Isi dengan angka dipisah koma, contoh: 80,75,90,65.';
    } else {
        ob_start();
        include $path;
        $hasil = ob_get_clean();
    }
}

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $aktif ? 'Soal ' . $aktif . ' - ' . h($soal[$aktif]['judul']) : 'Tugas PHP Native' ?></title>
<style>
  :root {
    --bg: #f7f8fa; --surface: #ffffff; --ink: #1c2430; --muted: #5d6b7c;
    --line: #d8dee6; --accent: #1f4fd8; --accent-soft: #e8eefc;
    --code-bg: #101722; --code-ink: #e3e9f2;
  }
  @media (prefers-color-scheme: dark) {
    :root {
      --bg: #12161d; --surface: #1a2029; --ink: #e6ebf2; --muted: #9aa7b8;
      --line: #2c3541; --accent: #7aa2ff; --accent-soft: #1f2a44;
      --code-bg: #0b0f15; --code-ink: #e3e9f2;
    }
  }
  * { box-sizing: border-box; }
  body { margin: 0; background: var(--bg); color: var(--ink);
         font: 16px/1.55 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
  a { color: inherit; }
  a:focus-visible, button:focus-visible, input:focus-visible { outline: 3px solid var(--accent); outline-offset: 2px; }
  .layout { display: grid; grid-template-columns: 280px 1fr; min-height: 100vh; }
  nav { background: var(--surface); border-right: 1px solid var(--line); padding: 24px 16px; }
  nav h1 { font-size: 18px; margin: 0 8px 4px; }
  nav p.sub { margin: 0 8px 20px; color: var(--muted); font-size: 14px; }
  nav ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 4px; }
  nav a { display: block; padding: 10px 12px; border-radius: 6px; text-decoration: none; }
  nav a:hover { background: var(--accent-soft); }
  nav a[aria-current="page"] { background: var(--accent-soft); color: var(--accent); font-weight: 600; }
  nav a small { display: block; color: var(--muted); font-weight: 400; font-size: 13px; }
  main { padding: 32px clamp(16px, 4vw, 48px); max-width: 960px; width: 100%; }
  main h2 { font-size: 26px; margin: 0 0 4px; }
  main p.lead { margin: 0 0 24px; color: var(--muted); }
  pre { background: var(--code-bg); color: var(--code-ink); padding: 20px; border-radius: 8px;
        overflow-x: auto; font: 14px/1.5 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; margin: 0; }
  .note { border-left: 4px solid var(--accent); background: var(--surface); padding: 12px 16px; margin-bottom: 20px; }
  .error { border-left-color: #c62828; }
  form.data { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
  form.data input { flex: 1 1 260px; padding: 10px 12px; border: 1px solid var(--line); border-radius: 6px;
                    background: var(--surface); color: var(--ink); font: inherit; }
  form.data button { padding: 10px 18px; border: 0; border-radius: 6px; background: var(--accent); color: #fff;
                     font: inherit; font-weight: 600; cursor: pointer; }
  .grid { display: grid; gap: 12px; }
  .grid a { background: var(--surface); border: 1px solid var(--line); border-radius: 8px; padding: 16px 18px; text-decoration: none; }
  .grid a:hover { border-color: var(--accent); }
  .grid strong { display: block; }
  .grid span { color: var(--muted); font-size: 15px; }
  .refresh { display: inline-block; margin-top: 16px; color: var(--accent); }
  @media (max-width: 760px) {
    .layout { grid-template-columns: 1fr; }
    nav { border-right: 0; border-bottom: 1px solid var(--line); }
    nav a small { display: none; }
  }
</style>
</head>
<body>
<div class="layout">
  <nav aria-label="Daftar soal">
    <h1>Tugas PHP Native</h1>
    <p class="sub">Pilih soal untuk menjalankan skripnya.</p>
    <ul>
      <?php foreach ($soal as $no => $s): ?>
        <li><a href="?soal=<?= $no ?>"<?= $no === $aktif ? ' aria-current="page"' : '' ?>>
          Soal <?= $no ?>: <?= h($s['judul']) ?><small><?= h($s['ket']) ?></small></a></li>
      <?php endforeach; ?>
    </ul>
  </nav>

  <main>
  <?php if (!$aktif): ?>
    <h2>Pilih soal</h2>
    <p class="lead">Setiap soal dijalankan langsung di halaman ini.</p>
    <div class="grid">
      <?php foreach ($soal as $no => $s): ?>
        <a href="?soal=<?= $no ?>"><strong>Soal <?= $no ?>: <?= h($s['judul']) ?></strong><span><?= h($s['ket']) ?></span></a>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <h2>Soal <?= $aktif ?>: <?= h($soal[$aktif]['judul']) ?></h2>
    <p class="lead"><?= h($soal[$aktif]['ket']) ?> File: <code><?= h($soal[$aktif]['file']) ?></code></p>

    <?php if ($aktif === 4): ?>
      <form class="data" method="get">
        <input type="hidden" name="soal" value="4">
        <input type="text" name="data" value="<?= h($dataInput) ?>" placeholder="Data angka dipisah koma, contoh: 80,75,90,65" aria-label="Data angka">
        <button type="submit">Hitung</button>
      </form>
    <?php endif; ?>

    <?php if ($pesan): ?>
      <div class="note error"><?= h($pesan) ?></div>
    <?php else: ?>
      <?= $hasil ?>
      <?php if ($aktif === 5): ?><a class="refresh" href="?soal=5">Acak ulang</a><?php endif; ?>
    <?php endif; ?>
  <?php endif; ?>
  </main>
</div>
</body>
</html>
