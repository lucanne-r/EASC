<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ $project->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f4f5f7; color: #17212e; font: 14px/1.5 "Segoe UI", Arial, sans-serif; }
        a { color: inherit; text-decoration: none; }

        .top { background: #1b3248; color: #fff; padding: 14px 0; border-top: 2px solid #0e1c2b; }
        .bar { max-width: 1600px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; }
        .brand { font-size: 21px; font-weight: 700; }
        .sub { font-size: 13px; opacity: .85; }
        .pill { background: #fff; color: #17212e; border-radius: 8px; padding: 10px 14px; font-weight: 600; font-size: 13px; }

        .shell { max-width: 1600px; margin: 0 auto; padding: 20px; display: flex; gap: 20px; align-items: flex-start; }
        .side { width: 220px; flex-shrink: 0; background: #fff; border: 1px solid #dfe3ea; border-radius: 12px; padding: 14px; }
        .side .pn { font-weight: 700; font-size: 16px; margin-bottom: 12px; }
        .side a { display: block; padding: 9px 10px; border-radius: 8px; margin-bottom: 2px; font-weight: 600; }
        .side a:hover { background: #f4f5f7; }
        .side a.on { background: #203a5f; color: #fff; }
        .side a.back { margin-top: 12px; font-weight: 400; color: #667085; font-size: 13px; }

        main { flex: 1; min-width: 0; }
        h2 { margin: 0 0 4px; font-size: 20px; }
        .lead { color: #667085; margin: 0 0 16px; }
        .box { background: #fff; border: 1px dashed #b8c0cc; border-radius: 12px; padding: 30px; text-align: center; color: #667085; }

        @media (max-width: 800px) { .shell { flex-direction: column; } .side { width: 100%; } }
    </style>
</head>
<body>

<header class="top">
    <div class="bar">
        <div>
            <div class="brand">⚙ EACS · Engineering Activity Control</div>
            <div class="sub">Project aktif · {{ $project->name }}</div>
        </div>
        <a class="pill" href="/">Home / Pilih Project</a>
    </div>
</header>

<div class="shell">
    <aside class="side">
        <div class="pn">{{ $project->name }}</div>
        <a href="/p/{{ $project->slug }}/parameter" class="{{ request()->is('p/*/parameter') ? 'on' : '' }}">Parameter</a>
        <a href="/p/{{ $project->slug }}/control-activity" class="{{ request()->is('p/*/control-activity') ? 'on' : '' }}">Control Activity</a>
        <a href="/p/{{ $project->slug }}/control-order" class="{{ request()->is('p/*/control-order') ? 'on' : '' }}">Control Order</a>
        <a class="back" href="/">← Pilih project lain</a>
    </aside>

    <main>
        @yield('content')
    </main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>
<script>
(function () {
    var wraps = document.querySelectorAll('.co-wrap');
    if (!wraps.length) return;

    var anchor = document.querySelector('h2 + .lead') || document.querySelector('h2');
    if (!anchor) return;

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'co-btn ghost';
    btn.textContent = 'Ekspor Excel';
    btn.style.margin = '0 0 12px';
    anchor.insertAdjacentElement('afterend', btn);

    var MAX_W = 300, MAX_H = 110;
    var imgCache = {};

    // muat gambar, taruh di latar putih, ubah ke PNG
    function loadImg(src) {
        if (!imgCache[src]) {
            imgCache[src] = new Promise(function (resolve) {
                var im = new Image();
                im.onload = function () {
                    var c = document.createElement('canvas');
                    c.width = im.naturalWidth;
                    c.height = im.naturalHeight;
                    var ctx = c.getContext('2d');
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, c.width, c.height);
                    ctx.drawImage(im, 0, 0);
                    var scale = Math.min(MAX_W / c.width, MAX_H / c.height, 1);
                    resolve({
                        data: c.toDataURL('image/png'),
                        w: Math.round(c.width * scale),
                        h: Math.round(c.height * scale)
                    });
                };
                im.onerror = function () { resolve(null); };
                im.src = src;
            });
        }
        return imgCache[src];
    }

    function cellText(td) {
        var c = td.cloneNode(true);
        c.querySelectorAll('form,input,button,select,img,.co-btn,.no-export').forEach(function (el) { el.remove(); });
        return c.textContent.replace(/\s+/g, ' ').trim();
    }

    function sheetName(wb, base) {
        var n = base.replace(/[\\\/\?\*\[\]:]/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 28) || 'Data';
        var name = n, i = 2;
        while (wb.getWorksheet(name)) { name = n + ' ' + (i++); }
        return name;
    }

    btn.addEventListener('click', async function () {
        if (!window.ExcelJS) {
            alert('Library Excel belum termuat. Cek koneksi internet, lalu refresh halaman (Ctrl+F5).');
            return;
        }
        var label = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Menyiapkan file...';

        try {
            var wb = new ExcelJS.Workbook();
            var tab = document.querySelector('.pm-tabs a.on');
            var h2 = document.querySelector('h2');
            var baseName = tab ? tab.textContent : (h2 ? h2.textContent : 'Data');

            for (var wi = 0; wi < wraps.length; wi++) {
                var t = wraps[wi].querySelector('table');
                if (!t || !t.rows.length) continue;

                var rows = Array.prototype.slice.call(t.rows);
                var headCells = Array.prototype.slice.call(rows[0].cells);
                var keep = [];
                headCells.forEach(function (th, i) { if (th.textContent.trim() !== '') keep.push(i); });
                var body = rows.slice(1).filter(function (r) {
                    return r.cells.length > 1 && !r.querySelector('td[colspan]');
                });

                var ws = wb.addWorksheet(sheetName(wb, baseName));
                ws.addRow(keep.map(function (i) { return headCells[i].textContent.trim(); }));
                var hr = ws.getRow(1);
                hr.height = 24;
                hr.eachCell(function (c) {
                    c.font = { bold: true, color: { argb: 'FFFFFFFF' } };
                    c.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF6D1A2B' } };
                    c.alignment = { vertical: 'middle' };
                });

                var widths = keep.map(function (i) { return Math.max(8, headCells[i].textContent.trim().length + 2); });
                var imgCols = {};

                for (var r = 0; r < body.length; r++) {
                    var cells = body[r].cells;
                    var values = [];
                    var rowH = 0;
                    var imgsHere = [];

                    for (var k = 0; k < keep.length; k++) {
                        var td = cells[keep[k]];
                        if (!td) { values.push(''); continue; }

                        var img = td.querySelector('img');
                        if (img) {
                            var info = await loadImg(img.src);
                            values.push('');
                            if (info) {
                                imgsHere.push({ k: k, info: info });
                                rowH = Math.max(rowH, info.h);
                            }
                            continue;
                        }

                        var txt = cellText(td);
                        if (td.classList.contains('n') && /^-?\d+$/.test(txt)) {
                            values.push(Number(txt));
                        } else {
                            values.push(txt);
                        }
                        widths[k] = Math.max(widths[k], Math.min(txt.length + 2, 50));
                    }

                    var row = ws.addRow(values);
                    row.alignment = { vertical: 'middle', wrapText: true };
                    if (rowH) row.height = Math.round(rowH * 0.75) + 8;

                    for (var m = 0; m < imgsHere.length; m++) {
                        var it = imgsHere[m];
                        var id = wb.addImage({ base64: it.info.data, extension: 'png' });
                        ws.addImage(id, {
                            tl: { col: it.k + 0.05, row: ws.rowCount - 1 + 0.05 },
                            ext: { width: it.info.w, height: it.info.h },
                            editAs: 'oneCell'
                        });
                        imgCols[it.k] = Math.max(imgCols[it.k] || 0, it.info.w);
                    }
                }

                for (var c2 = 0; c2 < keep.length; c2++) {
                    ws.getColumn(c2 + 1).width = imgCols[c2] ? Math.ceil(imgCols[c2] / 7) + 2 : widths[c2];
                }
                ws.views = [{ state: 'frozen', ySplit: 1 }];
                ws.autoFilter = { from: { row: 1, column: 1 }, to: { row: 1, column: keep.length } };
            }

            var buf = await wb.xlsx.writeBuffer();
            var blob = new Blob([buf], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            var fname = (h2 ? h2.textContent.trim() : 'Data').replace(/\s+/g, '_') + '_' + new Date().toISOString().slice(0, 10) + '.xlsx';
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = fname;
            document.body.appendChild(a);
            a.click();
            a.remove();
        } catch (e) {
            console.error(e);
            alert('Gagal membuat file Excel. Refresh halaman lalu coba lagi, atau kirim screenshot error di Console (F12).');
        } finally {
            btn.disabled = false;
            btn.textContent = label;
        }
    });
})();
</script>
</body>
</html>