<script setup lang="ts">
import { computed } from 'vue'
import { trans } from 'laravel-vue-i18n'
import { faYinYang } from '@fal';
import { capitalize } from "@/Composables/capitalize"

library.add(faYinYang);


import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";

const props = defineProps<{
    cell: {
        key: string
        type?: string  // For width of the column
        label: string | {
            type: string,
            tooltip: string,
            data: object
        }
        sortable: boolean
        hidden: boolean
        sorted: string
        onSort: any
    }
    column: {
        key: string
    }
    resource: any
}>();

function onClick() {
    if (props.cell.sortable) {
        props.cell.onSort(props.cell.key);
    }
}

const isCellNumber = computed(() => {
    return props.resource.some((aaa: any) => typeof aaa[props.column.key] === 'number')
})
</script>

<template>
    <th v-show="!cell.hidden" class="font-normal">
        <component :is="cell.sortable ? 'button' : 'div'" class="py-1" :class="[cell.type == 'avatar' || cell.type == 'icon' ? 'px-2 flex justify-center w-fit mx-auto' : 'px-6 w-full']" :dusk="cell.sortable ? `sort-${cell.key}` : null" @click.prevent="onClick">
            <slot name="pagehead" :data="{isCellNumber : isCellNumber, cell}">
                <span class="flex flex-row items-center" :class="{'justify-center': cell.type == 'avatar' || cell.type == 'icon', 'justify-end': isCellNumber}">
                    <div v-if="typeof cell.label === 'object'">
                        <FontAwesomeIcon v-if="cell.label.type === 'icon'" :title="capitalize(cell.label.tooltip)"
                            aria-hidden="true" :icon="cell.label.data" size="lg" />
                        <FontAwesomeIcon v-else :title="'icon'" aria-hidden="true" :icon="cell.label" size="lg" />
                    </div>
                    <span v-else class="capitalize">{{ cell.label ? trans(cell.label) : ''}}</span>

                    <!-- Icon: arrow for sort -->
                    <svg v-if="cell.sortable" aria-hidden="true" class="w-3 h-3 ml-2" :class="{
                        'text-gray-400': !cell.sorted,
                        'text-green-500': cell.sorted,
                    }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" :sorted="cell.sorted">
                        <path v-if="!cell.sorted" fill="currentColor"
                            d="M41 288h238c21.4 0 32.1 25.9 17 41L177 448c-9.4 9.4-24.6 9.4-33.9 0L24 329c-15.1-15.1-4.4-41 17-41zm255-105L177 64c-9.4-9.4-24.6-9.4-33.9 0L24 183c-15.1 15.1-4.4 41 17 41h238c21.4 0 32.1-25.9 17-41z" />

                        <path v-if="cell.sorted === 'asc'" fill="currentColor"
                            d="M279 224H41c-21.4 0-32.1-25.9-17-41L143 64c9.4-9.4 24.6-9.4 33.9 0l119 119c15.2 15.1 4.5 41-16.9 41z" />

                        <path v-if="cell.sorted === 'desc'" fill="currentColor"
                            d="M41 288h238c21.4 0 32.1 25.9 17 41L177 448c-9.4 9.4-24.6 9.4-33.9 0L24 329c-15.1-15.1-4.4-41 17-41z" />
                    </svg>
                </span>
            </slot>
        </component>
    </th>
</template>

