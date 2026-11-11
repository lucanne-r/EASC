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
        .bar { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; }
        .brand { font-size: 21px; font-weight: 700; }
        .sub { font-size: 13px; opacity: .85; }
        .pill { background: #fff; color: #17212e; border-radius: 8px; padding: 10px 14px; font-weight: 600; font-size: 13px; }

        .shell { max-width: 1200px; margin: 0 auto; padding: 20px; display: flex; gap: 20px; align-items: flex-start; }
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

</body>
</html>