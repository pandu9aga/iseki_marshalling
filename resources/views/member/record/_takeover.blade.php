@foreach($items as $t)
<form action="{{ route('member.record.takeover', $t['id']) }}" method="POST" class="{{ $loop->last ? '' : 'mb-3' }}">
    @csrf
    <div>
        <strong>{{ $t['seq'] }}</strong> | {{ $t['type'] }} | {{ ucwords(str_replace('_', ' ', $t['area'])) }}
        <br><small class="text-muted">{{ $t['nama'] }}: {{ $t['reason'] }} | {{ $t['since'] }} | Sisa {{ $t['remaining'] }} part</small>
    </div>
    @foreach($t['boxes'] as $tb)
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="boxes[]" id="tkBox{{ $t['id'] }}_{{ $loop->index }}" value="{{ $tb['area'] }}|{{ $tb['box'] }}">
        <label class="form-check-label" for="tkBox{{ $t['id'] }}_{{ $loop->index }}">{{ ucwords(str_replace('_', ' ', $tb['area'])) }} - Box {{ $tb['box'] }} ({{ $tb['parts'] }} part)</label>
    </div>
    @endforeach
    <button type="submit" class="btn btn-success mt-2">Ambil Alih</button>
</form>
@endforeach
