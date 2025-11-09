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

        /* Variable Row Animations */
        .variable-row {
            transition: all 0.3s ease;
            transform-origin: top;
            animation: slideDown 0.3s ease-out;
        }

        .variable-row.removing {
            animation: slideUp 0.3s ease-out forwards;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }

            to {
                opacity: 0;
                transform: translateY(-10px);
            }
        }

        /* Form Validation Styles */
        .form-control.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
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
                                    <!-- Variable rows will be added here by JavaScript -->
                                </div>
                                <div class="form-text mt-2" id="variables-error" style="display: none;">
                                    <i class="fas fa-exclamation-circle text-danger me-1"></i>
                                    Please add at least one valid variable
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
            $(document).ready(function() {
                // Function to create a variable row
                function createVariableRow(index) {
                    return `
                        <div class="row mb-2 variable-row" data-index="${index}">
                            <div class="col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-tag"></i>
                                    </span>
                                    <input type="text" class="form-control shadow-sm variable-name" 
                                        name="variables[${index}][name]" 
                                        placeholder="Variable Name (e.g., user_name)"
                                        required>
                                    <div class="invalid-feedback">Please enter a valid variable name</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-info"></i>
                                    </span>
                                    <input type="text" class="form-control shadow-sm" 
                                        name="variables[${index}][description]"
                                        placeholder="Description (e.g., User's full name)"
                                        required>
                                    <div class="invalid-feedback">Please enter a description</div>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-variable"
                                    ${index === 0 ? 'disabled' : ''}>
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    `;
                }

                // Function to reindex variables
                function reindexVariables() {
                    $('#variables-container .variable-row').each(function(index) {
                        $(this).attr('data-index', index);
                        $(this).find('input').each(function() {
                            const name = $(this).attr('name');
                            if (name) {
                                $(this).attr('name', name.replace(/\[\d+\]/, `[${index}]`));
                            }
                        });
                        $(this).find('.remove-variable').prop('disabled', index === 0);
                    });
                }

                // Add initial variable row
                $('#variables-container').empty().append(createVariableRow(0));

                // Handle add variable button
                $('#add-variable').on('click', function() {
                    const newIndex = $('#variables-container .variable-row').length;
                    const newRow = $(createVariableRow(newIndex));
                    $('#variables-container').append(newRow);
                    newRow.hide().fadeIn(300);
                });

                // Handle remove variable button
                $('#variables-container').on('click', '.remove-variable', function() {
                    if (!$(this).prop('disabled')) {
                        const row = $(this).closest('.variable-row');
                        row.fadeOut(300, function() {
                            row.remove();
                            reindexVariables();
                        });
                    }
                });

                // Variable name validation
                $('#variables-container').on('input', '.variable-name', function() {
                    const value = $(this).val();
                    const valid = /^[a-z][a-z0-9_]*$/.test(value);

                    if (!valid && value) {
                        $(this).addClass('is-invalid')
                            .next('.invalid-feedback')
                            .text(
                                'Only lowercase letters, numbers, and underscores allowed. Must start with a letter.'
                                );
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                // Form validation
                $('form').on('submit', function(e) {
                    let isValid = true;

                    // Validate variable names
                    $('#variables-container .variable-name').each(function() {
                        const value = $(this).val();
                        if (!value || !/^[a-z][a-z0-9_]*$/.test(value)) {
                            $(this).addClass('is-invalid');
                            isValid = false;
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                        return false;
                    }
                });

                // Template Key Management
                function generateTemplateKey(name) {
                    const timestamp = Date.now();
                    const random = Math.random().toString(36).substring(2, 8);
                    const baseKey = (name || 'template').toLowerCase()
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/\s+/g, '_')
                        .replace(/-+/g, '_');

                    return `${baseKey}_${timestamp}_${random}`;
                }

                // Generate key on name input
                $('#name').on('input', function() {
                    if (!$('#template_key').val()) {
                        const newKey = generateTemplateKey($(this).val());
                        $('#template_key').val(newKey).trigger('input');
                    }
                });

                // Generate new key button
                $('.generate-key').on('click', function() {
                    const newKey = generateTemplateKey($('#name').val());
                    $('#template_key').val(newKey).trigger('input');
                });

                // Key validation
                let keyCheckTimeout;
                $('#template_key').on('input', function() {
                    const key = $(this).val().toLowerCase();
                    $(this).val(key);

                    clearTimeout(keyCheckTimeout);
                    keyCheckTimeout = setTimeout(() => {
                        const $status = $('#template_key_status');
                        $status.html(
                            '<i class="fas fa-spinner fa-spin text-primary me-1"></i> Checking availability...'
                            );

                        $.get('{{ route('notigen.check-key') }}', {
                                key
                            })
                            .done(function(response) {
                                if (response.available) {
                                    $status.html(
                                        '<i class="fas fa-check-circle text-success me-1"></i> Template key is available'
                                        );
                                    $('#template_key')[0].setCustomValidity('');
                                } else {
                                    $status.html(
                                        '<i class="fas fa-exclamation-circle text-danger me-1"></i> ' +
                                        response.message);
                                    $('#template_key')[0].setCustomValidity(
                                        'This template key is already in use');
                                }
                            })
                            .fail(function() {
                                $status.html(
                                    '<i class="fas fa-exclamation-circle text-warning me-1"></i> Could not verify key availability'
                                    );
                            });
                    }, 500);
                });
            });
        </script>
    @endpush
@endsection
