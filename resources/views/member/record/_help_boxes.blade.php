{{-- Pilihan satu box untuk dibantu (radio). $boxes: [['area','box','parts']], $prefix: awalan id unik --}}
@foreach($boxes as $hb)
<div class="form-check">
    <input class="form-check-input" type="radio" name="box" id="{{ $prefix }}{{ $loop->index }}" value="{{ $hb['area'] }}|{{ $hb['box'] }}" required>
    <label class="form-check-label" for="{{ $prefix }}{{ $loop->index }}">{{ ucwords(str_replace('_', ' ', $hb['area'])) }} - Box {{ $hb['box'] }} ({{ $hb['parts'] }} part)</label>
</div>
@endforeach
