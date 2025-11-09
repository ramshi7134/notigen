@extends('notigen::layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="float-left">View Template: {{ $template->name }}</h3>
                    <div class="float-right">
                        <a href="{{ route('notigen.edit', $template->id) }}" class="btn btn-primary">Edit Template</a>
                        <a href="{{ route('notigen.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5>Template Key</h5>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $template->template_key }}" readonly>
                            <button class="btn btn-outline-secondary copy-key" type="button" data-key="{{ $template->template_key }}">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                        <small class="text-muted">Use this key to reference this template in your code.</small>
                    </div>
                    
                    <div class="mb-4">
                        <h5>Description</h5>
                        <p>{{ $template->description ?: 'No description provided.' }}</p>
                    </div>

                    <div class="mb-4">
                        <h5>Channels</h5>
                        <div>
                            @foreach($template->channels as $channel)
                                <span class="badge bg-info">{{ $channel }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Template Content</h5>
                        <pre class="border p-3 bg-light"><code>{{ $template->content }}</code></pre>
                    </div>

                    @if($template->variables)
                    <div class="mb-4">
                        <h5>Variables</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($template->variables as $name => $variable)
                                <tr>
                                    <td><code>{{{ $name }}}</code></td>
                                    <td>{{ $variable['description'] ?? 'No description' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    <div class="mb-4">
                        <h5>Preview</h5>
                        <form id="previewForm" class="mb-3">
                            @foreach($template->variables as $name => $variable)
                            <div class="mb-3">
                                <label class="form-label">{{ $name }}</label>
                                <input type="text" class="form-control preview-variable" 
                                       data-name="{{ $name }}" 
                                       placeholder="{{ $variable['description'] ?? '' }}">
                            </div>
                            @endforeach
                            <button type="submit" class="btn btn-primary">Preview</button>
                        </form>
                        <div id="previewResult" class="border p-3 bg-light" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('previewForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const variables = {};
    document.querySelectorAll('.preview-variable').forEach(input => {
        variables[input.dataset.name] = input.value;
    });

    fetch('{{ route("notigen.preview", $template->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ variables })
    })
    .then(response => response.json())
    .then(data => {
        const previewResult = document.getElementById('previewResult');
        previewResult.innerHTML = data.content;
        previewResult.style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to generate preview');
    });
});
</script>
@endpush
@endsection
