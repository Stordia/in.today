<x-filament-panels::page>
    {{-- Lead Summary Card --}}
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-user class="w-5 h-5 text-gray-400" />
                {{ $this->record->name }}
            </div>
        </x-slot>

        <x-slot name="description">
            Sending email to this lead
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-500 dark:text-gray-400">Email</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $this->record->email }}</p>
            </div>

            @if ($this->record->restaurant_name)
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Business</span>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $this->record->restaurant_name }}</p>
                </div>
            @endif

            @if ($this->record->location)
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Location</span>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $this->record->location }}</p>
                </div>
            @endif

            @if ($this->record->type)
                <div>
                    <span class="text-gray-500 dark:text-gray-400">Type</span>
                    <p class="font-medium text-gray-900 dark:text-white">
                        {{ \App\Models\ContactLead::TYPE_OPTIONS[$this->record->type] ?? $this->record->type }}
                    </p>
                </div>
            @endif

            @if (!empty($this->record->services))
                <div class="md:col-span-2 lg:col-span-4">
                    <span class="text-gray-500 dark:text-gray-400">Services Requested</span>
                    <div class="flex flex-wrap gap-1 mt-1">
                        @foreach ($this->record->services as $service)
                            <span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-1.5 min-w-[theme(spacing.5)] py-0.5 bg-primary-50 text-primary-600 ring-primary-600/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/30">
                                {{ $service }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>

    {{-- Email Form --}}
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-envelope class="w-5 h-5 text-gray-400" />
                Compose Email
            </div>
        </x-slot>

        <form wire:submit="send">
            {{ $this->form }}

            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <x-filament::button type="submit" icon="heroicon-o-paper-airplane">
                    Send Email
                </x-filament::button>

                <x-filament::link
                    :href="\App\Filament\Resources\ContactLeadResource::getUrl('view', ['record' => $this->record])"
                    color="gray"
                >
                    Cancel
                </x-filament::link>
            </div>
        </form>
    </x-filament::section>
</x-filament-panels::page>
