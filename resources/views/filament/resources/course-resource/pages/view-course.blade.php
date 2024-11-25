<x-filament-panels::page>
    <h2 class="text-lg font-bold mb-4">{{ $record->title }}</h2>

    <div class="mb-6">
        <p><strong>Description:</strong> {{ $record->description }}</p>
        <p><strong>Mentor:</strong> {{ $record->mentor->name }}</p>
        <p><strong>Domain:</strong> {{ $record->domain->name }}</p>
        <p><strong>Verified:</strong> {{ $record->verified ? 'Yes' : 'No' }}</p>
    </div>

    <x-filament::form>
        <x-filament::card>
            {{ $this->form }}
        </x-filament::card>
    </x-filament::form>
</x-filament-panels::page>
