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
        .pill { background: #fff; color: #17212e; border: 0; border-radius: 8px; padding: 10px 14px; font: 600 13px "Segoe UI", Arial, sans-serif; cursor: pointer; }

        .wrap { max-width: 1050px; margin: 0 auto; padding: 0 20px 40px; }
        .hero { background: #203a5f; color: #fff; border-radius: 16px; text-align: center; padding: 38px 20px; margin: 22px 0; }
        .hero h1 { margin: 0 0 8px; font-size: 32px; }
        .hero p { margin: 0; }
        .arrow { text-align: center; font-size: 26px; color: #2f6f57; margin: 14px 0; }

        .flash { background: #e2f5e9; color: #157a3d; padding: 10px 14px; border-radius: 8px; margin-bottom: 12px; }
        .error { background: #fde8e6; color: #b42318; padding: 10px 14px; border-radius: 8px; margin-bottom: 12px; }

        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 12px; }
        .card { background: #fff; border: 1px solid #dfe3ea; border-radius: 14px; padding: 20px; position: relative; }
        .card:hover { border-color: #203a5f; }
        .card h3 { margin: 14px 0 4px; font-size: 20px; }
        .card .d { color: #667085; font-size: 13px; margin-bottom: 14px; }
        .card .go { font-weight: 600; }
        .card .del { position: absolute; top: 14px; right: 14px; }
        .btn-del { background: #fde8e6; color: #b42318; border: 0; border-radius: 6px; padding: 4px 10px; font-size: 12px; font-weight: 600; cursor: pointer; }
        .card.add { border: 2px dashed #b8c0cc; background: transparent; text-align: center; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; font: inherit; color: #203a5f; }
        .card.add .plus { font-size: 34px; line-height: 1; }

        .overview { background: #f0f1f3; border-radius: 12px; padding: 18px; text-align: center; }
        .overview h3 { margin: 0 0 4px; }
        .overview p { margin: 0; }

        dialog { border: 0; border-radius: 14px; padding: 20px; width: min(420px, 94vw); }
        dialog::backdrop { background: rgba(10, 20, 35, .5); }
        dialog h3 { margin: 0 0 14px; }
        .f { margin-bottom: 10px; }
        .f label { display: block; font-size: 12px; color: #667085; margin-bottom: 3px; }
        .f input { width: 100%; font: inherit; border: 1px solid #dfe3ea; border-radius: 8px; padding: 8px 10px; }
        .dl { display: flex; gap: 8px; margin-top: 14px; }
        .btn { background: #203a5f; color: #fff; border: 0; border-radius: 8px; padding: 8px 14px; font: 600 13px "Segoe UI", Arial, sans-serif; cursor: pointer; }
        .btn.ghost { background: #fff; color: #17212e; border: 1px solid #dfe3ea; }
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
            <button type="button" class="pill" onclick="document.getElementById('dlg').showModal()">+ Tambah Project</button>
        </div>
    </div>
</header>

<div class="wrap">
    <section class="hero">
        <h1>ENGINEERING ACTIVITY CONTROL SYSTEM</h1>
        <p>Standar kontrol aktivitas Engineering</p>
    </section>

    @if (session('ok'))<div class="flash">{{ session('ok') }}</div>@endif
    @if ($errors->any())
        <div class="error">@foreach ($errors->all() as $e){{ $e }}<br>@endforeach</div>
    @endif

    <div class="arrow">↓</div>

    <div class="cards">
        @foreach ($projects as $p)
            <div class="card">
                <form class="del" method="POST" action="/projects/{{ $p->id }}" onsubmit="return confirm('Hapus project {{ $p->name }}?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn-del">Hapus</button>
                </form>
                   <a href="/p/{{ $p->slug }}">
                    <div>▤</div>
                    <h3>{{ $p->name }}</h3>
                    <div class="d">{{ $p->description ?: 'Database aktivitas sendiri' }}</div>
                    <div class="go">Buka Activity Explorer →</div>
                </a>
            </div>
        @endforeach

        <button type="button" class="card add" onclick="document.getElementById('dlg').showModal()">
            <span class="plus">+</span>
            <b>Tambah project baru</b>
        </button>
    </div>

    <div class="arrow">↓</div>

    <section class="overview">
        <h3>Master Project Overview</h3>
        <p>Tahap berikutnya: melihat seluruh project, aktivitas Open, prioritas, PIC, hambatan, dan progres dari satu tempat.</p>
    </section>
</div>

<dialog id="dlg">
    <form method="POST" action="/projects">
        @csrf
        <h3>Tambah project</h3>
        <div class="f"><label>Nama project *</label><input name="name" required maxlength="100" placeholder="mis. Kakotora Line 5"></div>
        <div class="f"><label>Keterangan</label><input name="description" maxlength="200" placeholder="boleh dikosongkan"></div>
        <div class="dl">
            <button class="btn">Simpan</button>
            <button type="button" class="btn ghost" onclick="document.getElementById('dlg').close()">Batal</button>
        </div>
    </form>
</dialog>

</body>
</html>