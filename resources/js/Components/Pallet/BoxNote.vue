<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'
import { inject, ref } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faPencil, faStickyNote, faTrash, faSparkles, faLock } from '@fas'
import { faTimes } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import Modal from '@/Components/Utils/Modal.vue'
import PureTextarea from '@/Components/Pure/PureTextarea.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import axios from 'axios'
import { routeType } from '@/types/route'
import { notify } from '@kyvg/vue3-notification'
import { PDRNotes } from '@/types/Pallet'
library.add(faPencil, faStickyNote, faTrash, faSparkles, faLock, faTimes)

const layout = inject('layout', {})
const props = defineProps<{
    noteData: PDRNotes
    updateRoute: routeType
}>()

// Section: Modal Note
const noteModalValue = ref(props.noteData.note)
const isModalOpen = ref(false)
const isSubmitNoteLoading = ref(false)
const onSubmitNote = async () => {
    isSubmitNoteLoading.value = true
    try {
        const response = await axios.patch(route(props.updateRoute.name, props.updateRoute.parameters), {
            [props.noteData.field]: noteModalValue.value
        })
        props.noteData.note = noteModalValue.value
    } catch (error) {
        notify({
			title: "Failed",
			text: "Failed to update the note, try again.",
			type: "error",
		})
    }

    isSubmitNoteLoading.value = false
    isModalOpen.value = false
}

const fallbackBgColor = '#f9fafb'  // Background
const fallbackColor = '#374151'  // Color
</script>

<template>
    <div class="relative w-full py-2 pt-4"
        :style="{
            backgroundColor: noteData.bgColor || fallbackBgColor
        }"
    >
        <!-- Section: Header -->
        <div class="absolute top-0 left-0 w-full flex gap-x-1 pr-4 lg:pr-0 justify-between lg:justify-normal">
            <div class="w-full flex justify-between text-xs truncate text-center py-0.5 pl-3 pr-4" :style="{
                // borderBottom: `color-mix(in srgb, ${noteData.bgColor} 80%, black) solid 1px`,
                backgroundColor: noteData.bgColor ? `color-mix(in srgb, ${noteData.bgColor} 80%, white)` : fallbackBgColor,
                color: fallbackColor + '77'
            }">
                <div>
                    <FontAwesomeIcon icon='fas fa-sticky-note' class='' fixed-width aria-hidden='true' />
                    {{ noteData.label }}
                </div>

                    <!-- Section: Actions -->
                <template v-if="noteData.editable">
                    <!-- Icon: pencil (edit) -->
                    <div v-if="noteData.note" @click="isModalOpen = true" v-tooltip="trans('Edit note')" class="group px-0.5 cursor-pointer w-fit h-5">
                        <FontAwesomeIcon icon='fas fa-pencil' size="xs" class='group-hover:text-gray-100'
                            fixed-width aria-hidden='true'
                            :style="{
                                color: fallbackColor
                            }"
                        />
                    </div>

                    <!-- Icon: -->
                    <div v-else="!noteData.note" @click="isModalOpen = true" class="h-5 cursor-pointer">
                        <FontAwesomeIcon v-tooltip="trans('Add note')" icon='fas fa-sparkles' class='' fixed-width aria-hidden='true'
                            :style="{
                                    color: fallbackColor
                                }"
                        />
                    </div>
                </template>

                <!-- Icon: Lock -->
                <div v-else v-tooltip="noteData.lockMessage || trans('This note is not editable')" class="h-5">
                    <FontAwesomeIcon icon='fas fa-lock' class='text-gray-400' fixed-width aria-hidden='true' />
                </div>
            </div>

        </div>

        <!-- Section: Note -->
        <!-- <h3 class="font-medium ">Customer's note</h3> -->
        <div @dblclick="noteData.editable ? isModalOpen = true : false"
            v-tooltip="trans('Double click to edit')"
            class="rounded-md mx-auto flex items-center px-4 mt-4 text-white"
            :class="noteData.editable ? 'cursor-pointer' : ''">
            <p class="text-sm hover:text-gray-300"
                :style="{
                    color: fallbackColor
                }"
            >
                {{ noteData.note || '' }}
            </p>
        </div>
    </div>

    <Modal :isOpen="isModalOpen" @onClose="() => (isModalOpen = false, noteModalValue = noteData.note)">
		<div class="min-h-72 max-h-96 px-2 overflow-auto">
            <div class="text-xl font-semibold mb-2">{{ noteData.label }}'s note</div>
			<div class="relative">
                <div v-if="noteModalValue" @click="() => noteModalValue = ''" class="absolute top-1 right-1 text-red-400 hover:text-red-600 text-xxs cursor-pointer">
                    Clear
                </div>
                <PureTextarea v-model="noteModalValue" counter rows="6" @keydown.ctrl.enter="() => onSubmitNote()" maxLength="5000" />
            </div>

            <div class="flex justify-end gap-x-2 mt-3">
                <Button label="cancel" @click="() => (isModalOpen = false, noteModalValue = noteData.note)" :style="'tertiary'" />
                <Button label="Save" @click="() => onSubmitNote()" :loading="isSubmitNoteLoading" :disabled="noteModalValue == noteData.note" />
            </div>
		</div>
	</Modal>
</template>