<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 02 Sep 2026 Malaga, Spain
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { router } from "@inertiajs/vue3"
import { computed, ref } from "vue"
import { ctrans } from "@/Composables/useTrans"
import Select from "primevue/select"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faUserHardHat, faTimes } from "@fal"

library.add(faUserHardHat, faTimes)

type Artisan = { id: number, name: string }

const props = defineProps<{
    data: {
        current: Artisan[]
        options: Artisan[]
        attach_route: { name: string, parameters: (number | string)[] } | null
        detach_route: { name: string, parameters: (number | string)[] } | null
    }
    label?: string
    empty?: string
    plain?: boolean
}>()

const adding = ref<number | null>(null)
const available = computed(() => props.data.options.filter(option => !props.data.current.some(artisan => artisan.id === option.id)))

function attach() {
    if (adding.value === null || !props.data.attach_route) return
    router.post(route(props.data.attach_route.name, props.data.attach_route.parameters), { employee_id: adding.value }, {
        preserveScroll: true,
        onFinish: () => { adding.value = null },
    })
}

function detach(artisan: Artisan) {
    if (!props.data.detach_route) return
    router.delete(route(props.data.detach_route.name, [...props.data.detach_route.parameters, artisan.id]), { preserveScroll: true })
}
</script>

<template>
    <div class="mx-4 mt-4 flex flex-wrap items-center gap-2 text-sm">
        <span class="flex items-center gap-1.5 text-gray-500">
            <FontAwesomeIcon icon="fal fa-user-hard-hat" fixed-width aria-hidden="true" />
            {{ label ?? ctrans("Usually made by") }}
        </span>
        <span v-if="!data.current.length" class="text-gray-400">{{ empty ?? ctrans("nobody yet") }}</span>
        <span
            v-for="(artisan, index) in data.current"
            :key="artisan.id"
            class="flex items-center gap-1 rounded-full border px-2.5 py-px text-xs"
            :class="index === 0 && !plain ? 'border-gray-500 bg-gray-100 font-medium text-gray-800' : 'border-gray-300 text-gray-600'">
            {{ artisan.name }}
            <button v-if="data.detach_route" type="button" class="text-gray-400 hover:text-red-600" :title="ctrans('Remove')" @click="detach(artisan)">
                <FontAwesomeIcon icon="fal fa-times" fixed-width aria-hidden="true" />
            </button>
        </span>
        <Select
            v-if="data.attach_route && available.length"
            v-model="adding"
            :options="available"
            optionLabel="name"
            optionValue="id"
            filter
            size="small"
            :placeholder="ctrans('Add artisan') + '…'"
            :aria-label="ctrans('Add artisan')"
            class="w-48"
            @update:modelValue="attach" />
    </div>
</template>
