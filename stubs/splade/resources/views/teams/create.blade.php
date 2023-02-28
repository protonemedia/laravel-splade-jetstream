@seoTitle(__('Create Team'))

<x-app-layout>
    <x-slot:header>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Team') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <x-splade-form :action="route('teams.store')">
            <x-form-section>
                <x-slot:title>
                    {{ __('Team Details') }}
                </x-slot>

                <x-slot:description>
                    {{ __('Create a new team to collaborate with others on projects.') }}
                </x-slot>

                <x-slot:form>
                    <div class="col-span-6">
                        <x-splade-group :label="__('Team Owner')">
                            <div class="flex items-center mt-2">
                                <img class="object-cover w-12 h-12 rounded-full" src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}">

                                <div class="ml-4 leading-tight">
                                    <div class="text-gray-900 dark:text-white">{{ auth()->user()->name }}</div>
                                    <div class="text-gray-700 dark:text-gray-300 text-sm">
                                        {{ auth()->user()->email }}
                                    </div>
                                </div>
                            </div>
                        </x-splade-group>
                    </div>

                    <div class="col-span-6 sm:col-span-4">
                        <x-splade-input id="name" name="name" :label="__('Team Name')" autofocus />
                    </div>
                </x-slot>

                <x-slot:actions>
                    <x-splade-submit :label="__('Create')" />
                </x-slot>
            </x-form-section>
        </x-splade-form>
    </div>
</x-app-layout>
