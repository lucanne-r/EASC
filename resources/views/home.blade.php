<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EACS · Engineering Activity Control</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f4f5f7; color: #17212e; font: 14px/1.5 "Segoe UI", Arial, sans-serif; }
        a { color: inherit; text-decoration: none; }

        .top { background: #1b3248; color: #fff; padding: 14px 0; border-top: 2px solid #0e1c2b; }
        .bar { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; }
        .brand { font-size: 21px; font-weight: 700; }
        .sub { font-size: 13px; opacity: .85; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .pill { background: #fff; color: #17212e; border-radius: 8px; padding: 10px 14px; font-weight: 600; font-size: 13px; }

        .wrap { max-width: 1050px; margin: 0 auto; padding: 0 20px 40px; }
        .hero { background: #203a5f; color: #fff; border-radius: 16px; text-align: center; padding: 38px 20px; margin: 22px 0; }
        .hero h1 { margin: 0 0 8px; font-size: 32px; }
        .hero p { margin: 0; }
        .arrow { text-align: center; font-size: 26px; color: #2f6f57; margin: 14px 0; }

        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 12px; }
        .card { display: block; background: #fff; border: 1px solid #dfe3ea; border-radius: 14px; padding: 20px; }
        .card:hover { border-color: #203a5f; }
        .card h3 { margin: 14px 0 4px; font-size: 20px; }
        .card .d { color: #667085; font-size: 13px; margin-bottom: 14px; }
        .card .go { font-weight: 600; }

        .overview { background: #f0f1f3; border-radius: 12px; padding: 18px; text-align: center; }
        .overview h3 { margin: 0 0 4px; }
        .overview p { margin: 0; }
    </style>
</head>
<body>

<header class="top">
    <div class="bar">
        <div>
            <div class="brand">⚙ EACS · Engineering Activity Control</div>
            <div class="sub">Project aktif · Semua Project</div>
        </div>
        <div class="actions">
            <a class="pill" href="/">Home / Pilih Project</a>
            <a class="pill" href="#">Dashboard &amp; Activity</a>
            <a class="pill" href="#">+ Add Activity</a>
            <a class="pill" href="#">Export CSV</a>
        </div>
    </div>
</header>

<div class="wrap">
    <section class="hero">
        <h1>ENGINEERING ACTIVITY CONTROL SYSTEM</h1>
        <p>Standar kontrol aktivitas Engineering</p>
    </section>

    <div class="arrow">↓</div>

    <div class="cards">
        @foreach ($projects as $p)
            <a class="card" href="#">
                <div>▤</div>
                <h3>{{ $p['name'] }}</h3>
                <div class="d">{{ $p['desc'] }}</div>
                <div class="go">Buka Activity Explorer →</div>
            </a>
        @endforeach

        <a class="card" href="#">
            <div>▤</div>
            <h3>Project lainnya</h3>
            <div class="d">Lihat daftar project yang tersedia</div>
            <div class="go">Buka Activity Explorer →</div>
        </a>
    </div>

    <div class="arrow">↓</div>

    <section class="overview">
        <h3>Master Project Overview</h3>
        <p>Tahap berikutnya: melihat seluruh project, aktivitas Open, prioritas, PIC, hambatan, dan progres dari satu tempat.</p>
    </section>
</div>

</body>
</html>