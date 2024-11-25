<x-filament::page>
    <h2 class="text-xl font-bold">{{ $record->title }}</h2>

    <div class="my-4">
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

    <div class="mt-6">
        <h3 class="text-lg font-bold">Module Groups</h3>

        @forelse ($record->module_groups as $group)
            <div class="mb-4 border rounded p-4">
                <h4 class="font-semibold">{{ $group['title'] }}</h4>

                @forelse ($group['modules'] as $module)
                    <div class="ml-4 mt-2">
                        <p><strong>Module:</strong> {{ $module['title'] }}</p>

                        @forelse ($module['assignments_quizzes'] as $assignment)
                            <div class="ml-6 mt-1">
                                <p><strong>{{ ucfirst($assignment['type']) }}:</strong> {{ $assignment['title'] }}</p>
                                <p>{{ $assignment['description'] }}</p>
                                <p><strong>Due Date:</strong> {{ $assignment['due_date'] }}</p>
                                <p><strong>Content:</strong> {{ json_encode($assignment['content'], JSON_PRETTY_PRINT) }}</p>
                            </div>
                        @empty
                            <p class="ml-6">No assignments or quizzes found.</p>
                        @endforelse
                    </div>
                @empty
                    <p class="ml-4">No modules found in this group.</p>
                @endforelse
            </div>
        @empty
            <p>No module groups found.</p>
        @endforelse
    </div>
</x-filament::page>
