@extends('admin.layouts.app')

@section('title', __('admin.message') . ' #' . ($message->id ?? 'N/A'))

@section('content')
<div class="admin-header">
    <h1>{{ __('admin.message_details') }}</h1>
    <a href="{{ route('admin.contact-messages.index') }}" class="btn-secondary">{{ __('admin.back_to_list') }}</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="message-details">
            <div class="detail-row">
                <div class="detail-label">{{ __('admin.from') }}:</div>
                <div class="detail-value">{{ $message->name }} ({{ $message->email }})</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">{{ __('admin.phone') }}:</div>
                <div class="detail-value">{{ $message->phone ?? __('admin.not_provided') }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">{{ __('admin.subject') }}:</div>
                <div class="detail-value">{{ $message->subject }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">{{ __('admin.received') }}:</div>
                <div class="detail-value">{{ $message->created_at ? $message->created_at->format('F j, Y, g:i a') : __('admin.na') }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">{{ __('admin.status') }}:</div>
                <div class="detail-value">
                    @if($message->read)
                        <span class="badge badge-read">{{ __('admin.read') }}</span>
                    @else
                        <span class="badge badge-unread">{{ __('admin.unread') }}</span>
                    @endif
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">{{ __('admin.message') }}:</div>
                <div class="detail-message">{{ $message->message }}</div>
            </div>
        </div>
    </div>
</div>




<style>
    /* Contact Message Details – add to admin.css */
.message-details {
    background: var(--color-surface);
    border-radius: 1rem;
    padding: 1.5rem;
    margin-top: 1rem;
    border: 1px solid var(--color-border);
}
.detail-row {
    margin-bottom: 1rem;
    display: flex;
    flex-wrap: wrap;
}
.detail-label {
    font-weight: 600;
    width: 120px;
    color: var(--color-muted);
}
.detail-value {
    flex: 1;
    color: var(--color-text);
}
.detail-message {
    flex: 1;
    background: #f8fafc;
    padding: 1rem;
    border-radius: 0.5rem;
    white-space: pre-wrap;
}
.btn-danger {
    background: #dc3545;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-danger:hover {
    background: #c82333;
}
.mt-4 {
    margin-top: 1rem;
}
</style>
@endsection