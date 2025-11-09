@extends('notigen::layouts.app')

@push('styles')
    <style>
        .custom-card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }

        .custom-card .card-header {
            background: linear-gradient(45deg, #4a90e2, #6772e5);
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 1.5rem;
            border: none;
        }

        .custom-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.25);
            border-color: #4a90e2;
        }

        .btn-primary {
            background: #4a90e2;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(74, 144, 226, 0.25);
        }
    </style>
@endpush

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="custom-header-content">
                            <h3 class="mb-0">Create Notification Template</h3>
                            <a href="{{ route('notigen.index') }}" class="btn btn-light back-btn">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('notigen.store') }}" method="POST">
                            @csrf

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="name" class="form-label fw-bold">Template Name</label>
                                        <input type="text" class="form-control shadow-sm" id="name" name="name"
                                            required>
                                    </div>

                                    <div class="mb-4">
                                        <label for="template_key" class="form-label fw-bold">Template Key</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control shadow-sm" id="template_key"
                                                name="template_key" required pattern="[a-z0-9_-]+"
                                                title="Only lowercase letters, numbers, dashes and underscores are allowed">
                                            <button class="btn btn-outline-secondary generate-key" type="button">
                                                <i class="fas fa-sync-alt"></i> Generate New
                                            </button>
                                        </div>
                                        <div class="form-text mt-2" id="template_key_status">
                                            <i class="fas fa-info-circle text-primary me-1"></i>
                                            This key will be used to reference the template in your code
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="description" class="form-label fw-bold">Description</label>
                                        <textarea class="form-control shadow-sm" id="description" name="description" rows="3"
                                            placeholder="Briefly describe the purpose of this template"></textarea>
                                    </div>

                                    <div class="mb-4">
                                        <label for="subject" class="form-label fw-bold">Subject</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="text" class="form-control shadow-sm" id="subject"
                                                name="subject" placeholder="Email subject line">
                                        </div>
                                        <div class="form-text mt-2">
                                            <i class="fas fa-info-circle text-primary me-1"></i>
                                            Use variables like {name} in your subject
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="channels" class="form-label fw-bold">Notification Channels</label>
                                        <div class="channel-selection">
                                            @foreach (config('notigen.default_channels', ['mail']) as $channel)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="channels[]"
                                                        value="{{ $channel }}" id="channel_{{ $channel }}"
                                                        @if ($channel == 'mail') checked @endif>
                                                    <label class="form-check-label" for="channel_{{ $channel }}">
                                                        <i
                                                            class="fas fa-{{ $channel == 'mail' ? 'envelope' : 'bell' }} me-1"></i>
                                                        {{ ucfirst($channel) }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label for="content" class="form-label fw-bold">Template Content</label>
                                        <textarea class="form-control shadow-sm" id="content" name="content" rows="12" required
                                            placeholder="Write your template content here..."></textarea>
                                        <div class="form-text mt-2">
                                            <i class="fas fa-code text-primary me-1"></i>
                                            Supports variables like {variable_name} and HTML formatting
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <label class="form-label fw-bold mb-0">Template Variables</label>
                                    <button type="button" class="btn btn-primary btn-sm" id="add-variable">
                                        <i class="fas fa-plus me-1"></i>Add Variable
                                    </button>
                                </div>
                                <div id="variables-container" class="card bg-light border-0 p-3">
                                    <div class="row mb-2 variable-row">
                                        <div class="col-md-5">
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-tag"></i>
                                                </span>
                                                <input type="text" class="form-control shadow-sm"
                                                    name="variables[0][name]"
                                                    placeholder="Variable Name (e.g., user_name)">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-info"></i>
                                                </span>
                                                <input type="text" class="form-control shadow-sm"
                                                    name="variables[0][description]"
                                                    placeholder="Description (e.g., User's full name)">
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button"
                                                class="btn btn-outline-danger btn-sm w-100 remove-variable" disabled>
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-2"></i>Create Template
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function generateTemplateKey(name) {
                const timestamp = new Date().getTime();
                const randomStr = Math.random().toString(36).substring(2, 8);
                const baseKey = name.toLowerCase()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/\s+/g, '_');
                
                return `${baseKey}_${timestamp}_${randomStr}`;
            }

            function addVariableRow(container, index) {
                const row = `
                    <div class="row mb-2 variable-row">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-tag"></i>
                                </span>
                                <input type="text" class="form-control shadow-sm" 
                                    name="variables[${index}][name]" 
                                    placeholder="Variable Name (e.g., user_name)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-info"></i>
                                </span>
                                <input type="text" class="form-control shadow-sm" 
                                    name="variables[${index}][description]"
                                    placeholder="Description (e.g., User's full name)">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-variable">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', row);
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Template Key Validation and Generation
                const templateKeyInput = document.getElementById('template_key');
                const templateKeyStatus = document.getElementById('template_key_status');
                const generateKeyBtn = document.querySelector('.generate-key');
                let keyCheckTimeout;                        function validateTemplateKey(key) {
                            if (!key) return;

                            // Clear previous timeout
                            if (keyCheckTimeout) clearTimeout(keyCheckTimeout);

                            // Set new timeout to check key
                            keyCheckTimeout = setTimeout(() => {
                                fetch(`{{ route('notigen.check-key') }}?key=${key}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.available) {
                                            templateKeyStatus.innerHTML =
                                                '<i class="fas fa-check-circle text-success me-1"></i> Template key is available';
                                            templateKeyInput.setCustomValidity('');
                                        } else {
                                            templateKeyStatus.innerHTML =
                                                '<i class="fas fa-exclamation-circle text-danger me-1"></i> This template key is already in use';
                                            templateKeyInput.setCustomValidity(
                                                'This template key is already in use');
                                        }
                                    });
                            }, 500);
                        }

                        // Generate initial key when name is typed
                        document.getElementById('name').addEventListener('input', function() {
                            if (!templateKeyInput.value) {
                                const timestamp = new Date().getTime();
                                const randomStr = Math.random().toString(36).substring(2, 8);
                                const baseKey = this.value.toLowerCase()
                                    .replace(/[^\w\s-]/g, '')
                                    .replace(/\s+/g, '_');

                                const newKey = `${baseKey}_${timestamp}_${randomStr}`;
                                templateKeyInput.value = newKey;
                                validateTemplateKey(newKey);
                            }
                        });

                        // Validate key on input
                        templateKeyInput.addEventListener('input', function() {
                            const key = this.value.toLowerCase();
                            this.value = key; // Force lowercase
                            validateTemplateKey(key);
                        });

                        // Generate new key
                        generateKeyBtn.addEventListener('click', function() {
                            const timestamp = new Date().getTime();
                            const randomStr = Math.random().toString(36).substring(2, 8);
                            const name = document.getElementById('name').value;
                            const baseKey = name.toLowerCase()
                                .replace(/[^\w\s-]/g, '')
                                .replace(/\s+/g, '_');

                            const newKey = `${baseKey}_${timestamp}_${randomStr}`;
                            templateKeyInput.value = newKey;
                            validateTemplateKey(newKey);
                        });

                        // Variables Container Management
                        const container = document.getElementById('variables-container');

                        document.getElementById('add-variable').addEventListener('click', function() {
                            const index = container.children.length;
                            const template = `
                        <div class="row mb-2 variable-row" data-index="${index}">
                            <div class="col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-tag"></i>
                                    </span>
                                    <input type="text" class="form-control shadow-sm" 
                                        name="variables[${index}][name]" 
                                        placeholder="Variable Name (e.g., user_name)">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-info"></i>
                                    </span>
                                    <input type="text" class="form-control shadow-sm" 
                                        name="variables[${index}][description]"
                                        placeholder="Description (e.g., User's full name)">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-variable">
                                    <i class="fas fa-times"></i>
                                </button>
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
