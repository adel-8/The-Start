@extends('admin.layouts.app')

@section('title', __('admin.contact_messages'))

@section('content')

<div class="admin-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <h1>{{ __('admin.contact_messages') }}</h1>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>{{ __('admin.id') }}</th>
                <th>{{ __('admin.name') }}</th>
                <th>{{ __('admin.email') }}</th>
                <th>{{ __('admin.subject') }}</th>
                <th>{{ __('admin.status') }}</th>
                <th>{{ __('admin.received') }}</th>
                <th>{{ __('admin.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($messages as $message)
            <tr>
                <td>{{ $message->id }}</td>
                <td>{{ $message->name }}</td>
                <td>{{ $message->email }}</td>
                <td>{{ $message->subject }}</td>
                <td>
                    @if($message->read)
                        <span class="badge badge-read">{{ __('admin.read') }}</span>
                    @else
                        <span class="badge badge-unread">{{ __('admin.unread') }}</span>
                    @endif
                </td>
                <td>{{ $message->created_at ? $message->created_at->format('Y-m-d H:i') : __('admin.na') }}</td>
                <td>
                    <a href="{{ route('admin.contact-messages.show', $message) }}" class="btn-sm btn-edit">
                        {{ __('admin.view') }}
                    </a>
                    <form action="{{ route('admin.contact-messages.destroy', $message) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn-sm btn-delete delete-message-btn"
                                data-confirm="{{ __('admin.delete_confirm') }}">
                            {{ __('admin.delete') }}
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; padding:2rem; color:var(--color-muted);">
                    {{ __('admin.no_messages') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:1.5rem;">
    {{ $messages->links() }}
</div>

@endsection

@push('styles')
<style>
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 1rem;
        border: 1px solid var(--color-border);
    }
    .admin-table {
        min-width: 750px;
        width: 100%;
        border: none;
    }
    .btn-edit {
        background: var(--color-info);
        color: white;
        padding: 0.25rem 0.6rem;
        border-radius: 0.4rem;
        font-size: 0.75rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }
    .btn-delete {
        background: var(--color-danger);
        color: white;
        border: none;
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('.delete-message-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            const confirmMessage = button.getAttribute('data-confirm');
            if (!confirm(confirmMessage)) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush