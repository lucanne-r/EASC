@extends('layouts.project')
@section('title', $dc->exists ? 'Edit DC Code' : 'Tambah DC Code')

@section('content')
@php $base = '/p/' . $project->slug . '/parameter'; @endphp
<link rel="stylesheet" href="{{ asset('css/order.css') }}">

<h2>{{ $dc->exists ? 'Edit DC Code' : 'Tambah DC Code' }}</h2>
<p class="lead">{{ $project->name }}</p>

@if ($errors->any())<div class="co-flash bad">@foreach ($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<form class="co-form" method="POST" action="{{ $dc->exists ? $base . '/dc/' . $dc->id : $base . '/dc' }}">
    @csrf
    @if ($dc->exists) @method('PUT') @endif

    <div class="co-row">
        <div class="co-f"><label>Prog No.</label><input type="number" name="prog_no" min="0" value="{{ old('prog_no', $dc->prog_no) }}"></div>
        <div class="co-f"><label>DC Code *</label><input type="text" name="dc_code" value="{{ old('dc_code', $dc->dc_code) }}" required></div>
    </div>

    <div class="co-row">
        <div class="co-f"><label>Part Number</label><input type="text" name="part_number" value="{{ old('part_number', $dc->part_number) }}"></div>
        <div class="co-f"><label>Model</label><input type="text" name="model" value="{{ old('model', $dc->model) }}"></div>
    </div>

    <div class="co-row">
        <div class="co-f">
            <label>TYPE (menentukan standar timbangan)</label>
            <input type="text" name="type_code" list="types" autocomplete="off" value="{{ old('type_code', $dc->type_code) }}">
            <datalist id="types">@foreach ($types as $t)<option value="{{ $t }}">@endforeach</datalist>
        </div>
        <div class="co-f"><label>Prog No. timbangan</label><input type="number" name="scale_prog" min="0" value="{{ old('scale_prog', $dc->scale_prog) }}"></div>
    </div>

    <div class="co-f">
        <label>Status</label>
        <select name="status">
            <option value="">Normal</option>
            @foreach (\App\Models\DcCode::STATUSES as $s)
                <option {{ old('status', $dc->status) === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
    </div>

    <div class="co-f"><label>Remark</label><input type="text" name="remark" value="{{ old('remark', $dc->remark) }}"></div>

    <div class="co-actions">
        <button class="co-btn">Simpan</button>
        <a class="co-btn ghost" href="{{ $base }}">Batal</a>
    </div>
</form>
@endsection