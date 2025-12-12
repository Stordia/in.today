@extends('layouts.venue')

@section('title', 'Find a place for tonight')
@section('meta_description', 'Discover restaurants, bars and venues near you with online booking')
@section('robots', 'index,follow')

@section('content')
    {{-- Hero Section --}}
    <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center">
            {{-- Main Heading --}}
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold text-primary mb-4">
                Find a place for tonight
            </h1>

            {{-- Subtitle --}}
            <p class="text-lg sm:text-xl text-secondary mb-12">
                Discover restaurants, bars and venues near you.
            </p>

            {{-- Search Form --}}
            <form method="GET" action="{{ route('public.city.search') }}" class="max-w-xl mx-auto">
                @csrf
                <div class="space-y-4">
                    {{-- City Select --}}
                    <div>
                        <label for="city" class="sr-only">Where</label>
                        @if($cities->isEmpty())
                            {{-- Empty State --}}
                            <div class="text-center py-12">
                                <p class="text-secondary">No cities available yet.</p>
                            </div>
                        @else
                            <select
                                id="city"
                                name="city_id"
                                required
                                class="w-full px-6 py-4 text-lg rounded-xl border-2 border-default bg-card text-primary focus:ring-2 focus:ring-brand focus:border-brand transition appearance-none cursor-pointer"
                            >
                                <option value="">Where do you want to go?</option>
                                @foreach($cities as $city)
                                    @php
                                        $cityCountry = $city->country()->first();
                                    @endphp
                                    <option value="{{ $city->id }}">
                                        {{ $city->name }}, {{ $cityCountry ? $cityCountry->name : '' }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    {{-- Submit Button --}}
                    @if($cities->isNotEmpty())
                        <button
                            type="submit"
                            class="w-full px-8 py-4 text-lg bg-brand text-white font-semibold rounded-xl hover:bg-brand-hover transition focus:ring-2 focus:ring-brand focus:ring-offset-2 shadow-lg shadow-brand/25"
                        >
                            Find places
                        </button>
                    @endif
                </div>
            </form>

            {{-- Additional Info --}}
            @if($cities->isNotEmpty())
                <p class="mt-8 text-sm text-secondary">
                    Browse {{ $totalVenues }} {{ Str::plural('venue', $totalVenues) }} across {{ $cities->count() }} {{ Str::plural('city', $cities->count()) }}
                </p>
            @endif
        </div>
    </div>
@endsection
