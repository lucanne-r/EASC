@extends('layouts.project')
@section('title', 'Control Activity')

@section('content')
@php
  $slug = $project->slug;
  $pct = fn($n, $max) => $max ? max(2, round($n / $max * 100)) : 0;
  $maxMachine = max(1, $machines->max('n') ?? 1);
  $maxPrio = max(1, $prio->max() ?? 1);
  $maxCat = max(1, $cats->max() ?? 1);
  $maxSt = max(1, $stats['open'], $stats['progress'], $stats['hold'], $stats['closed']);
  $pc = ['P1' => '#c0675c', 'P2' => '#e0a53c', 'P3' => '#4a7a9b'];
  $badge = ['Open' => 'warn', 'In Progress' => 'info', 'Hold' => 'info', 'Closed' => 'ok', 'Delete' => 'bad'];
@endphp
<link rel="stylesheet" href="{{ asset('css/order.css') }}">
<style>
    .pm-tabs{display:flex;gap:4px;border-bottom:1px solid #dfe3ea;margin-bottom:14px}
    .pm-tabs span{padding:9px 14px;font-weight:600;color:#203a5f;border-bottom:2px solid #203a5f;margin-bottom:-1px}
    .ac-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:14px}
    .ac-card{background:#fff;border:1px solid #dfe3ea;border-radius:12px;padding:14px 16px}
    .ac-kpis span{color:#667085;font-size:13px} .ac-kpis b{display:block;font-size:28px;margin:6px 0 2px} .ac-kpis small{color:#667085;font-size:12px}
    .ac-card h4{margin:0 0 12px;font-size:15px}
    .ac-two{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin:14px 0}
    .ac-bar{display:flex;align-items:center;gap:12px;margin-bottom:10px}
    .ac-bar .l{width:150px;flex:none} .ac-bar .l small{display:block;color:#667085;font-size:11px}
    .ac-bar .t{flex:1;height:8px;background:#eceff3;border-radius:5px;overflow:hidden}
    .ac-bar .t i{display:block;height:100%;border-radius:5px}
    .ac-bar .n{width:34px;text-align:right;font-weight:700}
    .ac-pg{margin-top:14px} .ac-pg svg{width:16px;height:16px}
    details summary{cursor:pointer;color:#4a7a9b;font-size:12px;margin-top:3px}
    dialog{border:1px solid #dfe3ea;border-radius:12px;padding:20px;width:600px;max-width:95vw}
    dialog label{display:block;margin:10px 0 4px;font-weight:600;font-size:13px}
    dialog input,dialog select,dialog textarea{width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font:inherit}
    .ac-row{display:flex;gap:10px}.ac-row>*{flex:1}
    @media (max-width:900px){.ac-kpis{grid-template-columns:1fr 1fr}.ac-two{grid-template-columns:1fr}}
</style>

<h2>Control Activity</h2>
<p class="lead">Ringkasan dan daftar activity {{ $project->name }}.</p>

@if (session('ok'))<div class="co-flash ok">{{ session('ok') }}</div>@endif
@if ($errors->any())<div class="co-flash bad">@foreach ($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<div class="ac-kpis">
    <div class="ac-card"><span>Total Activity</span><b>{{ $stats['total'] }}</b><small>Termasuk history Closed</small></div>
    <div class="ac-card"><span>Open</span><b>{{ $stats['open'] }}</b><small>Belum mulai / belum selesai</small></div>
    <div class="ac-card"><span>In Progress + Hold</span><b>{{ $stats['progress'] + $stats['hold'] }}</b><small>{{ $stats['progress'] }} In Progress · {{ $stats['hold'] }} Hold</small></div>
    <div class="ac-card"><span>Closure Rate</span><b>{{ $stats['rate'] }}%</b><small>{{ $stats['closed'] }} dari {{ $stats['total'] }} aktivitas Closed</small></div>
</div>

<div class="ac-card">
    <h4>Status Overview</h4>
    @foreach ([['Open',$stats['open'],'#e0a53c'],['In Progress',$stats['progress'],'#4a7a9b'],['Hold',$stats['hold'],'#9ca3af'],['Closed',$stats['closed'],'#2f7d58']] as [$l,$n,$c])
        <div class="ac-bar"><div class="l">{{ $l }}</div><div class="t"><i style="width:{{ $n ? $pct($n,$maxSt) : 0 }}%;background:{{ $c }}"></i></div><div class="n">{{ $n }}</div></div>
    @endforeach
</div>

<div class="ac-two">
    <div class="ac-card">
        <h4>Aktivitas per Mesin / Area</h4>
        @foreach ($machines as $m)
            <div class="ac-bar"><div class="l">{{ $m->machine_area ?: 'Belum ditentukan' }}<small>Open {{ $m->open }} · Closed {{ $m->closed }}</small></div>
                <div class="t"><i style="width:{{ $pct($m->n,$maxMachine) }}%;background:#4a7a9b"></i></div><div class="n">{{ $m->n }}</div></div>
        @endforeach
    </div>
    <div class="ac-card">
        <h4>Open per Priority</h4>
        @foreach (['P1','P2','P3'] as $p)
            <div class="ac-bar"><div class="l">{{ $p }}</div><div class="t"><i style="width:{{ ($prio[$p] ?? 0) ? $pct($prio[$p],$maxPrio) : 0 }}%;background:{{ $pc[$p] }}"></i></div><div class="n">{{ $prio[$p] ?? 0 }}</div></div>
        @endforeach
        @php $blank = ($prio[''] ?? 0) + ($prio['—'] ?? 0); @endphp
        <div class="ac-bar"><div class="l">Belum ditentukan</div><div class="t"><i style="width:{{ $blank ? $pct($blank,$maxPrio) : 0 }}%;background:#4a7a9b"></i></div><div class="n">{{ $blank }}</div></div>
        <h4 style="margin-top:16px">Open per Category</h4>
        @foreach ($cats as $name => $n)
            <div class="ac-bar"><div class="l">{{ $name }}</div><div class="t"><i style="width:{{ $pct($n,$maxCat) }}%;background:#2f7d58"></i></div><div class="n">{{ $n }}</div></div>
        @endforeach
    </div>
</div>

<div class="pm-tabs"><span>Activity ({{ $activities->count() }})</span></div>

<form class="co-tools" method="GET" action="{{ route('activity.index', $slug) }}">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari ID, activity, PIC...">
    <select name="status"><option value="">Semua status</option>@foreach (\App\Models\Activity::STATUSES as $s)<option {{ request('status')==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select>
    <select name="priority"><option value="">Semua priority</option>@foreach (\App\Models\Activity::PRIORITIES as $s)<option {{ request('priority')==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select>
    <select name="category"><option value="">Semua kategori</option>@foreach (\App\Models\Activity::CATEGORIES as $s)<option {{ request('category')==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select>
    <select name="machine"><option value="">Semua mesin</option>@foreach (array_keys(\App\Models\Activity::MACHINES) as $s)<option {{ request('machine')==$s?'selected':'' }}>{{ $s }}</option>@endforeach<option {{ request('machine')=='Other'?'selected':'' }}>Other</option></select>
    <select name="pic"><option value="">Semua PIC</option>@foreach ($pics as $s)<option {{ request('pic')==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select>
    <select name="order"><option value="">Semua urutan</option><option value="assigned" {{ request('order')=='assigned'?'selected':'' }}>Sudah diurutkan</option><option value="unassigned" {{ request('order')=='unassigned'?'selected':'' }}>Belum diurutkan</option></select>
    <input type="date" name="due_from" value="{{ request('due_from') }}" title="Due date dari">
    <input type="date" name="due_to" value="{{ request('due_to') }}" title="Due date sampai">
    <button class="co-btn ghost">Terapkan</button>
    <a class="co-btn ghost" href="{{ route('activity.index', $slug) }}">Reset</a>
    <a class="co-btn ghost" href="{{ route('activity.export', $slug) }}">Export CSV</a>
    <span class="co-sp"></span>
    <button type="button" class="co-btn" onclick="openForm()">+ Tambah Activity</button>
</form>

<div class="co-wrap">
    <table>
        <tr><th>ID</th><th>Mesin / Area</th><th>Category</th><th>Priority</th><th>Activity</th><th>PIC</th><th>Status</th><th></th></tr>
        @forelse ($activities as $a)
            <tr>
                <td><b>{{ $a->code }}</b></td>
                <td>{{ $a->machine_area }}</td>
                <td>{{ $a->category }}</td>
                <td>{{ $a->priority }}</td>
                <td>{{ $a->activity }}
                    @if ($a->attachments->count())
                        <details><summary>📎 {{ $a->attachments->count() }} lampiran</summary>
                            @foreach ($a->attachments as $f)
                                <div><a href="{{ route('activity.download', [$slug, $f->id]) }}" target="_blank" style="color:#4a7a9b">{{ $f->original_name }}</a>
                                <form class="co-inline" method="POST" action="{{ route('activity.attachment.delete', [$slug, $f->id]) }}" onsubmit="return confirm('Hapus lampiran?')">@csrf @method('DELETE')<button class="co-btn sm red">hapus</button></form></div>
                            @endforeach
                        </details>
                    @endif
                </td>
                <td>{{ $a->pic }}</td>
                <td><span class="co-b {{ $badge[$a->status] ?? 'info' }}">{{ $a->status }}</span></td>
                <td style="white-space:nowrap">
                    <button type="button" class="co-btn sm" onclick='openForm(@json($a))'>Edit</button>
                    @if (! in_array($a->status, ['Closed','Delete']))
                        <form class="co-inline" method="POST" action="{{ route('activity.close', [$slug, $a->id]) }}">@csrf<button class="co-btn sm" style="background:#dcfce7;color:#166534">Close</button></form>
                    @endif
                    @if ($a->status !== 'Delete')
                        <form class="co-inline" method="POST" action="{{ route('activity.delete', [$slug, $a->id]) }}" onsubmit="return confirm('Hapus {{ $a->code }}?')">@csrf<button class="co-btn sm red">Hapus</button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8" style="text-align:center;color:#667085;padding:24px">Belum ada activity. Klik "+ Tambah Activity".</td></tr>
        @endforelse
    </table>
</div>

<dialog id="dlg">
    <form id="f" method="POST" enctype="multipart/form-data" action="{{ route('activity.store', $slug) }}">
        @csrf <span id="m"></span>
        <h3 id="ttl" style="margin:0 0 6px;font-size:16px">Tambah Activity</h3>
        <div class="ac-row">
            <div><label>Mesin / Area</label>
                <select name="machine_area" id="machine_area" onchange="autoId()"><option value=""></option>
                    @foreach (array_keys(\App\Models\Activity::MACHINES) as $s)<option>{{ $s }}</option>@endforeach<option>Other</option></select></div>
            <div><label>Activity ID</label><input name="code" id="code" placeholder="otomatis" required></div>
        </div>
        <div class="ac-row">
            <div><label>Priority</label><select name="priority" id="priority"><option value=""></option>@foreach (\App\Models\Activity::PRIORITIES as $s)<option>{{ $s }}</option>@endforeach</select></div>
            <div><label>Kategori</label><select name="category" id="category"><option value=""></option>@foreach (\App\Models\Activity::CATEGORIES as $s)<option>{{ $s }}</option>@endforeach</select></div>
        </div>
        <label>Activity</label><textarea name="activity" id="activity" rows="3" required></textarea>
        <div class="ac-row">
            <div><label>PIC</label><input name="pic" id="pic"></div>
            <div><label>Due date</label><input type="date" name="due_date" id="due_date"></div>
            <div><label>Urutan</label><input type="number" min="0" name="work_order" id="work_order" value="0"></div>
        </div>
        <label>Status</label><select name="status" id="status">@foreach (\App\Models\Activity::STATUSES as $s)<option>{{ $s }}</option>@endforeach</select>
        <label>Lampiran (boleh lebih dari satu)</label><input type="file" name="attachments[]" multiple>
        <div style="margin-top:16px;text-align:right">
            <button type="button" class="co-btn ghost" onclick="dlg.close()">Batal</button>
            <button class="co-btn">Simpan</button>
        </div>
    </form>
</dialog>

<script>
const dlg = document.getElementById('dlg'), f = document.getElementById('f');
const base = @json(route('activity.index', $slug));
function openForm(a){
    f.reset();
    const code = document.getElementById('code');
    if (a) {
        document.getElementById('ttl').textContent = 'Edit ' + a.code;
        f.action = base + '/' + a.id;
        document.getElementById('m').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        code.value = a.code; code.readOnly = true;
        ['machine_area','priority','category','activity','pic','due_date','work_order','status'].forEach(k => document.getElementById(k).value = a[k] ?? '');
    } else {
        document.getElementById('ttl').textContent = 'Tambah Activity';
        f.action = @json(route('activity.store', $slug));
        document.getElementById('m').innerHTML = ''; code.readOnly = false;
    }
    dlg.showModal();
}
async function autoId(){
    const code = document.getElementById('code'); if (code.readOnly) return;
    const m = document.getElementById('machine_area').value; if (!m) return;
    const r = await fetch(base + '/next-id?machine=' + encodeURIComponent(m));
    const j = await r.json();
    if (j.id) code.value = j.id; else { code.value = ''; alert(j.error); }
}
</script>
@endsection