@extends('admin.layouts.app')

@section('title', __('admin.customers'))

@section('content')
<div class="admin-header">
    <h1>{{ __('admin.customers') }}</h1>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="admin-table">
    <thead>
        
            <th>{{ __('admin.id') }}</th>
            <th>{{ __('admin.name') }}</th>
            <th>{{ __('admin.email') }}</th>
            <th>{{ __('admin.role') }}</th>
            <th>{{ __('admin.registered') }}</th>
            <th>{{ __('admin.actions') }}</th>
        </thead>
    <tbody>
        @foreach($users as $user)
        
            <td>{{ $user->id }}</td>
            <td>{{ $user->name ?? $user->username }}</td>
            <td>{{ $user->email }}</td>
            <td>
                @php
                    $roleName = match($user->role_id) {
                        1 => __('admin.role_owner'),
                        2 => __('admin.role_admin'),
                        3 => __('admin.role_customer'),
                        default => __('admin.role_unknown')
                    };
                    $roleClass = match($user->role_id) {
                        1 => 'role-owner',
                        2 => 'role-admin',
                        3 => 'role-customer',
                        default => ''
                    };
                @endphp
                <span class="badge {{ $roleClass }}">{{ $roleName }}</span>
            </td>
            <td>{{ $user->created_at->format('Y-m-d') }}</td>
            <td>
                <a href="{{ route('admin.users.edit', $user) }}" class="btn-sm btn-edit">{{ __('admin.edit') }}</a>
                @if(auth()->id() !== $user->id)
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-sm btn-delete delete-user-btn" data-confirm="{{ __('admin.delete_confirm') }}">{{ __('admin.delete') }}</button>
                    </form>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $users->links() }}
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.delete-user-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            const confirmMessage = button.getAttribute('data-confirm');
            if (!confirm(confirmMessage)) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush