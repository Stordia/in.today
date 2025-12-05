<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header --}}
        <div>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Overview of affiliate partners, links, conversions and payouts.
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                Flow: Affiliates &rarr; Links &rarr; Clicks &rarr; Conversions &rarr; Payouts
            </p>
        </div>

        {{-- KPI Cards - Responsive Grid --}}
        <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Total Affiliates --}}
            <div class="rounded-2xl bg-white dark:bg-gray-900/60 p-5 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/5">
                <div class="flex items-center gap-x-4">
                    <div class="flex-shrink-0 rounded-xl bg-primary-50 dark:bg-primary-500/10 p-3">
                        <x-heroicon-o-user-group class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Affiliates</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($totalAffiliates) }}</p>
                    </div>
                </div>
            </div>

            {{-- Total Conversions --}}
            <div class="rounded-2xl bg-white dark:bg-gray-900/60 p-5 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/5">
                <div class="flex items-center gap-x-4">
                    <div class="flex-shrink-0 rounded-xl bg-info-50 dark:bg-info-500/10 p-3">
                        <x-heroicon-o-arrow-trending-up class="h-6 w-6 text-info-600 dark:text-info-400" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Conversions</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($totalConversions) }}</p>
                    </div>
                </div>
            </div>

            {{-- Outstanding Commission --}}
            <div class="rounded-2xl bg-white dark:bg-gray-900/60 p-5 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/5">
                <div class="flex items-center gap-x-4">
                    <div class="flex-shrink-0 rounded-xl bg-warning-50 dark:bg-warning-500/10 p-3">
                        <x-heroicon-o-clock class="h-6 w-6 text-warning-600 dark:text-warning-400" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Outstanding Commission</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">&euro;{{ number_format($outstandingCommission, 2, ',', '.') }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Approved, not paid</p>
                    </div>
                </div>
            </div>

            {{-- Total Paid --}}
            <div class="rounded-2xl bg-white dark:bg-gray-900/60 p-5 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/5">
                <div class="flex items-center gap-x-4">
                    <div class="flex-shrink-0 rounded-xl bg-success-50 dark:bg-success-500/10 p-3">
                        <x-heroicon-o-banknotes class="h-6 w-6 text-success-600 dark:text-success-400" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Paid</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">&euro;{{ number_format($totalPaid, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div x-data="{ activeTab: @entangle('activeTab') }">
            {{-- Tab Navigation --}}
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
                    <button
                        type="button"
                        wire:click="setActiveTab('overview')"
                        :class="activeTab === 'overview' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors"
                    >
                        Overview
                    </button>
                    <button
                        type="button"
                        wire:click="setActiveTab('affiliates')"
                        :class="activeTab === 'affiliates' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors"
                    >
                        Affiliates
                    </button>
                    <button
                        type="button"
                        wire:click="setActiveTab('links')"
                        :class="activeTab === 'links' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors"
                    >
                        Links
                    </button>
                    <button
                        type="button"
                        wire:click="setActiveTab('conversions')"
                        :class="activeTab === 'conversions' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors"
                    >
                        Conversions
                    </button>
                    <button
                        type="button"
                        wire:click="setActiveTab('payouts')"
                        :class="activeTab === 'payouts' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-medium transition-colors"
                    >
                        Payouts
                    </button>
                </nav>
            </div>

            {{-- Tab Content --}}
            <div class="mt-6">
                {{-- Overview Tab --}}
                <div x-show="activeTab === 'overview'" x-cloak>
                    <div class="grid gap-6 lg:grid-cols-2">
                        {{-- Left Column: How Affiliates Work --}}
                        <div class="rounded-2xl bg-white dark:bg-gray-900/60 p-5 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/5">
                            <h2 class="text-sm font-semibold text-gray-950 dark:text-white">
                                How Affiliates Work
                            </h2>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Short explanation of the flow:
                            </p>
                            <ul class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-300 list-disc list-inside">
                                <li>Affiliates register and receive unique tracking links.</li>
                                <li>Customers arrive via <span class="font-mono text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">/go/{slug}</span>.</li>
                                <li>Admin reviews and approves conversions with order amounts and commissions.</li>
                                <li>Approved conversions are grouped into payouts for payment.</li>
                                <li>Payouts can be tracked and marked as paid when transferred.</li>
                            </ul>
                        </div>

                        {{-- Right Column: Recent Conversions --}}
                        <div class="rounded-2xl bg-white dark:bg-gray-900/60 p-5 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/5 flex flex-col">
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="text-sm font-semibold text-gray-950 dark:text-white">
                                    Recent Conversions
                                </h2>
                                <a href="{{ $this->getConversionResourceUrl('index') }}"
                                   class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500 dark:hover:text-primary-300">
                                    View all &rarr;
                                </a>
                            </div>

                            <div class="mt-4 flex-1 overflow-hidden rounded-xl ring-1 ring-gray-950/5 dark:ring-white/5">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-900/80 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide">
                                            <tr>
                                                <th class="px-3 py-2 text-left">Affiliate</th>
                                                <th class="px-3 py-2 text-left">Link</th>
                                                <th class="px-3 py-2 text-left">Lead</th>
                                                <th class="px-3 py-2 text-center">Status</th>
                                                <th class="px-3 py-2 text-right">Commission</th>
                                                <th class="px-3 py-2 text-right">Created</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-950/60">
                                            @forelse($recentConversions as $conversion)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-900 dark:text-gray-200">
                                                        {{ $conversion->affiliate?->name ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-600 dark:text-gray-300">
                                                        {{ $conversion->affiliateLink?->slug ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2 whitespace-nowrap text-gray-500 dark:text-gray-400 text-xs">
                                                        {{ $conversion->contactLead?->email ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        <x-filament::badge :color="$this->getConversionStatusColor($conversion->status)" size="sm">
                                                            {{ $this->getConversionStatusLabel($conversion->status) }}
                                                        </x-filament::badge>
                                                    </td>
                                                    <td class="px-3 py-2 text-right whitespace-nowrap text-gray-900 dark:text-gray-200">
                                                        @if($conversion->commission_amount)
                                                            &euro;{{ number_format($conversion->commission_amount, 2, ',', '.') }}
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2 text-right whitespace-nowrap text-gray-500 dark:text-gray-400 text-xs">
                                                        {{ $conversion->created_at?->format('d.m.Y H:i') }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="px-3 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                                        No conversions yet.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Affiliates Tab --}}
                <div x-show="activeTab === 'affiliates'" x-cloak>
                    <div class="rounded-2xl bg-white dark:bg-gray-900/60 p-5 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Affiliates</h3>
                            <div class="flex items-center gap-3">
                                <x-filament::link :href="$this->getAffiliateResourceUrl('index')" color="gray" icon="heroicon-m-list-bullet">
                                    Full list
                                </x-filament::link>
                                <x-filament::button :href="$this->getAffiliateResourceUrl('create')" tag="a" icon="heroicon-m-plus" size="sm">
                                    Create Affiliate
                                </x-filament::button>
                            </div>
                        </div>

                        @if($affiliates->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">No affiliates yet.</p>
                        @else
                            <div class="overflow-hidden rounded-xl ring-1 ring-gray-950/5 dark:ring-white/5">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-900/80 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide">
                                            <tr>
                                                <th class="px-3 py-2 text-left">Name</th>
                                                <th class="px-3 py-2 text-left">Email</th>
                                                <th class="px-3 py-2 text-left">Type</th>
                                                <th class="px-3 py-2 text-center">Links</th>
                                                <th class="px-3 py-2 text-center">Conversions</th>
                                                <th class="px-3 py-2 text-right">Total Paid</th>
                                                <th class="px-3 py-2 text-right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-950/60">
                                            @foreach($affiliates as $affiliate)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                                    <td class="px-3 py-2 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                                        {{ $affiliate->name }}
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                        {{ $affiliate->contact_email ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <x-filament::badge :color="$this->getAffiliateTypeBadgeColor($affiliate->type)" size="sm">
                                                            {{ ucfirst($affiliate->type) }}
                                                        </x-filament::badge>
                                                    </td>
                                                    <td class="px-3 py-2 text-center text-gray-900 dark:text-white">
                                                        {{ $affiliate->links_count }}
                                                    </td>
                                                    <td class="px-3 py-2 text-center text-gray-900 dark:text-white">
                                                        {{ $affiliate->conversions_count }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-gray-900 dark:text-white whitespace-nowrap">
                                                        &euro;{{ number_format($affiliate->paid_commission ?? 0, 2, ',', '.') }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right">
                                                        <div class="flex items-center justify-end gap-1">
                                                            <x-filament::icon-button
                                                                :href="$this->getAffiliateResourceUrl('view', $affiliate)"
                                                                tag="a"
                                                                icon="heroicon-m-eye"
                                                                size="sm"
                                                                color="gray"
                                                                tooltip="View"
                                                            />
                                                            <x-filament::icon-button
                                                                :href="$this->getAffiliateResourceUrl('edit', $affiliate)"
                                                                tag="a"
                                                                icon="heroicon-m-pencil-square"
                                                                size="sm"
                                                                color="gray"
                                                                tooltip="Edit"
                                                            />
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Links Tab --}}
                <div x-show="activeTab === 'links'" x-cloak>
                    <div class="rounded-2xl bg-white dark:bg-gray-900/60 p-5 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Recent Links</h3>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                    Links are created from within each Affiliate's edit page.
                                </p>
                            </div>
                            <x-filament::link :href="$this->getLinkResourceUrl('index')" color="gray" icon="heroicon-m-list-bullet">
                                Full list
                            </x-filament::link>
                        </div>

                        @if($links->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">No links yet.</p>
                        @else
                            <div class="overflow-hidden rounded-xl ring-1 ring-gray-950/5 dark:ring-white/5">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-900/80 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide">
                                            <tr>
                                                <th class="px-3 py-2 text-left">Affiliate</th>
                                                <th class="px-3 py-2 text-left">Slug</th>
                                                <th class="px-3 py-2 text-left">Target URL</th>
                                                <th class="px-3 py-2 text-center">Clicks</th>
                                                <th class="px-3 py-2 text-center">Conversions</th>
                                                <th class="px-3 py-2 text-right">Created</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-950/60">
                                            @foreach($links as $link)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                                    <td class="px-3 py-2 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                                        {{ $link->affiliate?->name ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded font-mono">{{ $link->slug }}</code>
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-500 dark:text-gray-400 max-w-xs truncate" title="{{ $link->target_url }}">
                                                        {{ Str::limit($link->target_url, 40) }}
                                                    </td>
                                                    <td class="px-3 py-2 text-center text-gray-900 dark:text-white">
                                                        {{ number_format($link->clicks_count ?? 0) }}
                                                    </td>
                                                    <td class="px-3 py-2 text-center text-gray-900 dark:text-white">
                                                        {{ $link->conversions_count }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                        {{ $link->created_at?->format('d.m.Y') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Conversions Tab --}}
                <div x-show="activeTab === 'conversions'" x-cloak>
                    <div class="rounded-2xl bg-white dark:bg-gray-900/60 p-5 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Recent Conversions</h3>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                    Approve conversions and group them into payouts in the detailed view.
                                </p>
                            </div>
                            <x-filament::link :href="$this->getConversionResourceUrl('index')" color="gray" icon="heroicon-m-list-bullet">
                                Full list
                            </x-filament::link>
                        </div>

                        @if($conversions->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">No conversions yet.</p>
                        @else
                            <div class="overflow-hidden rounded-xl ring-1 ring-gray-950/5 dark:ring-white/5">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-900/80 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide">
                                            <tr>
                                                <th class="px-3 py-2 text-left">Affiliate</th>
                                                <th class="px-3 py-2 text-left">Link</th>
                                                <th class="px-3 py-2 text-left">Lead</th>
                                                <th class="px-3 py-2 text-left">Restaurant</th>
                                                <th class="px-3 py-2 text-center">Status</th>
                                                <th class="px-3 py-2 text-right">Order</th>
                                                <th class="px-3 py-2 text-right">Commission</th>
                                                <th class="px-3 py-2 text-right">Created</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-950/60">
                                            @foreach($conversions as $conversion)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                                    <td class="px-3 py-2 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                                        {{ $conversion->affiliate?->name ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                        {{ $conversion->affiliateLink?->slug ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">
                                                        {{ $conversion->contactLead?->email ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                        {{ $conversion->restaurant?->name ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        <x-filament::badge :color="$this->getConversionStatusColor($conversion->status)" size="sm">
                                                            {{ $this->getConversionStatusLabel($conversion->status) }}
                                                        </x-filament::badge>
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-gray-900 dark:text-white whitespace-nowrap">
                                                        @if($conversion->order_amount)
                                                            &euro;{{ number_format($conversion->order_amount, 2, ',', '.') }}
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-gray-900 dark:text-white whitespace-nowrap">
                                                        @if($conversion->commission_amount)
                                                            &euro;{{ number_format($conversion->commission_amount, 2, ',', '.') }}
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">
                                                        {{ $conversion->created_at?->format('d.m.Y H:i') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Payouts Tab --}}
                <div x-show="activeTab === 'payouts'" x-cloak>
                    <div class="rounded-2xl bg-white dark:bg-gray-900/60 p-5 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Recent Payouts</h3>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                    Create payouts from within each Affiliate's edit page under the Payouts tab.
                                </p>
                            </div>
                            <x-filament::link :href="$this->getPayoutResourceUrl('index')" color="gray" icon="heroicon-m-list-bullet">
                                Full list
                            </x-filament::link>
                        </div>

                        @if($payouts->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">No payouts yet.</p>
                        @else
                            <div class="overflow-hidden rounded-xl ring-1 ring-gray-950/5 dark:ring-white/5">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-900/80 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide">
                                            <tr>
                                                <th class="px-3 py-2 text-left">#</th>
                                                <th class="px-3 py-2 text-left">Affiliate</th>
                                                <th class="px-3 py-2 text-right">Amount</th>
                                                <th class="px-3 py-2 text-center">Status</th>
                                                <th class="px-3 py-2 text-center">Conversions</th>
                                                <th class="px-3 py-2 text-left">Reference</th>
                                                <th class="px-3 py-2 text-right">Paid At</th>
                                                <th class="px-3 py-2 text-right">Created</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-950/60">
                                            @foreach($payouts as $payout)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                                    <td class="px-3 py-2 text-gray-500 dark:text-gray-400">
                                                        {{ $payout->id }}
                                                    </td>
                                                    <td class="px-3 py-2 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                                        {{ $payout->affiliate?->name ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                                        &euro;{{ number_format($payout->amount ?? 0, 2, ',', '.') }}
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        <x-filament::badge :color="$this->getPayoutStatusColor($payout->status)" size="sm">
                                                            {{ $this->getPayoutStatusLabel($payout->status) }}
                                                        </x-filament::badge>
                                                    </td>
                                                    <td class="px-3 py-2 text-center text-gray-900 dark:text-white">
                                                        {{ $payout->conversions_count }}
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                        {{ $payout->reference ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                        {{ $payout->paid_at?->format('d.m.Y H:i') ?? '—' }}
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                        {{ $payout->created_at?->format('d.m.Y') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
