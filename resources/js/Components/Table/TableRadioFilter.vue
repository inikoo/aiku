<script setup lang="ts">
import { trans } from 'laravel-vue-i18n'
import { ref, reactive } from 'vue'
import { Popover, PopoverButton, PopoverPanel, Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCircle } from '@fal'
import { faCheckCircle } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import { onMounted } from 'vue'
import { useLocaleStore } from '@/Stores/locale'
import Button from '../Elements/Buttons/Button.vue'
import Icon from '@/Components/Icon.vue'
import { Icon as IconTS } from '@/types/Utils/Icon'
import LoadingIcon from '../Utils/LoadingIcon.vue'
library.add(faCircle, faCheckCircle)


const props = defineProps<{
    radioFilter: {
        // value?: string
        options: {
            label: string
            value: string
        }[]
    }
    value: string
    tableName: string
}>()

const emits = defineEmits<{
    (e: 'onSelectRadio', value: string): void
}>()

const isLoading = ref<string | null>(null)

// Method: Click the box
const onClickRadio = (val: string) => {
    emits('onSelectRadio', val)
}


onMounted(() => {
    // // console.log('fff', props.elements)
    // // To handle selected checkbox on hard-refresh
    // const prefix = props.tableName === 'default' ? 'radioFilter' : props.tableName + '_' + 'radioFilter'  // To handle banners_elements, users_elements, etc
    // const searchParams = new URLSearchParams(window.location.search)
    // const stateParam = searchParams.get(prefix)
    // stateParam ? selectedElement[selectedGroup.value] = stateParam.split(",") : false

    // const asdfzxc = Object.keys(props.elements)
    // console.log('rr', asdfzxc.some(elementName => window.location.search.includes(`elements[${elementName}]`)))
})

const options = [
    {
        label: 'ccc',
        value: 'ccc'
    },
    {
        label: 'ddd',
        value: 'ddd'
    },
    {
        label: 'eee',
        value: 'eee'
    }
]
</script>

<template>
    <div class="flex gap-1">
        <button v-for="radio in radioFilter?.options" @click.prevent="(e) => onClickRadio(radio.value)"
            class="hover:bg-slate-400/20 text-xs w-full sm:text-sm flex flex-auto items-center text-left gap-x-1.5 sm:gap-x-2 rounded px-2 sm:px-2 py-0.5 select-none cursor-pointer border disabled:bg-gray-300 disabled:cursor-default"
            :disabled="false">
            <LoadingIcon v-if="isLoading === radio.value" />
            <FontAwesomeIcon v-else-if="value === radio.value" icon='fas fa-check-circle' class='text-green-500' fixed-width aria-hidden='true' />
            <FontAwesomeIcon v-else icon='fal fa-circle' class='' fixed-width aria-hidden='true' />
            <span class="whitespace-nowrap">{{ radio.label }}</span>
        </button>
    </div>
</template>
