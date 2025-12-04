<x-filament-panels::page>
    <div class="mb-4 text-sm text-gray-500 dark:text-gray-400">
        This page is for internal dev/QA use. It is safe to paste short notes or check off tests here.
    </div>

    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" size="lg">
                Save Dev Status
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
