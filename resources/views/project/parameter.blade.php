@extends('layouts.project')
@section('title', 'Parameter')

@section('content')
@php $base = '/p/' . $project->slug . '/parameter'; @endphp
<link rel="stylesheet" href="{{ asset('css/order.css') }}">
<style>
    .pm-tabs{display:flex;gap:4px;border-bottom:1px solid #dfe3ea;margin-bottom:14px}
    .pm-tabs a{padding:9px 14px;font-weight:600;color:#667085;border-bottom:2px solid transparent;margin-bottom:-1px}
    .pm-tabs a.on{color:#203a5f;border-color:#203a5f}
    .pm-chip{display:inline-block;background:#fde8e6;color:#b42318;border-radius:8px;padding:5px 10px;font-weight:600;font-size:13px;margin:0 6px 6px 0}
</style>

<h2>Parameter</h2>
<p class="lead">DC Code dan standar timbangan {{ $project->name }}. Standar diambil otomatis dari TYPE.</p>

@if (session('ok'))<div class="co-flash ok">{{ session('ok') }}</div>@endif
@if ($errors->any())<div class="co-flash bad">@foreach ($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<div class="pm-tabs">
    <a class="{{ $tab === 'dc' ? 'on' : '' }}" href="{{ $base }}">DC Code ({{ $totalDc }})</a>
    <a class="{{ $tab === 'std' ? 'on' : '' }}" href="{{ $base }}?tab=std">Standar Timbangan ({{ $standards->count() }})</a>
</div>

@if ($noStdCount > 0)
    <div class="co-flash bad">{{ $noStdCount }} DC Code belum punya standar timbangan. Lihat daftarnya di tab Standar Timbangan, atau klik "Tanpa standar" di filter DC Code.</div>
@endif

@if ($tab === 'dc')
    <form class="co-tools" method="GET" action="{{ $base }}">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari DC Code, model, part number...">
        <select name="type">
            <option value="">Semua TYPE</option>
            <option value="none" {{ request('type') === 'none' ? 'selected' : '' }}>TYPE belum diisi</option>
            @foreach ($types as $t)<option {{ request('type') === $t ? 'selected' : '' }}>{{ $t }}</option>@endforeach
        </select>
        <select name="filter">
            <option value="">Semua standar</option>
            <option value="nostd" {{ request('filter') === 'nostd' ? 'selected' : '' }}>Tanpa standar</option>
        </select>
        <button class="co-btn ghost">Terapkan</button>
        <span class="co-sp"></span>
        <a class="co-btn" href="{{ $base }}/dc/create">+ Tambah DC Code</a>
    </form>

    <div class="co-wrap">
        <table>
            <tr><th>Prog</th><th>DC Code</th><th>Part No.</th><th>File Barcode</th><th>Model</th><th>TYPE</th><th>Prog timbangan</th><th class="n">Upper</th><th class="n">Lower</th><th>Standar</th><th>Status</th><th></th></tr>
            @forelse ($codes as $c)
                @php
    $s = $c->type_code ? $std->get($c->type_code) : null;
    $u = $base . '/dc/' . $c->id . '/inline';
@endphp
                <tr>
                    <td class="ed" data-field="prog_no" data-type="number" data-url="{{ $u }}" data-value="{{ $c->prog_no }}">{{ $c->prog_no }}</td>
                   <td class="ed" data-field="dc_code" data-reload="1" data-url="{{ $u }}" data-value="{{ $c->dc_code }}"><b>{{ $c->dc_code }}</b>@if (in_array($c->dc_code, $dupes)) <span class="co-b warn">dobel</span>@endif</td>
                    <td class="ed" data-field="part_number" data-url="{{ $u }}" data-value="{{ $c->part_number }}">{{ $c->part_number }}</td>
                    <td style="min-width:230px">
                        @if (isset($barcodes[$c->id]))
                            <a class="co-b info" href="{{ $base }}/dc/{{ $c->id }}/barcode" target="_blank">{{ \Illuminate\Support\Str::limit($barcodes[$c->id], 22) }}</a>
                            <form class="co-inline" method="POST" action="{{ $base }}/dc/{{ $c->id }}/barcode" onsubmit="return confirm('Hapus file barcode ini?')">
                                @csrf @method('DELETE')
                                <button class="co-btn sm red">Hapus</button>
                            </form>
                        @else
                            <form class="co-inline" method="POST" enctype="multipart/form-data" action="{{ $base }}/dc/{{ $c->id }}/barcode">
                                @csrf
                                <input type="file" name="barcode_file" accept="image/png,image/jpeg,image/gif,image/webp,application/pdf" required style="max-width:160px;font-size:12px">
                                <button class="co-btn sm">Unggah</button>
                            </form>
                        @endif
                    </td>
                    <td class="ed" data-field="model" data-url="{{ $u }}" data-value="{{ $c->model }}">{{ $c->model }}</td>
                    <td class="ed" data-field="type_code" data-reload="1" data-url="{{ $u }}" data-value="{{ $c->type_code }}">{{ $c->type_code }}</td>
                    <td class="ed" data-field="scale_prog" data-type="number" data-url="{{ $u }}" data-value="{{ $c->scale_prog }}">{{ $c->scale_prog }}</td>
                    <td class="n">{{ $s ? $s->upper : '–' }}</td>
                    <td class="n">{{ $s ? $s->lower : '–' }}</td>
                    <td>
                        @if ($s)<span class="co-b ok">Ada</span>
                        @elseif (! $c->type_code)<span class="co-b warn">TYPE belum diisi</span>
                        @else<span class="co-b bad">Belum ada standar</span>@endif
                    </td>
                   <td class="ed" data-field="status" data-type="status" data-reload="1" data-url="{{ $u }}" data-value="{{ $c->status }}">@if ($c->status)<span class="co-b info">{{ $c->status }}</span>@endif</td>
                    <td style="white-space:nowrap">
                        <a class="co-btn sm" href="{{ $base }}/dc/{{ $c->id }}/edit">Edit</a>
                        <form class="co-inline" method="POST" action="{{ $base }}/dc/{{ $c->id }}" onsubmit="return confirm('Hapus DC Code {{ $c->dc_code }}?')">
                            @csrf @method('DELETE')
                            <button class="co-btn sm red">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="12" style="text-align:center;color:#667085;padding:24px">Belum ada DC Code. Klik "+ Tambah DC Code".</td></tr>
            @endforelse
        </table>
    </div>
@else
    @if ($missing->count())
        <div class="co-title">Perlu dilengkapi</div>
        <p class="lead" style="margin-bottom:8px">TYPE ini dipakai DC Code tetapi belum punya standar. Klik untuk mengisinya.</p>
        <div style="margin-bottom:14px">
            @foreach ($missing as $t => $n)
                <a class="pm-chip" href="{{ $base }}/standar/create?type={{ urlencode($t) }}">{{ $t }} · {{ $n }} DC Code</a>
            @endforeach
        </div>
    @endif

    <div class="co-tools"><div class="co-title" style="margin:0">Daftar standar timbangan</div><span class="co-sp"></span>
        <a class="co-btn" href="{{ $base }}/standar/create">+ Tambah standar</a></div>

    <div class="co-wrap">
        <table>
            <tr><th>TYPE</th><th class="n">Nominal</th><th class="n">Toleransi (±)</th><th class="n">Upper</th><th class="n">Lower</th><th class="n">Dipakai DC Code</th><th></th></tr>
            @forelse ($standards as $s)
    @php $u = $base . '/standar/' . $s->id . '/inline'; @endphp
                <tr>
                    <td><b>{{ $s->type_code }}</b></td>
                   <td class="n ed" data-field="nominal" data-type="number" data-url="{{ $u }}" data-value="{{ $s->nominal }}">{{ $s->nominal }}</td>
                   <td class="n ed" data-field="tolerance" data-type="number" data-url="{{ $u }}" data-value="{{ $s->tolerance }}">{{ $s->tolerance }}</td>
                    <td class="n js-upper">{{ $s->upper }}</td>
                    <td class="n js-lower">{{ $s->lower }}</td>
                    <td class="n">{{ $usage[$s->type_code] ?? 0 }}</td>
                    <td style="white-space:nowrap">
                        <a class="co-btn sm" href="{{ $base }}/standar/{{ $s->id }}/edit">Edit</a>
                        <form class="co-inline" method="POST" action="{{ $base }}/standar/{{ $s->id }}" onsubmit="return confirm('Hapus standar {{ $s->type_code }}?')">
                            @csrf @method('DELETE')
                            <button class="co-btn sm red">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:#667085;padding:24px">Belum ada standar timbangan.</td></tr>
            @endforelse
        </table>
    </div>
@endif
<style>
    td.ed{cursor:text}
    td.ed:hover{background:#fbf3f4;outline:1px dashed #c9a3ab;outline-offset:-1px}
    td.ed.editing{padding:4px}
    td.ed.ok{background:#e3f4e8;transition:background .6s}
    .ed-box{width:100%;min-width:70px;padding:6px 8px;border:2px solid #6d1a2b;border-radius:6px;font:inherit;background:#fff}
</style>
<div id="pm-data" hidden data-csrf="{{ csrf_token() }}" data-statuses='@json(\App\Models\DcCode::STATUSES)'></div>
<script>
(function () {
    var pm = document.getElementById('pm-data');
    var CSRF = pm.dataset.csrf;
    var STATUSES = JSON.parse(pm.dataset.statuses);

    document.addEventListener('click', function (e) {
        var td = e.target.closest('td.ed');
        if (td && !td.classList.contains('editing')) startEdit(td);
    });

    function startEdit(td) {
        var type = td.dataset.type || 'text';
        var old = td.dataset.value || '';
        var original = td.innerHTML;
        var box;

        if (type === 'status') {
            box = document.createElement('select');
            box.innerHTML = '<option value="">Normal</option>' + STATUSES.map(function (s) {
                return '<option' + (s === old ? ' selected' : '') + '>' + s + '</option>';
            }).join('');
        } else {
            box = document.createElement('input');
            box.type = (type === 'number') ? 'number' : 'text';
            if (type === 'number') box.min = 0;
            box.value = old;
        }
        box.className = 'ed-box';

        td.classList.add('editing');
        td.innerHTML = '';
        td.appendChild(box);
        box.focus();
        if (box.select) box.select();

        var done = false;
        function cancel() {
            if (done) return;
            done = true;
            td.classList.remove('editing');
            td.innerHTML = original;
        }
        function save() {
            if (done) return;
            var val = box.value.trim();
            if (val === old) { cancel(); return; }
            done = true;
            td.classList.remove('editing');
            td.innerHTML = '...';

            fetch(td.dataset.url, {
                method: 'PATCH',
                headers: {'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json'},
                body: JSON.stringify({field: td.dataset.field, value: val})
            })
            .then(function (r) { return r.json().then(function (j) { return {ok: r.ok, j: j}; }); })
            .then(function (res) {
                if (!res.ok) {
                    td.innerHTML = original;
                    var msg = res.j.message || 'Gagal menyimpan.';
                    if (res.j.errors) msg = Object.values(res.j.errors)[0][0];
                    alert(msg);
                    return;
                }
                if (td.dataset.reload) { location.reload(); return; }

                var v = (res.j.value === null || res.j.value === undefined) ? '' : res.j.value;
                td.dataset.value = v;
                td.textContent = v;
                var tr = td.closest('tr');
                if (res.j.upper !== undefined) tr.querySelector('.js-upper').textContent = res.j.upper;
                if (res.j.lower !== undefined) tr.querySelector('.js-lower').textContent = res.j.lower;
                td.classList.add('ok');
                setTimeout(function () { td.classList.remove('ok'); }, 700);
            })
            .catch(function () { td.innerHTML = original; alert('Gagal menyimpan. Cek koneksi atau coba lagi.'); });
        }

        box.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); save(); }
            if (e.key === 'Escape') cancel();
        });
        box.addEventListener('blur', save);
        if (type === 'status') box.addEventListener('change', save);
    }
})();
</script>
@endsection