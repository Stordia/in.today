<x-filament-panels::page>
    {{-- Tabs Navigation --}}
    <x-filament::tabs contained>
        <x-filament::tabs.item
            wire:click="setActiveTab('overview')"
            :alpine-active="'$wire.activeTab === \'overview\''"
            icon="heroicon-o-home"
        >
            Overview
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="setActiveTab('affiliates')"
            :alpine-active="'$wire.activeTab === \'affiliates\''"
            icon="heroicon-o-user-group"
            :badge="$affiliates->count()"
        >
            Affiliates
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="setActiveTab('links')"
            :alpine-active="'$wire.activeTab === \'links\''"
            icon="heroicon-o-link"
            :badge="$links->count()"
        >
            Links
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="setActiveTab('conversions')"
            :alpine-active="'$wire.activeTab === \'conversions\''"
            icon="heroicon-o-arrow-trending-up"
            :badge="$conversions->count()"
        >
            Conversions
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="setActiveTab('payouts')"
            :alpine-active="'$wire.activeTab === \'payouts\''"
            icon="heroicon-o-banknotes"
            :badge="$payouts->count()"
        >
            Payouts
        </x-filament::tabs.item>
    </x-filament::tabs>

    {{-- Tab Content --}}
    <div class="mt-6" x-data="{ activeTab: @entangle('activeTab') }">
        {{-- Overview Tab --}}
        <div x-show="activeTab === 'overview'" x-cloak class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-2">
                {{-- How Affiliates Work --}}
                <x-filament::section>
                    <x-slot name="heading">
                        How Affiliates Work
                    </x-slot>
                    <x-slot name="description">
                        Short explanation of the flow:
                    </x-slot>

                    <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400 list-disc list-inside">
                        <li>Affiliates register and receive unique tracking links.</li>
                        <li>Customers arrive via <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded font-mono">/go/{slug}</code>.</li>
                        <li>Admin reviews and approves conversions with order amounts and commissions.</li>
                        <li>Approved conversions are grouped into payouts for payment.</li>
                        <li>Payouts can be tracked and marked as paid when transferred.</li>
                    </ul>
                </x-filament::section>

                {{-- Recent Conversions --}}
                <x-filament::section>
                    <x-slot name="heading">
                        Recent Conversions
                    </x-slot>
                    <x-slot name="headerEnd">
                        <x-filament::link
                            :href="$this->getConversionResourceUrl('index')"
                            icon="heroicon-m-arrow-right"
                            icon-position="after"
                            size="sm"
                        >
                            View all
                        </x-filament::link>
                    </x-slot>

                    @if($recentConversions->isEmpty())
                        <x-filament-tables::empty-state
                            icon="heroicon-o-arrow-trending-up"
                            heading="No conversions yet"
                        />
                    @else
                        <div class="-mx-6 -mb-6 overflow-hidden">
                            <x-filament-tables::table>
                                <x-slot name="header">
                                    <x-filament-tables::header-cell>Affiliate</x-filament-tables::header-cell>
                                    <x-filament-tables::header-cell>Link</x-filament-tables::header-cell>
                                    <x-filament-tables::header-cell>Lead</x-filament-tables::header-cell>
                                    <x-filament-tables::header-cell alignment="center">Status</x-filament-tables::header-cell>
                                    <x-filament-tables::header-cell alignment="end">Commission</x-filament-tables::header-cell>
                                    <x-filament-tables::header-cell alignment="end">Created</x-filament-tables::header-cell>
                                </x-slot>

                                @foreach($recentConversions as $conversion)
                                    <x-filament-tables::row>
                                        <x-filament-tables::cell>
                                            <div class="px-3 py-4">
                                                <span class="text-sm font-medium text-gray-950 dark:text-white">
                                                    {{ $conversion->affiliate?->name ?? '—' }}
                                                </span>
                                            </div>
                                        </x-filament-tables::cell>
                                        <x-filament-tables::cell>
                                            <div class="px-3 py-4">
                                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $conversion->affiliateLink?->slug ?? '—' }}
                                                </span>
                                            </div>
                                        </x-filament-tables::cell>
                                        <x-filament-tables::cell>
                                            <div class="px-3 py-4">
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $conversion->contactLead?->email ?? '—' }}
                                                </span>
                                            </div>
                                        </x-filament-tables::cell>
                                        <x-filament-tables::cell>
                                            <div class="px-3 py-4 text-center">
                                                <x-filament::badge :color="$this->getConversionStatusColor($conversion->status)" size="sm">
                                                    {{ $this->getConversionStatusLabel($conversion->status) }}
                                                </x-filament::badge>
                                            </div>
                                        </x-filament-tables::cell>
                                        <x-filament-tables::cell>
                                            <div class="px-3 py-4 text-end">
                                                <span class="text-sm text-gray-950 dark:text-white">
                                                    @if($conversion->commission_amount)
                                                        {{ Number::currency($conversion->commission_amount, 'EUR', 'de') }}
                                                    @else
                                                        —
                                                    @endif
                                                </span>
                                            </div>
                                        </x-filament-tables::cell>
                                        <x-filament-tables::cell>
                                            <div class="px-3 py-4 text-end">
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $conversion->created_at?->format('d.m.Y H:i') }}
                                                </span>
                                            </div>
                                        </x-filament-tables::cell>
                                    </x-filament-tables::row>
                                @endforeach
                            </x-filament-tables::table>
                        </div>
                    @endif
                </x-filament::section>
            </div>
        </div>

        {{-- Affiliates Tab --}}
        <div x-show="activeTab === 'affiliates'" x-cloak>
            <x-filament::section>
                <x-slot name="heading">
                    Affiliates
                </x-slot>
                <x-slot name="headerEnd">
                    <div class="flex items-center gap-3">
                        <x-filament::link :href="$this->getAffiliateResourceUrl('index')" icon="heroicon-m-list-bullet" size="sm">
                            Full list
                        </x-filament::link>
                        <x-filament::button :href="$this->getAffiliateResourceUrl('create')" tag="a" icon="heroicon-m-plus" size="sm">
                            New affiliate
                        </x-filament::button>
                    </div>
                </x-slot>

                @if($affiliates->isEmpty())
                    <x-filament-tables::empty-state
                        icon="heroicon-o-user-group"
                        heading="No affiliates yet"
                        description="Get started by creating your first affiliate partner."
                    />
                @else
                    <div class="-mx-6 -mb-6 overflow-hidden">
                        <x-filament-tables::table>
                            <x-slot name="header">
                                <x-filament-tables::header-cell>Name</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell>Email</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell>Type</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell alignment="center">Links</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell alignment="center">Conversions</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell alignment="end">Total Paid</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell alignment="end">Actions</x-filament-tables::header-cell>
                            </x-slot>

                            @foreach($affiliates as $affiliate)
                                <x-filament-tables::row>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4">
                                            <span class="text-sm font-medium text-gray-950 dark:text-white">
                                                {{ $affiliate->name }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $affiliate->contact_email ?? '—' }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4">
                                            <x-filament::badge :color="$this->getAffiliateTypeBadgeColor($affiliate->type)" size="sm">
                                                {{ ucfirst($affiliate->type) }}
                                            </x-filament::badge>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4 text-center">
                                            <span class="text-sm text-gray-950 dark:text-white">
                                                {{ $affiliate->links_count }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4 text-center">
                                            <span class="text-sm text-gray-950 dark:text-white">
                                                {{ $affiliate->conversions_count }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4 text-end">
                                            <span class="text-sm text-gray-950 dark:text-white">
                                                {{ Number::currency($affiliate->paid_commission ?? 0, 'EUR', 'de') }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4 flex items-center justify-end gap-1">
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
                                    </x-filament-tables::cell>
                                </x-filament-tables::row>
                            @endforeach
                        </x-filament-tables::table>
                    </div>
                @endif
            </x-filament::section>
        </div>

        {{-- Links Tab --}}
        <div x-show="activeTab === 'links'" x-cloak>
            <x-filament::section>
                <x-slot name="heading">
                    Recent Links
                </x-slot>
                <x-slot name="description">
                    Links are created from within each Affiliate's edit page.
                </x-slot>
                <x-slot name="headerEnd">
                    <x-filament::link :href="$this->getLinkResourceUrl('index')" icon="heroicon-m-list-bullet" size="sm">
                        Full list
                    </x-filament::link>
                </x-slot>

                @if($links->isEmpty())
                    <x-filament-tables::empty-state
                        icon="heroicon-o-link"
                        heading="No links yet"
                        description="Create links from an affiliate's edit page."
                    />
                @else
                    <div class="-mx-6 -mb-6 overflow-hidden">
                        <x-filament-tables::table>
                            <x-slot name="header">
                                <x-filament-tables::header-cell>Affiliate</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell>Slug</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell>Target URL</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell alignment="center">Clicks</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell alignment="center">Conversions</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell alignment="end">Created</x-filament-tables::header-cell>
                            </x-slot>

                            @foreach($links as $link)
                                <x-filament-tables::row>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4">
                                            <span class="text-sm font-medium text-gray-950 dark:text-white">
                                                {{ $link->affiliate?->name ?? '—' }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4">
                                            <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded font-mono text-gray-600 dark:text-gray-300">{{ $link->slug }}</code>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4 max-w-xs truncate" title="{{ $link->target_url }}">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ Str::limit($link->target_url, 40) }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4 text-center">
                                            <span class="text-sm text-gray-950 dark:text-white">
                                                {{ number_format($link->clicks_count ?? 0) }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4 text-center">
                                            <span class="text-sm text-gray-950 dark:text-white">
                                                {{ $link->conversions_count }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4 text-end">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $link->created_at?->format('d.m.Y') }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                </x-filament-tables::row>
                            @endforeach
                        </x-filament-tables::table>
                    </div>
                @endif
            </x-filament::section>
        </div>

        {{-- Conversions Tab --}}
        <div x-show="activeTab === 'conversions'" x-cloak>
            <x-filament::section>
                <x-slot name="heading">
                    Recent Conversions
                </x-slot>
                <x-slot name="description">
                    Approve conversions and group them into payouts in the detailed view.
                </x-slot>
                <x-slot name="headerEnd">
                    <x-filament::link :href="$this->getConversionResourceUrl('index')" icon="heroicon-m-list-bullet" size="sm">
                        Full list
                    </x-filament::link>
                </x-slot>

                @if($conversions->isEmpty())
                    <x-filament-tables::empty-state
                        icon="heroicon-o-arrow-trending-up"
                        heading="No conversions yet"
                        description="Conversions are created when leads convert via affiliate links."
                    />
                @else
                    <div class="-mx-6 -mb-6 overflow-hidden overflow-x-auto">
                        <x-filament-tables::table>
                            <x-slot name="header">
                                <x-filament-tables::header-cell>Affiliate</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell>Link</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell>Lead</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell>Restaurant</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell alignment="center">Status</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell alignment="end">Order</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell alignment="end">Commission</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell alignment="end">Created</x-filament-tables::header-cell>
                            </x-slot>

                            @foreach($conversions as $conversion)
                                <x-filament-tables::row>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4">
                                            <span class="text-sm font-medium text-gray-950 dark:text-white">
                                                {{ $conversion->affiliate?->name ?? '—' }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $conversion->affiliateLink?->slug ?? '—' }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $conversion->contactLead?->email ?? '—' }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $conversion->restaurant?->name ?? '—' }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4 text-center">
                                            <x-filament::badge :color="$this->getConversionStatusColor($conversion->status)" size="sm">
                                                {{ $this->getConversionStatusLabel($conversion->status) }}
                                            </x-filament::badge>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4 text-end">
                                            <span class="text-sm text-gray-950 dark:text-white">
                                                @if($conversion->order_amount)
                                                    {{ Number::currency($conversion->order_amount, 'EUR', 'de') }}
                                                @else
                                                    —
                                                @endif
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4 text-end">
                                            <span class="text-sm text-gray-950 dark:text-white">
                                                @if($conversion->commission_amount)
                                                    {{ Number::currency($conversion->commission_amount, 'EUR', 'de') }}
                                                @else
                                                    —
                                                @endif
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4 text-end">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $conversion->created_at?->format('d.m.Y H:i') }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                </x-filament-tables::row>
                            @endforeach
                        </x-filament-tables::table>
                    </div>
                @endif
            </x-filament::section>
        </div>

        {{-- Payouts Tab --}}
        <div x-show="activeTab === 'payouts'" x-cloak>
            <x-filament::section>
                <x-slot name="heading">
                    Recent Payouts
                </x-slot>
                <x-slot name="description">
                    Create payouts from within each Affiliate's edit page under the Payouts tab.
                </x-slot>
                <x-slot name="headerEnd">
                    <x-filament::link :href="$this->getPayoutResourceUrl('index')" icon="heroicon-m-list-bullet" size="sm">
                        Full list
                    </x-filament::link>
                </x-slot>

                @if($payouts->isEmpty())
                    <x-filament-tables::empty-state
                        icon="heroicon-o-banknotes"
                        heading="No payouts yet"
                        description="Create payouts from an affiliate's view page."
                    />
                @else
                    <div class="-mx-6 -mb-6 overflow-hidden overflow-x-auto">
                        <x-filament-tables::table>
                            <x-slot name="header">
                                <x-filament-tables::header-cell>#</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell>Affiliate</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell alignment="end">Amount</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell alignment="center">Status</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell alignment="center">Conversions</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell>Reference</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell alignment="end">Paid At</x-filament-tables::header-cell>
                                <x-filament-tables::header-cell alignment="end">Created</x-filament-tables::header-cell>
                            </x-slot>

                            @foreach($payouts as $payout)
                                <x-filament-tables::row>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $payout->id }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4">
                                            <span class="text-sm font-medium text-gray-950 dark:text-white">
                                                {{ $payout->affiliate?->name ?? '—' }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4 text-end">
                                            <span class="text-sm font-medium text-gray-950 dark:text-white">
                                                {{ Number::currency($payout->amount ?? 0, 'EUR', 'de') }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4 text-center">
                                            <x-filament::badge :color="$this->getPayoutStatusColor($payout->status)" size="sm">
                                                {{ $this->getPayoutStatusLabel($payout->status) }}
                                            </x-filament::badge>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4 text-center">
                                            <span class="text-sm text-gray-950 dark:text-white">
                                                {{ $payout->conversions_count }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $payout->reference ?? '—' }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4 text-end">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $payout->paid_at?->format('d.m.Y H:i') ?? '—' }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                    <x-filament-tables::cell>
                                        <div class="px-3 py-4 text-end">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $payout->created_at?->format('d.m.Y') }}
                                            </span>
                                        </div>
                                    </x-filament-tables::cell>
                                </x-filament-tables::row>
                            @endforeach
                        </x-filament-tables::table>
                    </div>
                @endif
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>
