@extends('layouts.project')
@section('title', $order->exists ? 'Edit PR' : 'Tambah PR')

@section('content')
@php $base = '/p/' . $project->slug . '/control-order'; @endphp
<link rel="stylesheet" href="{{ asset('css/order.css') }}">

<h2>{{ $order->exists ? 'Edit PR' : 'Tambah PR' }}</h2>
<p class="lead">{{ $project->name }}</p>

@if ($errors->any())<div class="co-flash bad">@foreach ($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<form class="co-form" method="POST" action="{{ $order->exists ? $base . '/' . $order->id : $base }}">
    @csrf
    @if ($order->exists) @method('PUT') @endif

    <div class="co-row">
        <div class="co-f"><label>Bulan *</label><input type="month" name="period" value="{{ old('period', $order->period) }}" required></div>
        <div class="co-f"><label>Nomor urut di bulan itu</label><input type="number" name="item_no" min="1" value="{{ old('item_no', $order->item_no) }}"></div>
    </div>

    <div class="co-row">
        <div class="co-f"><label>No. PR</label><input type="text" name="pr_no" value="{{ old('pr_no', $order->pr_no) }}" placeholder="T6214DK011"></div>
        <div class="co-f">
            <label>RFI (dibebankan ke budget mana)</label>
            <select name="rfi_id">
                <option value="">Belum ditentukan</option>
                @foreach ($rfis as $r)
                    <option value="{{ $r->id }}" {{ (string) old('rfi_id', $order->rfi_id) === (string) $r->id ? 'selected' : '' }}>{{ $r->rfi_no }} {{ $r->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="co-f"><label>Deskripsi *</label><input type="text" name="description" value="{{ old('description', $order->description) }}" required></div>
    <div class="co-f"><label>Nilai (Rp), angka saja tanpa titik</label><input type="number" name="value" min="0" value="{{ old('value', $order->value) }}"></div>

    <div class="co-f">
        <label>Approval</label>
        <div class="co-checks">
            @foreach (\App\Models\Order::APPROVERS as $n)
                <label><input type="checkbox" name="approvals[]" value="{{ $n }}" {{ in_array($n, old('approvals', $order->approvals ?? [])) ? 'checked' : '' }}> {{ $n }}</label>
            @endforeach
        </div>
    </div>

    <div class="co-row">
        <div class="co-f">
            <label>PO</label>
            <div class="co-checks"><label><input type="checkbox" name="po_issued" value="1" {{ old('po_issued', $order->po_issued) ? 'checked' : '' }}> PO sudah terbit</label></div>
        </div>
        <div class="co-f"><label>No. PO</label><input type="text" name="po_no" value="{{ old('po_no', $order->po_no) }}"></div>
    </div>

    <div class="co-f">
        <div class="co-checks"><label><input type="checkbox" name="is_closed" value="1" {{ old('is_closed', $order->is_closed) ? 'checked' : '' }}> Close (selesai)</label></div>
    </div>

    <div class="co-f"><label>Remark</label><textarea name="remark" rows="2">{{ old('remark', $order->remark) }}</textarea></div>

    <div class="co-actions">
        <button class="co-btn">Simpan</button>
        <a class="co-btn ghost" href="{{ $base }}">Batal</a>
    </div>
</form>
@endsection