<script setup lang="ts">
import { ref, watch } from 'vue'
import Dialog from 'primevue/dialog'
import Checkbox from 'primevue/checkbox'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { ctrans } from '@/Composables/useTrans'

const props = defineProps<{
    jobs: string | null
    processing?: boolean
}>()

const emits = defineEmits<{
    (e: 'confirm'): void
    (e: 'cancel'): void
}>()

const understood = ref(false)

watch(() => props.jobs, () => understood.value = false)
</script>

<template>
    <Dialog
        :visible="!!jobs"
        modal
        :header="ctrans('This recipe has open job orders')"
        :style="{ width: '34rem' }"
        :breakpoints="{ '640px': '95vw' }"
        @update:visible="visible => !visible && emits('cancel')">
        <div class="space-y-3 text-sm text-gray-700">
            <p>{{ ctrans('Saving this change also changes the job orders already open for it:') }}</p>
            <ul class="list-disc space-y-1 pl-5">
                <li>{{ ctrans('Steps nobody has started, or started with nothing recorded yet, change to the new amounts straight away. The tablet asks the artisan for the new amount.') }}</li>
                <li>{{ ctrans('Steps with work already recorded keep their old count, so nothing already counted or paid changes. Check those on the job order.') }}</li>
                <li>{{ ctrans('Steps taken out of the recipe are removed from open job orders where nobody worked on them.') }}</li>
            </ul>
            <p class="rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-amber-900">{{ jobs }}</p>
            <p class="text-gray-500">{{ ctrans('Recipes are meant to be settled before jobs go to the floor. Change them while jobs are open only when you have to.') }}</p>
            <label class="flex cursor-pointer items-center gap-2 font-medium">
                <Checkbox v-model="understood" binary />
                {{ ctrans('I understand the open job orders will change') }}
            </label>
        </div>
        <template #footer>
            <Button type="tertiary" :label="ctrans('Cancel')" @click="emits('cancel')" />
            <Button
                type="tertiary"
                :label="ctrans('Save and update open jobs')"
                :loading="processing"
                :disabled="!understood"
                @click="emits('confirm')" />
        </template>
    </Dialog>
</template>
