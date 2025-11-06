@extends('notigen::layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="float-left">Notification Templates</h3>
                    <a href="{{ route('notigen.create') }}" class="btn btn-primary float-right">Create New Template</a>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                            <tr>
                                <td>{{ $template->name }}</td>
                                <td>{{ $template->type }}</td>
                                <td>{{ $template->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('notigen.edit', $template->id) }}" class="btn btn-sm btn-info">Edit</a>
                                    <a href="{{ route('notigen.show', $template->id) }}" class="btn btn-sm btn-success">View</a>
                                    <form action="{{ route('notigen.destroy', $template->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
