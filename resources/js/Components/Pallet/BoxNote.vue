<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'
import { inject, ref } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faPencil, faStickyNote, faTrash, faSparkles, faLock } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import Modal from '@/Components/Utils/Modal.vue'
import PureTextarea from '@/Components/Pure/PureTextarea.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
library.add(faPencil, faStickyNote, faTrash, faSparkles, faLock)

const layout = inject('layout', {})
const props = defineProps<{
    noteData: {
        label: string
        note: string
        editable?: boolean
        bgColor?: string
        color?: string
        lockMessage?: string
    }
}>()

const isModalOpen = ref(false)
</script>

<template>
    <div class="relative w-full py-2 pt-4"
        :style="{
            backgroundColor: noteData.bgColor 
        }"
    >
        <!-- Section: Header -->
        <div class="absolute top-0 left-0 w-full flex gap-x-1 pr-4 lg:pr-0 justify-between lg:justify-normal">
            <div class="w-full flex justify-between text-xs truncate text-center rounded-br py-0.5 pl-3 pr-4" :style="{
                // borderBottom: `color-mix(in srgb, ${noteData.bgColor} 80%, black) solid 1px`,
                backgroundColor: `color-mix(in srgb, ${noteData.bgColor} 90%, white)`,
                color: '#fff'
            }">
                <div>
                    <FontAwesomeIcon icon='fas fa-sticky-note' class='' fixed-width aria-hidden='true' />
                    {{ noteData.label }}
                </div>

                <template v-if="noteData.editable">
                    <!-- Section: Actions -->
                    <div v-if="noteData.note" class="flex gap-x-1 items-center">
                        <div @click="isModalOpen = true" v-tooltip="trans('Edit note')" class="group px-0.5 cursor-pointer w-fit h-5">
                            <FontAwesomeIcon icon='fas fa-pencil' size="xs" class='text-white group-hover:text-gray-100'
                                fixed-width aria-hidden='true' />
                        </div>
                        <div v-tooltip="trans('Delete note')" class="group px-0.5 cursor-pointer w-fit h-5">
                            <FontAwesomeIcon icon='fas fa-trash' size="xs" class='text-white group-hover:text-gray-100'
                                fixed-width aria-hidden='true' />
                        </div>
                    </div>
                    <div v-else="!noteData.note" class="h-5 cursor-pointer">
                        <FontAwesomeIcon v-tooltip="trans('Add note')" icon='fas fa-sparkles' class='' fixed-width aria-hidden='true' />
                    </div>
                </template>

                <div v-else v-tooltip="noteData.lockMessage || trans('This note is not editable')" class="h-5">
                    <FontAwesomeIcon icon='fas fa-lock' class='' fixed-width aria-hidden='true' />
                </div>
            </div>

        </div>

        <!-- Section: Note -->
        <!-- <h3 class="font-medium ">Customer's note</h3> -->
        <div v-if="noteData.note" @click="noteData.editable ? isModalOpen = true : false"
            class="rounded-md mx-auto flex items-center px-4 mt-4 text-white"
            :class="noteData.editable ? 'cursor-pointer' : ''">
            <p class="text-sm">{{ noteData.note || '' }}</p>
        </div>
    </div>

    <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false">
		<div class="min-h-72 max-h-96 px-2 overflow-auto">
            <div class="text-xl font-semibold mb-2">{{ noteData.label }}'s note</div>
			<PureTextarea v-model="noteData.note" :rows="6"/>

            <div class="flex justify-end gap-x-2 mt-3">
                <Button label="cancel" :style="'tertiary'" />
                <Button label="Save" />
            </div>
		</div>
	</Modal>
</template>