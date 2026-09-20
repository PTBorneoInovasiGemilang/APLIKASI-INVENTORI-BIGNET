<?php
$pageTitle = 'Riwayat Surat Jalan';
ob_start();
$version = $selectedVersion ?? null;
$versions = $versions ?? [];
$items = $version ? json_decode($version['items_json'] ?? '[]', true) : [];
$items = is_array($items) ? $items : [];
$isReceiptVersion = $version && (int)$version['version_no'] > 1;

$baseCode = trim((string)($transfer['transaction_code'] ?? ''));
if ($baseCode === '' || preg_match('/^SJ-?$/i', $baseCode)) {
  $baseCode = 'TRF-' . str_pad((string)($transfer['id'] ?? 0), 6, '0', STR_PAD_LEFT);
}
$documentNoFor = static function (array $row, string $baseCode): string {
  $stored = trim((string)($row['document_no'] ?? ''));
  if ($stored !== '' && !preg_match('/^SJ-?$/i', $stored) && $stored !== 'SJ--01') {
    return $stored;
  }
  return 'SJ-' . $baseCode . '-' . str_pad((string)max(1, (int)($row['version_no'] ?? 1)), 2, '0', STR_PAD_LEFT);
};
?>
<style>
#surat-jalan-version{width:100%;max-width:100%;box-sizing:border-box}
#surat-jalan-version img.logo{display:block;width:72px;height:42px;max-width:72px;max-height:42px;object-fit:contain;flex:0 0 auto}
.sj-doc-header{display:flex;align-items:flex-start;justify-content:space-between;gap:24px}
.sj-company{display:flex;align-items:center;gap:10px;min-width:0}
.sj-company-name{font-size:18px;line-height:1.15;font-weight:800;letter-spacing:-.02em}
.sj-company-sub{margin-top:2px;font-size:10px;color:#6b7280}
.sj-title{text-align:right;flex:0 0 auto}
.sj-title-main{font-size:22px;line-height:1;font-weight:900;letter-spacing:.16em}
.sj-title-no{margin-top:4px;font-size:11px;font-weight:800}
.sj-title-meta{margin-top:3px;font-size:9px;font-weight:700;text-transform:uppercase;color:#1d4ed8}
.sj-meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px 28px;padding:16px 0}
.sj-meta-label{font-size:9px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#6b7280}
.sj-meta-value{margin-top:3px;font-size:12px;font-weight:700;line-height:1.25}
.sj-receipt-banner{margin:0 0 14px;border:1px solid #bfdbfe;background:#eff6ff;border-radius:12px;padding:9px 12px;font-size:10px;line-height:1.35;color:#1e3a8a}
.sj-receipt-banner strong{color:#1d4ed8}
.sj-grid-wrap{display:block;width:100%;min-width:100%;max-width:100%;overflow:hidden;box-sizing:border-box;border:1px solid #111827;border-radius:12px}
.sj-grid{display:grid;grid-template-columns:7% 14% 22% 10% 17% 30%;width:100%;box-sizing:border-box}
.sj-grid-head>div{background:#111827;color:#fff;font-size:10px;font-weight:700;padding:8px 10px;box-sizing:border-box;min-width:0;border-right:1px solid #374151}
.sj-grid-head>div:last-child{border-right:0}
.sj-grid-row>div{font-size:10px;line-height:1.35;padding:8px 10px;border-right:1px solid #e5e7eb;border-bottom:1px solid #d1d5db;box-sizing:border-box;min-width:0;overflow-wrap:anywhere;word-break:break-word;vertical-align:top}
.sj-grid-row>div:last-child{border-right:0}
.sj-grid-row:last-child>div{border-bottom:0}
.sj-grid-row>div:nth-child(1),.sj-grid-head>div:nth-child(1){text-align:center}
.sj-grid-row>div:nth-child(4),.sj-grid-head>div:nth-child(4){text-align:center}
.sj-bottom-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:14px}
.sj-signatures{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;margin-top:26px;text-align:center}
.sj-signature-box{break-inside:avoid;page-break-inside:avoid}
.sj-signature-line{margin-top:46px;border-top:1px solid #111827;padding-top:6px}
.sj-footer{margin-top:16px;display:flex;justify-content:space-between;border-top:1px solid #d1d5db;padding-top:7px;font-size:8px;color:#6b7280}
@media screen{.sj-document-card{padding:28px}}
@media print{
 @page{size:A4 portrait;margin:8mm 9mm 9mm}
 html,body{margin:0!important;padding:0!important;background:#fff!important;min-height:0!important;height:auto!important;overflow:visible!important}
 body{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}
 body *{visibility:hidden!important}
 #surat-jalan-version,#surat-jalan-version *{visibility:visible!important}
 #surat-jalan-version{position:absolute!important;top:0!important;left:0!important;width:100%!important;max-width:100%!important;margin:0!important;padding:5mm!important;box-sizing:border-box!important;background:#fff!important;border:1px solid #cbd5e1!important;border-radius:0!important;box-shadow:none!important;color:#111827!important;font-size:9pt!important;break-inside:avoid!important;page-break-inside:avoid!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}
 #surat-jalan-version img.logo{width:20mm!important;height:10mm!important;max-width:20mm!important;max-height:10mm!important}
 .sj-doc-header{gap:10mm!important;padding-bottom:3.5mm!important;border-bottom:1.2px solid #111827!important}
 .sj-company{gap:3mm!important}
 .sj-company-name{font-size:13pt!important}
 .sj-company-sub{font-size:7pt!important}
 .sj-title-main{font-size:18pt!important;letter-spacing:.18em!important}
 .sj-title-no{margin-top:2mm!important;font-size:8pt!important}
 .sj-title-meta{margin-top:1mm!important;font-size:6.5pt!important}
 .sj-meta-grid{gap:2.5mm 10mm!important;padding:3.5mm 0!important}
 .sj-meta-label{font-size:6.5pt!important}
 .sj-meta-value{margin-top:1mm!important;font-size:8.5pt!important}
 .sj-receipt-banner{margin:0 0 3mm!important;border-radius:6px!important;padding:2mm 2.5mm!important;font-size:7pt!important;line-height:1.25!important}
 .sj-grid-wrap{width:100%!important;min-width:100%!important;max-width:100%!important;overflow:visible!important;border:0.35mm solid #111827!important;border-radius:2mm!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}
 .sj-grid{grid-template-columns:7% 14% 22% 10% 17% 30%!important;width:100%!important}
 .sj-grid-head>div{font-size:7.2pt!important;padding:1.9mm 1.7mm!important;line-height:1.15!important;background:#111827!important;color:#fff!important;border-right:0.25mm solid #374151!important;border-bottom:0.35mm solid #111827!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}
 .sj-grid-head>div:last-child{border-right:0!important}
 .sj-grid-row>div{font-size:7.2pt!important;padding:1.9mm 1.7mm!important;line-height:1.2!important;border-right:0.25mm solid #d1d5db!important;border-bottom:0.25mm solid #d1d5db!important}
 .sj-grid-row>div:last-child{border-right:0!important}
 .sj-grid-row:last-child>div{border-bottom:0!important}
 .sj-grid-head>div:nth-child(2),.sj-grid-head>div:nth-child(3),.sj-grid-head>div:nth-child(5),.sj-grid-head>div:nth-child(6){text-align:left!important}
 .sj-grid-head>div:nth-child(4),.sj-grid-row>div:nth-child(4){white-space:nowrap!important}
 .sj-grid-row>div:nth-child(6){font-size:7pt!important;line-height:1.15!important}
 .sj-bottom-grid{gap:3mm!important;margin-top:3mm!important}
 .sj-bottom-grid>div{min-height:18mm!important}
 .sj-meta-label{letter-spacing:.09em!important}
 .sj-bottom-grid .text-\[11px\]{font-size:7pt!important;line-height:1.25!important}
 .sj-signatures{gap:6mm!important;margin-top:6mm!important}
 .sj-signature-line{margin-top:11mm!important;padding-top:1.5mm!important}
 .sj-signatures .text-\[11px\]{font-size:7pt!important}
 .sj-signatures .text-\[9px\]{font-size:6pt!important}
 .sj-footer{margin-top:3mm!important;padding-top:1.5mm!important;font-size:6pt!important}
 .no-print{display:none!important}
}
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
 <div class="no-print mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
 <div class="flex items-center gap-3">
  <a href="<?=url('/transfers/show?id='.(int)$transfer['id'])?>" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-50"><i class="fas fa-arrow-left"></i></a>
  <div><h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Riwayat Surat Jalan</h1><p class="text-sm text-gray-500 dark:text-gray-400">Setiap pengiriman dan penerimaan menghasilkan lembar Surat Jalan baru. Lembar sebelumnya tetap menjadi arsip dan tidak diperbarui.</p></div>
 </div>
 <?php if($version): ?><button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white"><i class="fas fa-print"></i> Cetak Lembar Ini</button><?php endif; ?>
 </div>

 <div class="no-print mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
 <?php foreach($versions as $v): $active=$version && (int)$version['id']===(int)$v['id']; $cardDocNo=$documentNoFor($v,$baseCode); ?>
 <a href="<?=url('/transfers/documents?id='.(int)$transfer['id'].'&version='.(int)$v['version_no'])?>" class="rounded-2xl border p-4 transition <?=$active?'border-blue-500 bg-blue-50 shadow-sm':'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-blue-300 hover:bg-blue-50/40'?>">
  <div class="flex items-center justify-between gap-3"><span class="rounded-full bg-gray-900 px-2.5 py-1 text-[10px] font-bold text-white">LEMBAR <?= (int)$v['version_no'] ?></span><i class="fas fa-file-lines text-blue-600"></i></div>
  <div class="mt-3 text-sm font-bold text-gray-900 dark:text-gray-100"><?=htmlspecialchars($v['event_label'])?></div>
  <div class="mt-1 text-xs text-gray-500 dark:text-gray-400"><?=date('d M Y, H:i',strtotime($v['created_at']))?></div>
  <div class="mt-2 text-xs font-semibold text-blue-700"><?=htmlspecialchars($cardDocNo)?></div>
 </a>
 <?php endforeach; ?>
 </div>

 <?php if(!$version): ?>
 <div class="no-print rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 p-10 text-center text-gray-500 dark:text-gray-400">Belum ada Surat Jalan tersimpan untuk transfer ini.</div>
 <?php else: ?>
 <?php $displayDocumentNo=$documentNoFor($version,$baseCode); ?>
 <section id="surat-jalan-version" class="sj-document-card rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
  <div class="sj-doc-header border-b-2 border-gray-900 pb-4">
  <div class="sj-company"><img class="logo" src="<?=url('/logo')?>" alt="Logo"><div><div class="sj-company-name">PT. BORNEO INOVASI GEMILANG</div><div class="sj-company-sub">Sistem Inventori & Distribusi Internal</div></div></div>
  <div class="sj-title"><div class="sj-title-main">SURAT JALAN</div><div class="sj-title-no">NO. <?=htmlspecialchars($displayDocumentNo)?></div><div class="sj-title-meta">Lembar <?= (int)$version['version_no'] ?> · <?=htmlspecialchars($version['event_label'])?></div></div>
  </div>

  <div class="sj-meta-grid">
  <div><div class="sj-meta-label">Dari Gudang</div><div class="sj-meta-value"><?=htmlspecialchars($version['source_warehouse'] ?? '-')?></div></div>
  <div><div class="sj-meta-label">Ke Gudang</div><div class="sj-meta-value"><?=htmlspecialchars($version['destination_warehouse'] ?? $transfer['dest_warehouse'] ?? '-')?></div></div>
  <div><div class="sj-meta-label">Tanggal Pengiriman</div><div class="sj-meta-value"><?=!empty($version['sent_at'])?date('d F Y, H:i',strtotime($version['sent_at'])):'-'?></div></div>
  <div><div class="sj-meta-label">Pengirim</div><div class="sj-meta-value"><?=htmlspecialchars($version['sender_name'] ?? '-')?></div></div>
  <div><div class="sj-meta-label">Supir / Pembawa</div><div class="sj-meta-value"><?=htmlspecialchars($version['driver_name'] ?? '-')?></div><div class="text-[10px] text-gray-500 dark:text-gray-400"><?=htmlspecialchars($version['vehicle_number'] ?? '')?><?=($version['driver_phone']??'')?' · '.htmlspecialchars($version['driver_phone']):''?></div></div>
  <div><div class="sj-meta-label">Penerima</div><div class="sj-meta-value"><?=htmlspecialchars($version['receiver_name'] ?? 'Belum menerima')?></div><div class="text-[10px] text-gray-500 dark:text-gray-400"><?=htmlspecialchars($version['receiver_role'] ?? '')?><?=($version['receiver_phone']??'')?' · '.htmlspecialchars($version['receiver_phone']):''?></div></div>
  </div>

  <?php if($isReceiptVersion): ?><div class="sj-receipt-banner"><strong>PENERIMAAN LEMBAR <?= (int)$version['version_no'] - 1 ?>:</strong> <?=htmlspecialchars($version['receiver_name'] ?? 'Belum menerima')?><?php if(!empty($version['received_at'])): ?> · <?=date('d F Y, H:i',strtotime($version['received_at']))?><?php endif; ?>. Dokumen ini merekam kondisi Surat Jalan pada saat penerimaan.</div><?php endif; ?>

  <div class="sj-grid-wrap">
  <div class="sj-grid sj-grid-head"><div>No.</div><div>Kode Barang</div><div>Nama Barang</div><div><?=$isReceiptVersion?'Jumlah Diterima':'Jumlah Dikirim'?></div><div>Pelacakan</div><div>Nomor Seri</div></div>
  <?php $no=1; foreach($items as $item): $sns=$item['serial_numbers']??[]; $displayQty=$isReceiptVersion?(int)($item['quantity_received']??0):(int)($item['quantity_sent']??0); ?>
  <div class="sj-grid sj-grid-row">
   <div><?=$no++?></div>
   <div class="font-semibold"><?=htmlspecialchars($item['kode_barang']??'-')?></div>
   <div class="font-semibold"><?=htmlspecialchars($item['nama']??'-')?></div>
   <div class="font-bold"><?=number_format($displayQty)?></div>
   <div><?=!empty($item['is_serialized'])?'Pakai Nomor Seri':'Tanpa Nomor Seri'?></div>
   <div class="text-[9px] leading-4"><?php if($sns): ?><?=implode(', ',array_map(fn($s)=>htmlspecialchars($s['serial_number']??$s),$sns))?><?php else: ?>&mdash;<?php endif; ?></div>
  </div>
  <?php endforeach; ?>
  </div>

  <div class="sj-bottom-grid">
  <div><div class="sj-meta-label">Catatan</div><div class="mt-1 min-h-14 rounded-xl border border-gray-300 dark:border-gray-700 p-2.5 text-[11px] whitespace-pre-line"><?=htmlspecialchars($version['notes']??'')?></div></div>
  <div class="rounded-xl border border-gray-300 dark:border-gray-700 p-3"><div class="sj-meta-label">Keterangan Dokumen</div><div class="mt-1 text-[11px] leading-4">Lembar ini adalah salinan Surat Jalan yang dibekukan pada saat <?=htmlspecialchars(strtolower($version['event_label']))?>. Lembar sebelumnya tetap menjadi arsip dan tidak diperbarui.</div></div>
  </div>

  <div class="sj-signatures">
  <div class="sj-signature-box"><div class="text-[11px] font-semibold">Pengirim</div><div class="sj-signature-line"><div class="font-bold text-[11px]"><?=htmlspecialchars($version['sender_name'] ?? '________________')?></div><div class="text-[9px] text-gray-500 dark:text-gray-400">Tanda tangan & nama jelas</div></div></div>
  <div class="sj-signature-box"><div class="text-[11px] font-semibold">Supir / Pembawa</div><div class="sj-signature-line"><div class="font-bold text-[11px]"><?=htmlspecialchars($version['driver_name'] ?? '________________')?></div><div class="text-[9px] text-gray-500 dark:text-gray-400">Tanda tangan & nama jelas</div></div></div>
  <div class="sj-signature-box"><div class="text-[11px] font-semibold">Penerima</div><div class="sj-signature-line"><div class="font-bold text-[11px]"><?=htmlspecialchars($version['receiver_name'] ?? '________________')?></div><div class="text-[9px] text-gray-500 dark:text-gray-400"><?=htmlspecialchars($version['receiver_role'] ?? 'Tanda tangan & nama jelas')?></div></div></div>
  </div>

  <div class="sj-footer"><span><?=htmlspecialchars($displayDocumentNo)?></span><span>Halaman 1 · Lembar <?= (int)$version['version_no'] ?></span></div>
 </section>
 <?php endif; ?>
</div>
<?php $content=ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>