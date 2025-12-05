<x-filament-panels::page>
    <form wire:submit="save">
        {{-- Tabs Navigation --}}
        <x-filament::tabs contained>
            <x-filament::tabs.item
                wire:click="setActiveTab('email')"
                :alpine-active="'$wire.activeTab === \'email\''"
                icon="heroicon-o-envelope"
            >
                Email
            </x-filament::tabs.item>

            <x-filament::tabs.item
                wire:click="setActiveTab('bookings')"
                :alpine-active="'$wire.activeTab === \'bookings\''"
                icon="heroicon-o-calendar-days"
            >
                Bookings
            </x-filament::tabs.item>

            <x-filament::tabs.item
                wire:click="setActiveTab('affiliates')"
                :alpine-active="'$wire.activeTab === \'affiliates\''"
                icon="heroicon-o-user-group"
            >
                Affiliates
            </x-filament::tabs.item>

            <x-filament::tabs.item
                wire:click="setActiveTab('technical')"
                :alpine-active="'$wire.activeTab === \'technical\''"
                icon="heroicon-o-wrench-screwdriver"
            >
                Technical
            </x-filament::tabs.item>
        </x-filament::tabs>

        {{-- Tab Content --}}
        <div class="mt-6" x-data="{ activeTab: @entangle('activeTab') }">
            {{-- Email Tab --}}
            <div x-show="activeTab === 'email'" x-cloak>
                <x-filament::section>
                    <x-slot name="heading">
                        Email
                    </x-slot>
                    <x-slot name="description">
                        These are the global defaults used by transactional emails (bookings, CRM, affiliates). Individual modules can override if needed.
                    </x-slot>

                    <div class="grid gap-6 md:grid-cols-2">
                        <x-filament-forms::field-wrapper
                            id="email_from_address"
                            label="From address"
                            helper-text="The sender email address for all system emails."
                            required
                        >
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    type="email"
                                    wire:model="data.email_from_address"
                                    required
                                />
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>

                        <x-filament-forms::field-wrapper
                            id="email_from_name"
                            label="From name"
                            helper-text="The sender name shown in email clients."
                            required
                        >
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    type="text"
                                    wire:model="data.email_from_name"
                                    required
                                />
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>

                        <div class="md:col-span-2">
                            <x-filament-forms::field-wrapper
                                id="email_reply_to_address"
                                label="Reply-to address"
                                helper-text="Where replies to system emails will be sent."
                            >
                                <x-filament::input.wrapper>
                                    <x-filament::input
                                        type="email"
                                        wire:model="data.email_reply_to_address"
                                    />
                                </x-filament::input.wrapper>
                            </x-filament-forms::field-wrapper>
                        </div>
                    </div>
                </x-filament::section>
            </div>

            {{-- Bookings Tab --}}
            <div x-show="activeTab === 'bookings'" x-cloak>
                <x-filament::section>
                    <x-slot name="heading">
                        Bookings & Reservations
                    </x-slot>
                    <x-slot name="description">
                        These are platform defaults for the reservation system and the public booking widget. They're used as initial defaults when a new restaurant is created, and as fallback when a restaurant has no specific booking settings configured.
                    </x-slot>

                    <div class="grid gap-6 md:grid-cols-2">
                        <x-filament-forms::field-wrapper
                            id="booking_send_customer_confirmation"
                            label="Send confirmation to customer"
                            helper-text="Send an email confirmation to customers after they make a booking."
                        >
                            <x-filament::input.wrapper>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="data.booking_send_customer_confirmation" class="sr-only peer">
                                    <div class="relative h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-primary-600 peer-focus:ring-4 peer-focus:ring-primary-500/25 dark:bg-gray-700 dark:peer-focus:ring-primary-800/25 after:absolute after:top-[2px] after:start-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white dark:border-gray-600"></div>
                                </label>
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>

                        <x-filament-forms::field-wrapper
                            id="booking_send_restaurant_notification"
                            label="Send notification to restaurant"
                            helper-text="Send an email notification to restaurants for new bookings."
                        >
                            <x-filament::input.wrapper>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="data.booking_send_restaurant_notification" class="sr-only peer">
                                    <div class="relative h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-primary-600 peer-focus:ring-4 peer-focus:ring-primary-500/25 dark:bg-gray-700 dark:peer-focus:ring-primary-800/25 after:absolute after:top-[2px] after:start-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white dark:border-gray-600"></div>
                                </label>
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>

                        <div class="md:col-span-2">
                            <x-filament-forms::field-wrapper
                                id="booking_default_notification_email"
                                label="Default restaurant notification email"
                                helper-text="Used when a restaurant has no specific notification email configured."
                            >
                                <x-filament::input.wrapper>
                                    <x-filament::input
                                        type="email"
                                        wire:model="data.booking_default_notification_email"
                                    />
                                </x-filament::input.wrapper>
                            </x-filament-forms::field-wrapper>
                        </div>
                    </div>
                </x-filament::section>
            </div>

            {{-- Affiliates Tab --}}
            <div x-show="activeTab === 'affiliates'" x-cloak>
                <x-filament::section>
                    <x-slot name="heading">
                        Affiliates
                    </x-slot>
                    <x-slot name="description">
                        Used as defaults when creating new affiliates. The commission rate is also used by the AffiliateConversion approval logic when no specific rate is set on the affiliate.
                    </x-slot>

                    <div class="grid gap-6 md:grid-cols-3">
                        <x-filament-forms::field-wrapper
                            id="affiliate_default_commission_rate"
                            label="Default commission rate (%)"
                            helper-text="Default commission percentage for new affiliates."
                            required
                        >
                            <x-filament::input.wrapper suffix="%">
                                <x-filament::input
                                    type="number"
                                    wire:model="data.affiliate_default_commission_rate"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    required
                                />
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>

                        <x-filament-forms::field-wrapper
                            id="affiliate_payout_threshold"
                            label="Payout threshold (EUR)"
                            helper-text="Minimum balance required before an affiliate can request payout."
                            required
                        >
                            <x-filament::input.wrapper prefix="€">
                                <x-filament::input
                                    type="number"
                                    wire:model="data.affiliate_payout_threshold"
                                    min="0"
                                    step="0.01"
                                    required
                                />
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>

                        <x-filament-forms::field-wrapper
                            id="affiliate_cookie_lifetime_days"
                            label="Cookie lifetime (days)"
                            helper-text="How long the affiliate tracking cookie remains valid."
                            required
                        >
                            <x-filament::input.wrapper suffix="days">
                                <x-filament::input
                                    type="number"
                                    wire:model="data.affiliate_cookie_lifetime_days"
                                    min="1"
                                    step="1"
                                    required
                                />
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>
                    </div>
                </x-filament::section>
            </div>

            {{-- Technical Tab --}}
            <div x-show="activeTab === 'technical'" x-cloak>
                <x-filament::section>
                    <x-slot name="heading">
                        Technical
                    </x-slot>
                    <x-slot name="description">
                        Internal technical flags. Be careful when changing these settings in production.
                    </x-slot>

                    <div class="grid gap-6 md:grid-cols-2">
                        <x-filament-forms::field-wrapper
                            id="technical_maintenance_mode"
                            label="Logical maintenance flag"
                            helper-text="A logical flag for maintenance mode. Does NOT call artisan down/up. Use this in middleware to show a maintenance page for non-admins."
                        >
                            <x-filament::input.wrapper>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="data.technical_maintenance_mode" class="sr-only peer">
                                    <div class="relative h-6 w-11 rounded-full bg-gray-200 peer-checked:bg-primary-600 peer-focus:ring-4 peer-focus:ring-primary-500/25 dark:bg-gray-700 dark:peer-focus:ring-primary-800/25 after:absolute after:top-[2px] after:start-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white dark:border-gray-600"></div>
                                </label>
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>

                        <x-filament-forms::field-wrapper
                            id="technical_log_level"
                            label="Log level"
                            helper-text="Controls the runtime logging level. Keep on 'info' in production."
                            required
                        >
                            <x-filament::input.wrapper>
                                <x-filament::input.select wire:model="data.technical_log_level" required>
                                    <option value="debug">Debug</option>
                                    <option value="info">Info</option>
                                    <option value="warning">Warning</option>
                                    <option value="error">Error</option>
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>
                    </div>
                </x-filament::section>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="mt-6">
            <x-filament::button type="submit" size="lg">
                Save Settings
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
