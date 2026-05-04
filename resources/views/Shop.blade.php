@extends('layouts.app')

@section('title', ($settings['shop_page_title'] ?? __('messages.shop_page_title')))

@push('styles')
    @vite('resources/css/Shop.css')
@endpush

@section('content')
<div class="shop-main">
    <div class="container">
        <form method="GET" action="{{ route('Shop') }}" id="filter-form">
            <div class="shop-toolbar">
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="{{ $settings['shop_search_placeholder'] ?? __('messages.search_products') }}" value="{{ request('search') }}">
                </div>

                <!-- Custom sort dropdown (unchanged, relies on request) -->
                <div class="custom-sort-wrapper" id="sortDropdown">
                    <div class="sort-trigger" id="sortTrigger">
                        <span class="trigger-label" id="sortTriggerLabel">
                            @switch(request('sort'))
                                @case('newest') {{ __('messages.newest') }} @break
                                @case('bestseller') {{ __('messages.best_sellers') }} @break
                                @case('price_asc') {{ __('messages.price_low_to_high') }} @break
                                @case('price_desc') {{ __('messages.price_high_to_low') }} @break
                                @default {{ __('messages.sort_by_featured') }}
                            @endswitch
                        </span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="sort-options" id="sortOptions">
                        <div class="sort-option" data-value="newest" data-display="{{ __('messages.newest') }}">{{ __('messages.newest') }}</div>
                        <div class="sort-option" data-value="bestseller" data-display="{{ __('messages.best_sellers') }}">{{ __('messages.best_sellers') }}</div>
                        <div class="sort-option" data-value="price_asc" data-display="{{ __('messages.price_low_to_high') }}">{{ __('messages.price_low_to_high') }}</div>
                        <div class="sort-option" data-value="price_desc" data-display="{{ __('messages.price_high_to_low') }}">{{ __('messages.price_high_to_low') }}</div>
                    </div>
                    <input type="hidden" name="sort" id="sortInput" value="{{ request('sort', $settings['shop_default_sort'] ?? 'newest') }}">
                </div>

                <button type="submit" class="apply-filters">{{ __('messages.apply') }}</button>
                <button type="button" class="filter-toggle-mobile" id="filterToggleBtn"><i class="fas fa-sliders-h"></i> {{ __('messages.filters') }}</button>
            </div>

            <div class="shop-layout">
                <aside class="filters-sidebar" id="filtersSidebar">
                    <div class="filter-section">
                        <h4>{{ __('messages.categories') }}</h4>
                        @foreach($categories as $cat)
                            <label>
                                <input type="radio" name="category" value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'checked' : '' }}>
                                {{ $cat->name }}
                            </label>
                        @endforeach
                        <label>
                            <input type="radio" name="category" value="" {{ !request('category') ? 'checked' : '' }}>
                            {{ __('messages.all') }}
                        </label>
                    </div>

                    <div class="filter-section">
                        <h4>{{ __('messages.price_range') }}</h4>
                        <div class="price-inputs">
                            <input type="number" name="min_price" placeholder="{{ __('messages.min') }}" value="{{ request('min_price') }}" step="5" min="0">
                            <input type="number" name="max_price" placeholder="{{ __('messages.max') }}" value="{{ request('max_price') }}" step="5" min="0">
                        </div>
                    </div>

                    <button type="submit" class="clear-filters">{{ __('messages.apply_filters') }}</button>
                    <a href="{{ route('Shop') }}" class="clear-filters">{{ __('messages.clear_all_filters') }}</a>
                </aside>

                <div class="products-area" id="products-area">
                    @include('partials.product-grid', ['products' => $products])
                    <div class="pagination-container">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/Shop.js')
@endpush