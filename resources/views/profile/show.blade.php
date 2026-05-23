@extends('layouts.app')

@section('title', __('messages.my_profile'))

@push('styles')
    <style>
        /* ----- Profile page custom styles (from the professional design) ----- */
        .profile-container {
            max-width: 1380px;
            margin: 0 auto;
            padding: 1rem 1.5rem 2rem;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 2rem;
            gap: 1rem;
        }

        .greeting h1 {
            font-size: 1.9rem;
            font-weight: 700;
            letter-spacing: -0.3px;
            background: linear-gradient(135deg, var(--color-primary), #4e3b64);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .greeting p {
            color: var(--color-muted);
            font-weight: 500;
            margin-top: 0.25rem;
            font-size: 0.9rem;
        }

        .badge-role {
            background: var(--color-surface);
            padding: 0.5rem 1.2rem;
            border-radius: 100px;
            box-shadow: var(--shadow-sm);
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--color-border);
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 1.8rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--color-surface);
            border-radius: 32px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid var(--color-border);
        }

        .profile-avatar-section {
            padding: 1.8rem 1.8rem 2rem;
            text-align: center;
        }

        .avatar-wrapper {
            width: 140px;
            height: 140px;
            margin: 0 auto 1.2rem;
            position: relative;
        }

        .profile-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid var(--color-surface);
            box-shadow: 0 18px 28px -10px rgba(0,0,0,0.15);
        }

        .img-placeholder-icon {
            width: 100%;
            height: 100%;
            background: linear-gradient(145deg, #e2dfd4, #f5f2ea);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: var(--color-primary);
        }

        .user-status {
            margin-top: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f3f1ea;
            padding: 0.3rem 1rem;
            border-radius: 40px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .verified-badge { color: #2c8f5e; }
        .not-verified { color: #c26b2e; }

        .role-chip {
            background: #f0ede6;
            padding: 0.35rem 1rem;
            border-radius: 40px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-top: 1rem;
            color: var(--color-primary);
        }

        .details-section {
            padding: 1.6rem 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            border-left: 4px solid var(--color-accent);
            padding-left: 1rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.2rem 1.8rem;
            margin-bottom: 2rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            border-bottom: 1px dashed var(--color-border);
            padding-bottom: 0.65rem;
        }

        .info-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            color: var(--color-muted);
            margin-bottom: 0.3rem;
        }

        .info-value {
            font-weight: 600;
            font-size: 1rem;
            color: var(--color-text);
            word-break: break-word;
        }

        .timestamp {
            font-family: monospace;
            font-size: 0.85rem;
            color: var(--color-muted);
        }

        .meta-timeline {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .meta-card {
            background: var(--color-surface);
            border-radius: 20px;
            padding: 0.9rem 1rem;
            border: 1px solid var(--color-border);
        }

        .meta-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--color-muted);
            margin-bottom: 0.5rem;
        }

        .action-buttons {
            margin-top: 2rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: flex-end;
        }

        .btn {
            border: none;
            padding: 0.7rem 1.6rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            border: 1px solid var(--color-border);
        }

        .btn-primary {
            background: var(--color-primary);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: var(--color-primary-hover);
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--color-border);
            color: var(--color-text);
        }

        .btn-outline:hover {
            background: #f5f3ec;
        }

        .shop-section {
            margin-top: 2rem;
            text-align: center;
            padding: 1rem 0 0.5rem;
        }

        .btn-shop {
            background: linear-gradient(135deg, var(--color-accent) 0%, #c9a345 100%);
            color: var(--color-text);
            border: none;
            padding: 1rem 2.5rem;
            border-radius: 60px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 6px 14px rgba(224, 184, 84, 0.3);
        }

        .btn-shop:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 22px rgba(224, 184, 84, 0.4);
        }

        @media (max-width: 800px) {
            .profile-grid {
                grid-template-columns: 1fr;
                gap: 1.2rem;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
<div class="profile-container">
    <div class="top-bar">
        <div class="greeting">
            <h1><i class="fas fa-id-card"></i> {{ __('messages.my_profile') }}</h1>
            <p>{{ __('messages.manage_account_info') }}</p>
        </div>
        <div class="badge-role">
            <i class="fas fa-tag"></i>
            @php
                $roleName = match($user->role_id) {
                    1 => __('messages.role_administrator'),
                    2 => __('messages.role_manager'),
                    3 => __('messages.role_customer'),
                    default => __('messages.role_member')
                };
            @endphp
            {{ $roleName }}
        </div>
    </div>

    <div class="profile-grid">
        <!-- Left column: Avatar and quick info -->
        <div class="card">
            <div class="profile-avatar-section">
                <div class="avatar-wrapper">
                    @if($user->profile_image)
                        <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" class="profile-img">
                    @else
                        <div class="img-placeholder-icon">
                            <i class="fas fa-user-circle"></i>
                        </div>
                    @endif
                </div>
                <div class="user-status">
                    @if($user->email_verified_at)
                        <i class="fas fa-check-circle verified-badge"></i> {{ __('messages.email_verified') }}
                    @else
                        <i class="fas fa-exclamation-circle not-verified"></i> {{ __('messages.email_not_verified') }}
                    @endif
                </div>
                <div class="role-chip">
                    <i class="fas fa-shield-alt"></i> {{ __('messages.role') }}: {{ $roleName }}
                </div>
            </div>
        </div>

        <!-- Right column: Details -->
        <div class="card">
            <div class="details-section">
                <div class="section-title">
                    <i class="fas fa-address-card"></i> {{ __('messages.personal_information') }}
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-user"></i> {{ __('messages.full_name') }}</div>
                        <div class="info-value">{{ $user->name ?? $user->username }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-envelope"></i> {{ __('messages.email_address') }}</div>
                        <div class="info-value">{{ $user->email }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-phone"></i> {{ __('messages.phone_number') }}</div>
                        <div class="info-value">{{ $user->phone ?? __('messages.not_provided') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-calendar-alt"></i> {{ __('messages.age') }}</div>
                        <div class="info-value">{{ $user->age ?? __('messages.not_provided') }}</div>
                    </div>
                </div>

                <div class="section-title">
                    <i class="fas fa-history"></i> {{ __('messages.account_timeline') }}
                </div>
                <div class="meta-timeline">
                    <div class="meta-card">
                        <div class="meta-label"><i class="far fa-calendar-plus"></i> {{ __('messages.member_since') }}</div>
                        <div class="meta-value timestamp">{{ $user->created_at->format('F j, Y') }}</div>
                    </div>
                    <div class="meta-card">
                        <div class="meta-label"><i class="fas fa-edit"></i> {{ __('messages.last_updated') }}</div>
                        <div class="meta-value timestamp">{{ $user->updated_at->diffForHumans() }}</div>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="{{ route('orders.index') }}" class="btn btn-outline">
                        <i class="fas fa-box"></i> {{ __('messages.view_my_orders') }}
                    </a>
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        <i class="fas fa-pen-alt"></i> {{ __('messages.edit_profile') }}
                    </a>
                    <a href="{{ route('/') }}" class="btn btn-outline">
                        <i class="fas fa-home"></i> {{ __('messages.back_to_home') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Marketplace button -->
    <div class="shop-section">
        <a href="{{ route('Shop') }}" class="btn-shop">
            <i class="fas fa-store"></i>
            {{ __('messages.explore_marketplace') }}
            <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</div>
@endsection