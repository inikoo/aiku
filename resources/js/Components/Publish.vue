<script setup lang="ts">
// This file is called in BannerWorkshop, WebsiteWorkshop
import { trans } from 'laravel-vue-i18n'
import Popover from "@/Components/Utils/Popover.vue"
import Button from '@/Components/Elements/Buttons/Button.vue'
import { computed, ref } from 'vue'
import { v4 as uuidv4 } from 'uuid';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSpinnerThird } from '@fad'
import { faAsterisk } from '@fas'
import { faRocketLaunch } from '@far'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faSpinnerThird, faAsterisk, faRocketLaunch)

const props = defineProps<{
    modelValue: string
    is_dirty: Boolean
    isLoading: true
}>()

const compRandomKey = computed(() => {
    return uuidv4()
})

const emits = defineEmits<{
    (e: 'update:modelValue', value: any): void
    (e: 'onPublish'): void
}>()

</script>

<template>
    <div class="flex items-center gap-2 relative" tabindex="-1">


        <!-- Popover (comment section) is appear if it 2nd publishing or more -->
        <Popover>
            <template #button="{ isOpen }">
                <!-- Style: Compare the hash from current data with hash from empty data -->
                <Button v-if="!isOpen"
                    :label="'Publish'"
                    :style="!is_dirty
                            ? 'disabled'
                            :  'primary'"
                    :key="compRandomKey"
                    :icon="'far fa-rocket-launch'"
                />
                <Button v-else :style="`cancel`" icon="fal fa-times" label="Cancel" />
            </template>

            <!-- Section: Popover -->
            <template #content>
                <div>
                    <div class="inline-flex items-start leading-none">
                        <FontAwesomeIcon :icon="'fas fa-asterisk'" class="font-light text-[12px] text-red-400 mr-1" />
                        <span class="capitalize">{{ trans('comment') }}</span>
                    </div>
                    <div class="py-2.5">
                        <textarea rows="3" :value="modelValue"
                            @input="$emit('update:modelValue', $event.target?.value)"
                            class="block w-64 lg:w-96 rounded-md shadow-sm border-gray-300 focus:border-gray-500 focus:ring-gray-500 sm:text-sm" />
                    </div>
                    <div class="flex justify-end">
                        <Button size="xs"  icon="far fa-rocket-launch" label="Publish" @click=" ()=>emits('onPublish')"
                            :key="modelValue.length" :style="modelValue.length ? 'primary' : 'disabled'">
                            <template #icon>
                                <FontAwesomeIcon v-if="isLoading" icon='fad fa-spinner-third' class='animate-spin'
                                    aria-hidden='true' />
                                <FontAwesomeIcon v-else icon='far fa-rocket-launch' class='' aria-hidden='true' />
                            </template>
                        </Button>
                    </div>
                </div>
            </template>
        </Popover>
    </div>
</template>

<style scoped></style>
