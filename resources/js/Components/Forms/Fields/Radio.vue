<template>
    <div>
        <label class="text-base font-semibold text-gray-800 capitalize">{{ fieldName }}</label>
        <p class="text-xs text-gray-500 capitalize italic">{{ form[fieldName] }}</p>
        <fieldset class="mt-4 select-none">
            <legend class="sr-only">Notification method</legend>
            <div class="space-y-2">
                <div v-for="option in options" :key="option.id" class="flex items-center">
                    <input :id="option.id" name="notification-method" type="radio" :checked="option.title == selectedRadio"
                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600 cursor-pointer" />
                    <label :for="option.id" class="ml-3 block text-sm font-medium leading-6 text-gray-800 cursor-pointer">
                        {{ option.title }}
                    </label>
                </div>
            </div>
        </fieldset>
    </div>
</template>
  
<script setup>

const props = defineProps(['form', 'fieldName'])
const selectedRadio = "Push notification"

const options = [
    { id: 'email', title: 'Email' },
    { id: 'sms', title: 'Phone (SMS)' },
    { id: 'push', title: 'Push notification' },
]
</script>