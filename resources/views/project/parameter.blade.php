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
            <tr><th>Prog</th><th>DC Code</th><th>Part No.</th><th>Model</th><th>TYPE</th><th>Prog timbangan</th><th class="n">Upper</th><th class="n">Lower</th><th>Standar</th><th>Status</th><th></th></tr>
            @forelse ($codes as $c)
                @php $s = $c->type_code ? $std->get($c->type_code) : null; @endphp
                <tr>
                    <td>{{ $c->prog_no }}</td>
                    <td><b>{{ $c->dc_code }}</b>@if (in_array($c->dc_code, $dupes)) <span class="co-b warn">dobel</span>@endif</td>
                    <td>{{ $c->part_number }}</td>
                    <td>{{ $c->model }}</td>
                    <td>{{ $c->type_code }}</td>
                    <td>{{ $c->scale_prog }}</td>
                    <td class="n">{{ $s ? $s->upper : '–' }}</td>
                    <td class="n">{{ $s ? $s->lower : '–' }}</td>
                    <td>
                        @if ($s)<span class="co-b ok">Ada</span>
                        @elseif (! $c->type_code)<span class="co-b warn">TYPE belum diisi</span>
                        @else<span class="co-b bad">Belum ada standar</span>@endif
                    </td>
                    <td>@if ($c->status)<span class="co-b info">{{ $c->status }}</span>@endif</td>
                    <td style="white-space:nowrap">
                        <a class="co-btn sm" href="{{ $base }}/dc/{{ $c->id }}/edit">Edit</a>
                        <form class="co-inline" method="POST" action="{{ $base }}/dc/{{ $c->id }}" onsubmit="return confirm('Hapus DC Code {{ $c->dc_code }}?')">
                            @csrf @method('DELETE')
                            <button class="co-btn sm red">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" style="text-align:center;color:#667085;padding:24px">Belum ada DC Code. Klik "+ Tambah DC Code".</td></tr>
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
                <tr>
                    <td><b>{{ $s->type_code }}</b></td>
                    <td class="n">{{ $s->nominal }}</td>
                    <td class="n">{{ $s->tolerance }}</td>
                    <td class="n">{{ $s->upper }}</td>
                    <td class="n">{{ $s->lower }}</td>
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
@endsection