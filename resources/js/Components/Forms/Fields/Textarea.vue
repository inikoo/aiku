<template>
    <div>
        <label for="about" class="block text-sm font-medium text-gray-700">
            <!-- {{ pageBody.layout.profile.fields.about.label }} -->
        </label>
        <div class="mt-1">
            <textarea id="about" name="about" rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm" />
        </div>
        <p class="mt-2 text-xs italic text-gray-500">
            <!-- {{ pageBody.layout.profile.fields.about.notes }} -->
        </p>
    </div>
</template>