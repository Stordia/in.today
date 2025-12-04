<x-filament-panels::page>
    {{-- Header --}}
    <div class="mb-6">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Overview of affiliate partners, links, conversions and payouts.
        </p>
        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
            Flow: Affiliates &rarr; Links &rarr; Clicks &rarr; Conversions &rarr; Payouts
        </p>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        {{-- Total Affiliates --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-x-4">
                <div class="flex-shrink-0 rounded-lg bg-primary-50 p-3 dark:bg-primary-500/10">
                    <x-heroicon-o-user-group class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Affiliates</p>
                    <p class="text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($totalAffiliates) }}</p>
                </div>
            </div>
        </div>

        {{-- Total Conversions --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-x-4">
                <div class="flex-shrink-0 rounded-lg bg-info-50 p-3 dark:bg-info-500/10">
                    <x-heroicon-o-arrow-trending-up class="h-6 w-6 text-info-600 dark:text-info-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Conversions</p>
                    <p class="text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($totalConversions) }}</p>
                </div>
            </div>
        </div>

        {{-- Outstanding Commission --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-x-4">
                <div class="flex-shrink-0 rounded-lg bg-warning-50 p-3 dark:bg-warning-500/10">
                    <x-heroicon-o-clock class="h-6 w-6 text-warning-600 dark:text-warning-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Outstanding Commission</p>
                    <p class="text-2xl font-semibold text-gray-950 dark:text-white">&euro;{{ number_format($outstandingCommission, 2, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Approved, not paid</p>
                </div>
            </div>
        </div>

        {{-- Total Paid --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-x-4">
                <div class="flex-shrink-0 rounded-lg bg-success-50 p-3 dark:bg-success-500/10">
                    <x-heroicon-o-banknotes class="h-6 w-6 text-success-600 dark:text-success-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Paid</p>
                    <p class="text-2xl font-semibold text-gray-950 dark:text-white">&euro;{{ number_format($totalPaid, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div x-data="{ activeTab: @entangle('activeTab') }" class="space-y-6">
        {{-- Tab Navigation --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
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
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            {{-- Overview Tab --}}
            <div x-show="activeTab === 'overview'" x-cloak>
                <h3 class="text-lg font-semibold text-gray-950 dark:text-white mb-4">How Affiliates Work</h3>
                <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-2 mb-6">
                    <li>Affiliates register and receive unique tracking links.</li>
                    <li>Customers arrive via <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">/go/{slug}</code>; leads and conversions are attributed to links.</li>
                    <li>Admin reviews and approves conversions, setting order amounts and commissions.</li>
                    <li>Approved conversions are grouped into payouts for payment.</li>
                    <li>Payouts can be tracked and marked as paid when transferred.</li>
                </ul>

                <h4 class="text-md font-semibold text-gray-950 dark:text-white mb-3">Recent Conversions</h4>

                @if($recentConversions->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">No conversions yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Affiliate</th>
                                    <th class="px-4 py-3">Link</th>
                                    <th class="px-4 py-3">Lead</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-right">Commission</th>
                                    <th class="px-4 py-3">Created</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($recentConversions as $conversion)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                            {{ $conversion->affiliate?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                            {{ $conversion->affiliateLink?->slug ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                            {{ $conversion->contactLead?->email ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <x-filament::badge :color="$this->getConversionStatusColor($conversion->status)">
                                                {{ $this->getConversionStatusLabel($conversion->status) }}
                                            </x-filament::badge>
                                        </td>
                                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white">
                                            @if($conversion->commission_amount)
                                                &euro;{{ number_format($conversion->commission_amount, 2, ',', '.') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                            {{ $conversion->created_at?->format('d.m.Y H:i') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="mt-4">
                    <x-filament::link :href="$this->getConversionResourceUrl('index')" icon="heroicon-m-arrow-right" icon-position="after">
                        View all conversions
                    </x-filament::link>
                </div>
            </div>

            {{-- Affiliates Tab --}}
            <div x-show="activeTab === 'affiliates'" x-cloak>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Affiliates</h3>
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
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3 text-center">Links</th>
                                    <th class="px-4 py-3 text-center">Conversions</th>
                                    <th class="px-4 py-3 text-right">Total Paid</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($affiliates as $affiliate)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                            {{ $affiliate->name }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                            {{ $affiliate->contact_email ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <x-filament::badge :color="$this->getAffiliateTypeBadgeColor($affiliate->type)">
                                                {{ ucfirst($affiliate->type) }}
                                            </x-filament::badge>
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-900 dark:text-white">
                                            {{ $affiliate->links_count }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-900 dark:text-white">
                                            {{ $affiliate->conversions_count }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white">
                                            &euro;{{ number_format($affiliate->paid_commission ?? 0, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
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
                @endif
            </div>

            {{-- Links Tab --}}
            <div x-show="activeTab === 'links'" x-cloak>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Recent Links</h3>
                    <div class="flex items-center gap-3">
                        <x-filament::link :href="$this->getLinkResourceUrl('index')" color="gray" icon="heroicon-m-list-bullet">
                            Full list
                        </x-filament::link>
                    </div>
                </div>

                <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">
                    Links are created from within each Affiliate's edit page. Use the Affiliates tab to manage links.
                </p>

                @if($links->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">No links yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Affiliate</th>
                                    <th class="px-4 py-3">Slug</th>
                                    <th class="px-4 py-3">Target URL</th>
                                    <th class="px-4 py-3 text-center">Clicks</th>
                                    <th class="px-4 py-3 text-center">Conversions</th>
                                    <th class="px-4 py-3">Created</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($links as $link)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                            {{ $link->affiliate?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">{{ $link->slug }}</code>
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 max-w-xs truncate" title="{{ $link->target_url }}">
                                            {{ Str::limit($link->target_url, 40) }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-900 dark:text-white">
                                            {{ number_format($link->clicks_count ?? 0) }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-900 dark:text-white">
                                            {{ $link->conversions_count }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                            {{ $link->created_at?->format('d.m.Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Conversions Tab --}}
            <div x-show="activeTab === 'conversions'" x-cloak>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Recent Conversions</h3>
                    <x-filament::link :href="$this->getConversionResourceUrl('index')" color="gray" icon="heroicon-m-list-bullet">
                        Full list
                    </x-filament::link>
                </div>

                <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">
                    Approve conversions and group them into payouts in the detailed view.
                </p>

                @if($conversions->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">No conversions yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Affiliate</th>
                                    <th class="px-4 py-3">Link</th>
                                    <th class="px-4 py-3">Lead</th>
                                    <th class="px-4 py-3">Restaurant</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-right">Order</th>
                                    <th class="px-4 py-3 text-right">Commission</th>
                                    <th class="px-4 py-3">Created</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($conversions as $conversion)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                            {{ $conversion->affiliate?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                            {{ $conversion->affiliateLink?->slug ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                            {{ $conversion->contactLead?->email ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                            {{ $conversion->restaurant?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <x-filament::badge :color="$this->getConversionStatusColor($conversion->status)">
                                                {{ $this->getConversionStatusLabel($conversion->status) }}
                                            </x-filament::badge>
                                        </td>
                                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white">
                                            @if($conversion->order_amount)
                                                &euro;{{ number_format($conversion->order_amount, 2, ',', '.') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white">
                                            @if($conversion->commission_amount)
                                                &euro;{{ number_format($conversion->commission_amount, 2, ',', '.') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                            {{ $conversion->created_at?->format('d.m.Y H:i') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Payouts Tab --}}
            <div x-show="activeTab === 'payouts'" x-cloak>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Recent Payouts</h3>
                    <div class="flex items-center gap-3">
                        <x-filament::link :href="$this->getPayoutResourceUrl('index')" color="gray" icon="heroicon-m-list-bullet">
                            Full list
                        </x-filament::link>
                    </div>
                </div>

                <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">
                    Create payouts from within each Affiliate's edit page under the Payouts tab.
                </p>

                @if($payouts->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">No payouts yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">#</th>
                                    <th class="px-4 py-3">Affiliate</th>
                                    <th class="px-4 py-3 text-right">Amount</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-center">Conversions</th>
                                    <th class="px-4 py-3">Reference</th>
                                    <th class="px-4 py-3">Paid At</th>
                                    <th class="px-4 py-3">Created</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($payouts as $payout)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                            {{ $payout->id }}
                                        </td>
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                            {{ $payout->affiliate?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                            &euro;{{ number_format($payout->amount ?? 0, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <x-filament::badge :color="$this->getPayoutStatusColor($payout->status)">
                                                {{ $this->getPayoutStatusLabel($payout->status) }}
                                            </x-filament::badge>
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-900 dark:text-white">
                                            {{ $payout->conversions_count }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                            {{ $payout->reference ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                            {{ $payout->paid_at?->format('d.m.Y H:i') ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                            {{ $payout->created_at?->format('d.m.Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
