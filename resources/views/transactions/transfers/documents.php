<?php
$pageTitle = 'Riwayat Surat Jalan';
ob_start();
$version = $selectedVersion ?? null;
$versions = $versions ?? [];
$items = $version ? json_decode($version['items_json'] ?? '[]', true) : [];
$items = is_array($items) ? $items : [];
?>
<style>
@media print {
    @page { size: auto; margin: 5mm; }
    body { margin:0!important; padding:0!important; background:#fff!important; }
    body * { visibility:hidden!important; }
    #surat-jalan-version, #surat-jalan-version * { visibility:visible!important; }
    #surat-jalan-version { position:static!important; width:100%!important; max-width:100%!important; margin:0!important; padding:4mm!important; box-sizing:border-box!important; background:#fff!important; border:0!important; box-shadow:none!important; }
    .no-print { display:none!important; }
    #surat-jalan-version table { width:100%!important; border-collapse:collapse!important; table-layout:fixed!important; }
    #surat-jalan-version thead { display:table-header-group!important; }
    #surat-jalan-version tr { break-inside:avoid!important; page-break-inside:avoid!important; }
    #surat-jalan-version th,#surat-jalan-version td { white-space:normal!important; overflow-wrap:anywhere!important; word-break:break-word!important; vertical-align:top!important; }
    #surat-jalan-version img.logo { width:30mm!important; height:12mm!important; max-width:30mm!important; max-height:12mm!important; object-fit:contain!important; }
    .signature-box { break-inside:avoid!important; page-break-inside:avoid!important; }
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
    <section id="surat-jalan-version" class="rounded-2xl border border-gray-200 bg-white p-7 shadow-sm">
        <div class="flex items-start justify-between gap-5 border-b-2 border-gray-900 pb-4">
            <div class="flex items-center gap-3"><img class="logo" src="<?=url('/logo')?>" alt="Logo"><div><div class="text-xl font-extrabold tracking-tight">PT. BORNEO INOVASI GEMILANG</div><div class="text-xs text-gray-500">Sistem Inventori & Distribusi Internal</div></div></div>
            <div class="text-right"><div class="text-2xl font-black tracking-[0.18em]">SURAT JALAN</div><div class="mt-1 text-sm font-bold">NO. <?=htmlspecialchars($version['document_no'])?></div><div class="mt-1 text-[10px] font-semibold uppercase text-blue-700">Lembar <?= (int)$version['version_no'] ?> · <?=htmlspecialchars($version['event_label'])?></div></div>
        </div>

        <div class="grid grid-cols-1 gap-4 py-5 md:grid-cols-2">
            <div><div class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Dari Gudang</div><div class="mt-1 text-sm font-bold"><?=htmlspecialchars($version['source_warehouse'] ?? '-')?></div></div>
            <div><div class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Ke Gudang</div><div class="mt-1 text-sm font-bold"><?=htmlspecialchars($version['destination_warehouse'] ?? '-')?></div></div>
            <div><div class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Tanggal Pengiriman</div><div class="mt-1 text-sm font-semibold"><?=!empty($version['sent_at'])?date('d F Y, H:i',strtotime($version['sent_at'])):'-'?></div></div>
            <div><div class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Pengirim</div><div class="mt-1 text-sm font-semibold"><?=htmlspecialchars($version['sender_name'] ?? '-')?></div></div>
            <div><div class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Supir / Pembawa</div><div class="mt-1 text-sm font-semibold"><?=htmlspecialchars($version['driver_name'] ?? '-')?></div><div class="text-xs text-gray-500"><?=htmlspecialchars($version['vehicle_number'] ?? '')?><?=($version['driver_phone']??'')?' · '.htmlspecialchars($version['driver_phone']):''?></div></div>
            <div><div class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Penerima</div><div class="mt-1 text-sm font-semibold"><?=htmlspecialchars($version['receiver_name'] ?? 'Belum menerima')?></div><div class="text-xs text-gray-500"><?=htmlspecialchars($version['receiver_role'] ?? '')?><?=($version['receiver_phone']??'')?' · '.htmlspecialchars($version['receiver_phone']):''?></div></div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-900"><table class="w-full text-sm"><thead><tr class="bg-gray-900 text-white"><th class="px-3 py-3 text-center">No</th><th class="px-3 py-3 text-left">Kode</th><th class="px-3 py-3 text-left">Nama Barang</th><th class="px-3 py-3 text-center">Jumlah</th><th class="px-3 py-3 text-left">Pelacakan</th><th class="px-3 py-3 text-left">Nomor Seri</th></tr></thead><tbody><?php $no=1; foreach($items as $item): $sns=$item['serial_numbers']??[]; ?><tr class="border-b border-gray-300"><td class="px-3 py-3 text-center align-top"><?=$no++?></td><td class="px-3 py-3 align-top font-semibold"><?=htmlspecialchars($item['kode_barang']??'-')?></td><td class="px-3 py-3 align-top font-semibold"><?=htmlspecialchars($item['nama']??'-')?></td><td class="px-3 py-3 text-center align-top font-bold"><?=number_format((int)($item['quantity_sent']??0))?></td><td class="px-3 py-3 align-top"><?=!empty($item['is_serialized'])?'Pakai Nomor Seri':'Tanpa Nomor Seri'?></td><td class="px-3 py-3 align-top text-xs leading-5"><?php if($sns): ?><?=implode(', ',array_map(fn($s)=>htmlspecialchars($s['serial_number']??$s),$sns))?><?php else: ?>&mdash;<?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>

        <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2"><div><div class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Catatan</div><div class="mt-2 min-h-16 rounded-xl border border-gray-300 p-3 text-sm whitespace-pre-line"><?=htmlspecialchars($version['notes']??'')?></div></div><div class="rounded-xl border border-gray-300 p-4"><div class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Keterangan Dokumen</div><div class="mt-2 text-sm">Lembar ini adalah salinan Surat Jalan yang dibekukan pada saat <?=htmlspecialchars(strtolower($version['event_label']))?>. Lembar sebelumnya tetap menjadi arsip dan tidak diperbarui.</div></div></div>

        <div class="mt-10 grid grid-cols-3 gap-8 text-center"><div class="signature-box"><div class="text-sm font-semibold">Pengirim</div><div class="mt-16 border-t border-gray-900 pt-2"><div class="font-bold"><?=htmlspecialchars($version['sender_name'] ?? '________________')?></div><div class="text-xs text-gray-500">Tanda tangan & nama jelas</div></div></div><div class="signature-box"><div class="text-sm font-semibold">Supir / Pembawa</div><div class="mt-16 border-t border-gray-900 pt-2"><div class="font-bold"><?=htmlspecialchars($version['driver_name'] ?? '________________')?></div><div class="text-xs text-gray-500">Tanda tangan & nama jelas</div></div></div><div class="signature-box"><div class="text-sm font-semibold">Penerima</div><div class="mt-16 border-t border-gray-900 pt-2"><div class="font-bold"><?=htmlspecialchars($version['receiver_name'] ?? '________________')?></div><div class="text-xs text-gray-500"><?=htmlspecialchars($version['receiver_role'] ?? 'Tanda tangan & nama jelas')?></div></div></div></div>

        <div class="mt-7 flex items-center justify-between border-t border-gray-300 pt-3 text-[10px] text-gray-500"><span><?=htmlspecialchars($version['document_no'])?></span><span>Halaman 1 · Lembar <?= (int)$version['version_no'] ?></span></div>
    </section>
    <?php endif; ?>
</div>
<?php $content=ob_get_clean(); require __DIR__.'/../../layouts/app.php'; ?>
