@extends('notigen::layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="float-left">Create Notification Template</h3>
                        <a href="{{ route('notigen.index') }}" class="btn btn-secondary float-right">Back to List</a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('notigen.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">Template Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject"
                                    placeholder="Email subject line">
                                <small class="form-text text-muted">You can use variables like {name} in your
                                    subject</small>
                            </div>

                            <div class="mb-3">
                                <label for="channels" class="form-label">Channels</label>
                                <select class="form-control" id="channels" name="channels[]" multiple required>
                                    @foreach (config('notigen.default_channels', ['mail']) as $channel)
                                        <option value="{{ $channel }}">{{ ucfirst($channel) }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple channels</small>
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">Template Content</label>
                                <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                                <small class="form-text text-muted">You can use variables like {variable_name}</small>
                            </div>

                            <div class="mb-3">
                                <label for="variables" class="form-label">Variables</label>
                                <div id="variables-container">
                                    <div class="row mb-2">
                                        <div class="col">
                                            <input type="text" class="form-control" name="variables[0][name]"
                                                placeholder="Variable Name">
                                        </div>
                                        <div class="col">
                                            <input type="text" class="form-control" name="variables[0][description]"
                                                placeholder="Variable Description">
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-danger btn-sm remove-variable"
                                                disabled>Remove</button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-secondary btn-sm mt-2" id="add-variable">Add
                                    Variable</button>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Create Template</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('add-variable').addEventListener('click', function() {
                const container = document.getElementById('variables-container');
                const index = container.children.length;

                const template = `
            <div class="row mb-2">
                <div class="col">
                    <input type="text" class="form-control" name="variables[${index}][name]" placeholder="Variable Name">
                </div>
                <div class="col">
                    <input type="text" class="form-control" name="variables[${index}][description]" placeholder="Variable Description">
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-danger btn-sm remove-variable">Remove</button>
                </div>
            </div>
        `;

                container.insertAdjacentHTML('beforeend', template);
            });

            document.getElementById('variables-container').addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-variable') && !e.target.disabled) {
                    e.target.closest('.row').remove();
                }
            });
        </script>
    @endpush
@endsection
