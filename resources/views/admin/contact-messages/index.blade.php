@extends('admin.layouts.app')

@section('title', __('admin.contact_messages'))

@section('content')
<div class="admin-header">
    <h1>{{ __('admin.contact_messages') }}</h1>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="admin-table">
    <thead>
       
            <th>{{ __('admin.id') }}</th>
            <th>{{ __('admin.name') }}</th>
            <th>{{ __('admin.email') }}</th>
            <th>{{ __('admin.subject') }}</th>
            <th>{{ __('admin.status') }}</th>
            <th>{{ __('admin.received') }}</th>
            <th>{{ __('admin.actions') }}</th>
        </thead>
    <tbody>
        @foreach($messages as $message)
        
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
                <a href="{{ route('admin.contact-messages.show', $message) }}" class="btn-sm btn-edit">{{ __('admin.view') }}</a>
                <form action="{{ route('admin.contact-messages.destroy', $message) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-sm btn-delete delete-message-btn" data-confirm="{{ __('admin.delete_confirm') }}">{{ __('admin.delete') }}</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $messages->links() }}
@endsection

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