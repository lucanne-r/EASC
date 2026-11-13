@extends('layouts.project')
@section('title', $std->exists ? 'Edit Standar' : 'Tambah Standar')

@section('content')
@php $base = '/p/' . $project->slug . '/parameter'; @endphp
<link rel="stylesheet" href="{{ asset('css/order.css') }}">

<h2>{{ $std->exists ? 'Edit standar timbangan' : 'Tambah standar timbangan' }}</h2>
<p class="lead">{{ $project->name }}</p>

@if ($errors->any())<div class="co-flash bad">@foreach ($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif

<form class="co-form" method="POST" action="{{ $std->exists ? $base . '/standar/' . $std->id : $base . '/standar' }}">
    @csrf
    @if ($std->exists) @method('PUT') @endif

    <div class="co-f">
        <label>TYPE *</label>
        <input type="text" name="type_code" value="{{ old('type_code', $std->type_code ?: request('type')) }}" {{ $std->exists ? 'readonly' : 'required' }} placeholder="mis. 10SA13">
    </div>

    <div class="co-row">
        <div class="co-f"><label>Nominal (berat standar) *</label><input type="number" id="nominal" name="nominal" min="1" value="{{ old('nominal', $std->nominal) }}" required></div>
        <div class="co-f"><label>Toleransi (±) *</label><input type="number" id="tolerance" name="tolerance" min="0" value="{{ old('tolerance', $std->tolerance) }}" required></div>
    </div>

    <p class="lead">Hasil: <b id="preview">-</b></p>

    <div class="co-actions">
        <button class="co-btn">Simpan</button>
        <a class="co-btn ghost" href="{{ $base }}?tab=std">Batal</a>
    </div>
</form>

<script>
(function () {
    var n = document.getElementById('nominal'), t = document.getElementById('tolerance'), p = document.getElementById('preview');
    function u() {
        var a = parseInt(n.value), b = parseInt(t.value);
        p.textContent = (isNaN(a) || isNaN(b)) ? '-' : 'Lower ' + (a - b) + '  ·  Upper ' + (a + b);
    }
    n.addEventListener('input', u); t.addEventListener('input', u); u();
})();
</script>
@endsection