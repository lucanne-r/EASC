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

    btn.addEventListener('click', function () {
        var html = '';
        wraps.forEach(function (w) {
            var t = w.querySelector('table');
            if (!t) return;

            var c = t.cloneNode(true);
            c.querySelectorAll('form,input,button,select,.co-btn,.no-export').forEach(function (el) { el.remove(); });
            c.querySelectorAll('th').forEach(function (th) {
                th.setAttribute('style', 'background:#6D1A2B;color:#FFFFFF;font-weight:bold');
            });

            var p = w.previousElementSibling;
            var title = (p && p.tagName !== 'FORM' && p.innerText.trim().length < 80) ? p.innerText.trim() : '';
            html += (title ? '<p><b>' + title + '</b></p>' : '') + c.outerHTML + '<br>';
        });

        var h2 = document.querySelector('h2');
        var name = (h2 ? h2.textContent.trim() : 'Data').replace(/\s+/g, '_')
                 + '_' + new Date().toISOString().slice(0, 10) + '.xls';

        var doc = '<html><head><meta charset="UTF-8"></head><body>' + html + '</body></html>';
        var blob = new Blob(['\ufeff' + doc], { type: 'application/vnd.ms-excel;charset=utf-8' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = name;
        document.body.appendChild(a);
        a.click();
        a.remove();
    });
})();
</script>
</body>
</html>