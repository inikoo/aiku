<script setup lang='ts'>
import { useLayoutStore } from '@/Stores/layout'
import { MenuButton } from "@headlessui/vue"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

const layout = useLayoutStore()
const props = defineProps<{
    activeButton: boolean
    icon: string | string[]
    label: string
}>()

</script>

<template>
    <MenuButton v-slot="{ open }"
        class="inline-flex min-w-32 max-w-full whitespace-nowrap justify-between items-center gap-x-2 rounded px-2.5 py-2 text-xs font-medium focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75"
        :class="[layout.organisations.data.find((item) => item.slug == layout.currentParams.organisation) ? 'bg-indigo-500 text-white hover:bg-indigo-600' : 'hover:bg-slate-200 text-slate-600']">
        <div class="flex items-center gap-x-1">
            <FontAwesomeIcon v-if="icon" :icon='icon' class='opacity-60 text-xs' fixed-width aria-hidden='true' />
            <!-- <FontAwesomeIcon v-else icon='fal fa-city' class='opacity-60 text-xs' fixed-width aria-hidden='true' /> -->
            {{ label }}
        </div>
        <FontAwesomeIcon icon='far fa-chevron-down' class='text-xs transition-all duration-200 ease-in-out' :class="[open ? 'rotate-180' : '']" aria-hidden='true' />
    </MenuButton>
</template>