<?php
$pageTitle = 'Riwayat Surat Jalan';
ob_start();
$version = $selectedVersion ?? null;
$versions = $versions ?? [];
$items = $version ? json_decode($version['items_json'] ?? '[]', true) : [];
$items = is_array($items) ? $items : [];
$isReceiptVersion = $version && (int)$version['version_no'] > 1;
?>
<style>
#surat-jalan-version { width:100%; max-width:100%; box-sizing:border-box; }
#surat-jalan-version img.logo {
    display:block;
    width:72px;
    height:42px;
    max-width:72px;
    max-height:42px;
    object-fit:contain;
    flex:0 0 auto;
}
.sj-doc-header { display:flex; align-items:flex-start; justify-content:space-between; gap:24px; }
.sj-company { display:flex; align-items:center; gap:10px; min-width:0; }
.sj-company-name { font-size:18px; line-height:1.15; font-weight:800; letter-spacing:-0.02em; }
.sj-company-sub { margin-top:2px; font-size:10px; color:#6b7280; }
.sj-title { text-align:right; flex:0 0 auto; }
.sj-title-main { font-size:22px; line-height:1; font-weight:900; letter-spacing:.16em; }
.sj-title-no { margin-top:4px; font-size:11px; font-weight:800; }
.sj-title-meta { margin-top:3px; font-size:9px; font-weight:700; text-transform:uppercase; color:#1d4ed8; }
.sj-meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px 28px; padding:16px 0; }
.sj-meta-label { font-size:9px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; color:#6b7280; }
.sj-meta-value { margin-top:3px; font-size:12px; font-weight:700; line-height:1.25; }
.sj-receipt-banner { margin:0 0 14px; border:1px solid #bfdbfe; background:#eff6ff; border-radius:12px; padding:9px 12px; font-size:10px; line-height:1.35; color:#1e3a8a; }
.sj-receipt-banner strong { color:#1d4ed8; }
.sj-table { display:table!important; width:100%!important; min-width:100%!important; max-width:100%!important; border-collapse:collapse; table-layout:fixed; box-sizing:border-box; }
.sj-table thead,.sj-table tbody,.sj-table tr { width:100%!important; }
.sj-table th,.sj-table td { box-sizing:border-box; overflow-wrap:anywhere; word-break:break-word; vertical-align:top; }
.sj-table th { font-size:10px; }
.sj-table td { font-size:10px; line-height:1.35; }
.sj-table .col-no{width:7%!important;}.sj-table .col-code{width:14%!important;}.sj-table .col-name{width:22%!important;}.sj-table .col-qty{width:10%!important;}.sj-table .col-track{width:17%!important;}.sj-table .col-sn{width:30%!important;}
.sj-bottom-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:14px; }
.sj-signatures { display:grid; grid-template-columns:repeat(3,1fr); gap:22px; margin-top:26px; text-align:center; }
.sj-signature-box { break-inside:avoid; page-break-inside:avoid; }
.sj-signature-line { margin-top:46px; border-top:1px solid #111827; padding-top:6px; }
.sj-footer { margin-top:16px; display:flex; justify-content:space-between; border-top:1px solid #d1d5db; padding-top:7px; font-size:8px; color:#6b7280; }

@media screen {
    .sj-document-card { padding:28px; }
}

@media print {
    @page { size:auto; margin:5mm; }
    html,body { margin:0!important; padding:0!important; background:#fff!important; }
    body * { visibility:hidden!important; }
    #surat-jalan-version, #surat-jalan-version * { visibility:visible!important; }
    #surat-jalan-version {
        position:static!important;
        width:100%!important;
        max-width:100%!important;
        margin:0!important;
        padding:3mm!important;
        box-sizing:border-box!important;
        background:#fff!important;
        border:0!important;
        box-shadow:none!important;
        color:#111827!important;
        font-size:9pt!important;
    }
    .no-print { display:none!important; }
    #surat-jalan-version img.logo { width:24mm!important; height:10mm!important; max-width:24mm!important; max-height:10mm!important; }
    .sj-company-name { font-size:12pt!important; }
    .sj-company-sub { font-size:8pt!important; }
    .sj-title-main { font-size:17pt!important; }
    .sj-title-no { font-size:9pt!important; }
    .sj-title-meta { font-size:7.5pt!important; }
    .sj-meta-grid { gap:2.5mm 10mm!important; padding:3.5mm 0!important; }
    .sj-meta-label { font-size:7pt!important; }
    .sj-meta-value { font-size:9pt!important; }
    .sj-receipt-banner { margin-bottom:3.5mm!important; padding:2.5mm 3mm!important; font-size:7.5pt!important; }
    .sj-table { display:table!important; width:100%!important; min-width:100%!important; max-width:100%!important; table-layout:fixed!important; }
    .sj-table thead,.sj-table tbody,.sj-table tr { width:100%!important; }
    .sj-table th { font-size:7.5pt!important; padding:2.2mm 2mm!important; }
    .sj-table td { font-size:7.5pt!important; padding:2mm!important; line-height:1.25!important; }
    .sj-table .col-no{width:7%!important;}.sj-table .col-code{width:14%!important;}.sj-table .col-name{width:22%!important;}.sj-table .col-qty{width:10%!important;}.sj-table .col-track{width:17%!important;}.sj-table .col-sn{width:30%!important;}
    .sj-bottom-grid { gap:3mm!important; margin-top:3.5mm!important; }
    .sj-signatures { gap:6mm!important; margin-top:7mm!important; }
    .sj-signature-line { margin-top:14mm!important; padding-top:2mm!important; }
    .sj-footer { margin-top:4mm!important; padding-top:2mm!important; font-size:6.5pt!important; }
    .sj-table thead { display:table-header-group!important; }
    .sj-table tr { break-inside:avoid!important; page-break-inside:avoid!important; }
    .sj-table th,.sj-table td { white-space:normal!important; overflow-wrap:anywhere!important; word-break:break-word!important; }
}
</style>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="no-print mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-3">
            <a href="<?=url('/transfers/show?id='.(int)$transfer['id'])?>" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 hover:bg-gray-50"><i class="fas fa-arrow-left"></i></a>
            <div><h1 class="text-2xl font-bold text-gray-900">Riwayat Surat Jalan</h1><p class="text-sm text-gray-500">Setiap pengiriman dan penerimaan menghasilkan lembar Surat Jalan baru. Lembar sebelumnya tidak berubah.</p></div>
        </div>
        <?php if($version): ?><button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white"><i class="fas fa-print"></i> Cetak Lembar Ini</button><?php endif; ?>
    </div>

    <div class="no-print mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <?php foreach($versions as $v): $active=$version && (int)$version['id']===(int)$v['id']; ?>
        <a href="<?=url('/transfers/documents?id='.(int)$transfer['id'].'&version='.(int)$v['version_no'])?>" class="rounded-2xl border p-4 transition <?=$active?'border-blue-500 bg-blue-50 shadow-sm':'border-gray-200 bg-white hover:border-blue-300 hover:bg-blue-50/40'?>">
            <div class="flex items-center justify-between gap-3"><span class="rounded-full bg-gray-900 px-2.5 py-1 text-[10px] font-bold text-white">LEMBAR <?= (int)$v['version_no'] ?></span><i class="fas fa-file-lines text-blue-600"></i></div>
            <div class="mt-3 text-sm font-bold text-gray-900"><?=htmlspecialchars($v['event_label'])?></div>
            <div class="mt-1 text-xs text-gray-500"><?=date('d M Y, H:i',strtotime($v['created_at']))?></div>
            <div class="mt-2 text-xs font-semibold text-blue-700"><?=htmlspecialchars($v['document_no'])?></div>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if(!$version): ?>
        <div class="no-print rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500">Belum ada Surat Jalan tersimpan untuk transfer ini.</div>
    <?php else: ?>
    <section id="surat-jalan-version" class="sj-document-card rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="sj-doc-header border-b-2 border-gray-900 pb-4">
            <div class="sj-company"><img class="logo" src="<?=url('/logo')?>" alt="Logo"><div><div class="sj-company-name">PT. BORNEO INOVASI GEMILANG</div><div class="sj-company-sub">Sistem Inventori & Distribusi Internal</div></div></div>
            <div class="sj-title"><div class="sj-title-main">SURAT JALAN</div><div class="sj-title-no">NO. <?=htmlspecialchars($version['document_no'])?></div><div class="sj-title-meta">Lembar <?= (int)$version['version_no'] ?> · <?=htmlspecialchars($version['event_label'])?></div></div>
        </div>

        <div class="sj-meta-grid">
            <div><div class="sj-meta-label">Dari Gudang</div><div class="sj-meta-value"><?=htmlspecialchars($version['source_warehouse'] ?? '-')?></div></div>
            <div><div class="sj-meta-label">Ke Gudang</div><div class="sj-meta-value"><?=htmlspecialchars($version['destination_warehouse'] ?? '-')?></div></div>
            <div><div class="sj-meta-label">Tanggal Pengiriman</div><div class="sj-meta-value"><?=!empty($version['sent_at'])?date('d F Y, H:i',strtotime($version['sent_at'])):'-'?></div></div>
            <div><div class="sj-meta-label">Pengirim</div><div class="sj-meta-value"><?=htmlspecialchars($version['sender_name'] ?? '-')?></div></div>
            <div><div class="sj-meta-label">Supir / Pembawa</div><div class="sj-meta-value"><?=htmlspecialchars($version['driver_name'] ?? '-')?></div><div class="text-[10px] text-gray-500"><?=htmlspecialchars($version['vehicle_number'] ?? '')?><?=($version['driver_phone']??'')?' · '.htmlspecialchars($version['driver_phone']):''?></div></div>
            <div><div class="sj-meta-label">Penerima</div><div class="sj-meta-value"><?=htmlspecialchars($version['receiver_name'] ?? 'Belum menerima')?></div><div class="text-[10px] text-gray-500"><?=htmlspecialchars($version['receiver_role'] ?? '')?><?=($version['receiver_phone']??'')?' · '.htmlspecialchars($version['receiver_phone']):''?></div></div>
        </div>

        <?php if($isReceiptVersion): ?>
            <div class="sj-receipt-banner"><strong>PENERIMAAN LEMBAR <?= (int)$version['version_no'] - 1 ?>:</strong> <?=htmlspecialchars($version['receiver_name'] ?? 'Belum menerima')?><?php if(!empty($version['received_at'])): ?> · <?=date('d F Y, H:i',strtotime($version['received_at']))?><?php endif; ?>. Dokumen ini merekam kondisi Surat Jalan pada saat penerimaan.</div>
        <?php endif; ?>

        <div class="overflow-hidden rounded-xl border border-gray-900"><table class="sj-table"><colgroup><col class="col-no"><col class="col-code"><col class="col-name"><col class="col-qty"><col class="col-track"><col class="col-sn"></colgroup><thead><tr class="bg-gray-900 text-white"><th class="px-2.5 py-2 text-center">No.</th><th class="px-2.5 py-2 text-left">Kode Barang</th><th class="px-2.5 py-2 text-left">Nama Barang</th><th class="px-2.5 py-2 text-center"><?=$isReceiptVersion?'Jumlah Diterima':'Jumlah Dikirim'?></th><th class="px-2.5 py-2 text-left">Pelacakan</th><th class="px-2.5 py-2 text-left">Nomor Seri</th></tr></thead><tbody><?php $no=1; foreach($items as $item): $sns=$item['serial_numbers']??[]; $displayQty=$isReceiptVersion ? (int)($item['quantity_received']??0) : (int)($item['quantity_sent']??0); ?><tr class="border-b border-gray-300"><td class="px-2.5 py-2 text-center font-semibold"><?=$no++?></td><td class="px-2.5 py-2 font-semibold"><?=htmlspecialchars($item['kode_barang']??'-')?></td><td class="px-2.5 py-2 font-semibold"><?=htmlspecialchars($item['nama']??'-')?></td><td class="px-2.5 py-2 text-center font-bold"><?=number_format($displayQty)?></td><td class="px-2.5 py-2"><?=!empty($item['is_serialized'])?'Pakai Nomor Seri':'Tanpa Nomor Seri'?></td><td class="px-2.5 py-2 text-[9px] leading-4"><?php if($sns): ?><?=implode(', ',array_map(fn($s)=>htmlspecialchars($s['serial_number']??$s),$sns))?><?php else: ?>&mdash;<?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>

        <div class="sj-bottom-grid"><div><div class="sj-meta-label">Catatan</div><div class="mt-1 min-h-14 rounded-xl border border-gray-300 p-2.5 text-[11px] whitespace-pre-line"><?=htmlspecialchars($version['notes']??'')?></div></div><div class="rounded-xl border border-gray-300 p-3"><div class="sj-meta-label">Keterangan Dokumen</div><div class="mt-1 text-[11px] leading-4">Lembar ini adalah salinan Surat Jalan yang dibekukan pada saat <?=htmlspecialchars(strtolower($version['event_label']))?>. Lembar sebelumnya tetap menjadi arsip dan tidak diperbarui.</div></div></div>

        <div class="sj-signatures"><div class="sj-signature-box"><div class="text-[11px] font-semibold">Pengirim</div><div class="sj-signature-line"><div class="font-bold text-[11px]"><?=htmlspecialchars($version['sender_name'] ?? '________________')?></div><div class="text-[9px] text-gray-500">Tanda tangan & nama jelas</div></div></div><div class="sj-signature-box"><div class="text-[11px] font-semibold">Supir / Pembawa</div><div class="sj-signature-line"><div class="font-bold text-[11px]"><?=htmlspecialchars($version['driver_name'] ?? '________________')?></div><div class="text-[9px] text-gray-500">Tanda tangan & nama jelas</div></div></div><div class="sj-signature-box"><div class="text-[11px] font-semibold">Penerima</div><div class="sj-signature-line"><div class="font-bold text-[11px]"><?=htmlspecialchars($version['receiver_name'] ?? '________________')?></div><div class="text-[9px] text-gray-500"><?=htmlspecialchars($version['receiver_role'] ?? 'Tanda tangan & nama jelas')?></div></div></div></div>

        <div class="sj-footer"><span><?=htmlspecialchars($version['document_no'])?></span><span>Halaman 1 · Lembar <?= (int)$version['version_no'] ?></span></div>
    </section>
    <?php endif; ?>
</div>
<?php $content=ob_get_clean(); require __DIR__ . '/../../layouts/app.php'; ?>
