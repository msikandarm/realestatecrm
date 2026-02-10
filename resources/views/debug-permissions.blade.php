@extends('layouts.app')

@section('title', 'Permission Debug')

@section('content')
<div class="page-header">
    <h1>Permission Debug Info</h1>
</div>

<div class="card">
    <div class="card-body">
        <h3>Current User Info</h3>
        <p><strong>ID:</strong> {{ Auth::id() }}</p>
        <p><strong>Name:</strong> {{ Auth::user()->name }}</p>
        <p><strong>Email:</strong> {{ Auth::user()->email }}</p>

        <h3>Roles</h3>
        <ul>
            @foreach(Auth::user()->roles as $role)
                <li>{{ $role->name }}</li>
            @endforeach
        </ul>

        <h3>Permissions ({{ Auth::user()->getAllPermissions()->count() }} total)</h3>
        <ul>
            @foreach(Auth::user()->getAllPermissions() as $permission)
                <li>{{ $permission->name }}</li>
            @endforeach
        </ul>

        <h3>Permission Tests</h3>
        <p>societies.view: {{ Auth::user()->can('societies.view') ? '✅ YES' : '❌ NO' }}</p>
        <p>societies.create: {{ Auth::user()->can('societies.create') ? '✅ YES' : '❌ NO' }}</p>
        <p>admin role: {{ Auth::user()->hasRole('admin') ? '✅ YES' : '❌ NO' }}</p>
    </div>
</div>
@endsection
