@extends('layouts.project')
@section('title', 'Control Order')

@section('content')
@php
    $rp = fn ($v) => 'Rp ' . number_format((int) $v, 0, ',', '.');
    $bulan = fn ($p) => \Carbon\Carbon::parse($p . '-01')->locale('id')->translatedFormat('F Y');
    $base = '/p/' . $project->slug . '/control-order';
@endphp
<link rel="stylesheet" href="{{ asset('css/order.css') }}">

<h2>Control Order</h2>
<p class="lead">Budget RFI dan daftar PR untuk {{ $project->name }}. Total PP dan sisa budget dihitung otomatis.</p>

@if (session('ok'))<div class="co-flash ok">{{ session('ok') }}</div>@endif
@if ($errors->any())<div class="co-flash bad">@foreach ($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<div class="co-title">Budget RFI</div>
<div class="co-wrap">
    <table>
        <tr><th>RFI</th><th>Nama</th><th>Budget</th><th class="n">Total PP</th><th class="n">Sisa budget</th><th>Pemakaian</th><th></th></tr>
        @forelse ($rfis as $r)
            @php
                $used = (int) $r->orders_sum_value;
                $left = $r->budget - $used;
                $pct = $r->budget ? min(100, round($used / $r->budget * 100)) : 0;
            @endphp
            <tr>
                <td><b>{{ $r->rfi_no }}</b></td>
                <td>{{ $r->name }}</td>
                <td>
                    <form class="co-inline" method="POST" action="{{ $base }}/rfi/{{ $r->id }}">
                        @csrf @method('PUT')
                        <input class="co-in" type="number" name="budget" min="0" value="{{ $r->budget }}">
                        <button class="co-btn sm">Simpan</button>
                    </form>
                </td>
                <td class="n">{{ $rp($used) }}</td>
                <td class="n"><span class="co-b {{ $left < 0 ? 'bad' : 'ok' }}">{{ $rp($left) }}</span></td>
                <td><span class="co-bar"><i style="width:{{ $pct }}%"></i></span> {{ $pct }}%</td>
                <td>
                    <form method="POST" action="{{ $base }}/rfi/{{ $r->id }}" onsubmit="return confirm('Hapus RFI {{ $r->rfi_no }}?')">
                        @csrf @method('DELETE')
                        <button class="co-btn sm red">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" style="text-align:center;color:#667085;padding:20px">Belum ada RFI. Tambahkan RFI dulu di bawah.</td></tr>
        @endforelse
        @if ($noRfiTotal > 0)
            <tr><td colspan="3"><i>PR yang belum ditentukan RFI-nya</i></td><td class="n">{{ $rp($noRfiTotal) }}</td><td colspan="3"></td></tr>
        @endif
    </table>
</div>

<details class="co-details">
    <summary>+ Tambah RFI</summary>
    <form class="co-form" style="max-width:none;border:0;padding:0;background:none" method="POST" action="{{ $base }}/rfi">
        @csrf
        <input type="text" name="rfi_no" placeholder="No. RFI, mis. 25-064" required>
        <input type="text" name="name" placeholder="Nama, mis. KAKOTORA">
        <input type="number" name="budget" min="0" placeholder="Budget (Rp)" required>
        <button class="co-btn">Simpan RFI</button>
    </form>
</details>

<div class="co-title">Daftar PR</div>
<form class="co-tools" method="GET">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nomor PR atau deskripsi...">
    <select name="period">
        <option value="">Semua bulan</option>
        @foreach ($periods as $p)<option value="{{ $p }}" {{ request('period') === $p ? 'selected' : '' }}>{{ $bulan($p) }}</option>@endforeach
    </select>
    <select name="rfi">
        <option value="">Semua RFI</option>
        <option value="none" {{ request('rfi') === 'none' ? 'selected' : '' }}>Belum ada RFI</option>
        @foreach ($rfis as $r)<option value="{{ $r->id }}" {{ (string) request('rfi') === (string) $r->id ? 'selected' : '' }}>{{ $r->rfi_no }}</option>@endforeach
    </select>
    <select name="stage">
        <option value="">Semua status</option>
        @foreach (['Open', 'PO', 'Close'] as $s)<option {{ request('stage') === $s ? 'selected' : '' }}>{{ $s }}</option>@endforeach
    </select>
    <button class="co-btn ghost">Terapkan</button>
    <span class="co-sp"></span>
    <a class="co-btn" href="{{ $base }}/create">+ Tambah PR</a>
</form>

@forelse ($orders as $period => $items)
    <div class="co-month">{{ $bulan($period) }} <span>{{ $items->count() }} PR · {{ $rp($items->sum('value')) }}</span></div>
    <div class="co-wrap">
        <table>
            <tr><th>No</th><th>PR</th><th>Deskripsi</th><th>RFI</th><th class="n">Nilai</th><th>Approval</th><th>PO</th><th>Status</th><th>Remark</th><th></th></tr>
            @foreach ($items as $o)
                <tr>
                    <td>{{ $o->item_no }}</td>
                    <td>
                        @if ($o->pr_no)
                            <b>{{ $o->pr_no }}</b>@if (in_array($o->pr_no, $dupes)) <span class="co-b warn">dobel</span>@endif
                        @else
                            <span class="co-b">belum ada PR</span>
                        @endif
                    </td>
                    <td class="w">{{ $o->description }}</td>
                    <td>{{ $o->rfi?->rfi_no }}</td>
                    <td class="n">{{ $o->value !== null ? $rp($o->value) : '–' }}</td>
                    <td>
                        @foreach (\App\Models\Order::APPROVERS as $n)
                            <span class="co-dot {{ in_array($n, $o->approvals ?? []) ? 'on' : '' }}" title="{{ $n }}">{{ substr($n, 0, 1) }}</span>
                        @endforeach
                    </td>
                    <td>@if ($o->po_issued)<span class="co-b info">PO{{ $o->po_no ? ' ' . $o->po_no : '' }}</span>@else–@endif</td>
                    <td>@if ($o->is_closed)<span class="co-b ok">Close</span>@else<span class="co-b warn">Open</span>@endif</td>
                    <td class="w">{{ $o->remark }}</td>
                    <td style="white-space:nowrap">
                        <a class="co-btn sm" href="{{ $base }}/{{ $o->id }}/edit">Edit</a>
                        <form class="co-inline" method="POST" action="{{ $base }}/{{ $o->id }}" onsubmit="return confirm('Hapus PR ini?')">
                            @csrf @method('DELETE')
                            <button class="co-btn sm red">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@empty
    <div class="co-wrap"><div style="padding:30px;text-align:center;color:#667085">Belum ada PR. Klik "+ Tambah PR".</div></div>
@endforelse
@endsection