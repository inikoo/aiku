<!--
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { notify } from '@kyvg/vue3-notification'
import InputText from 'primevue/inputtext'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { ctrans } from '@/Composables/useTrans'
import { routeType } from '@/types/route'
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCheck, faHandshakeSlash } from '@fal'

library.add(faCheck, faHandshakeSlash)

const props = defineProps<{
    differences: {
        flags: {
            key: string
            title: string
            found: string
            agreed: string
            accepted: { reason: string, by: string, at: string } | null
        }[]
        route: routeType
    }
}>()

const reasons = ref<Record<string, string>>({})
const saving = ref<string | null>(null)

const accept = (key: string) => {
    saving.value = key
    router.post(route(props.differences.route.name, props.differences.route.parameters), { reasons: { [key]: reasons.value[key] ?? '' } }, {
        preserveScroll: true,
        onError: (errors) => notify({ title: ctrans('Not accepted'), text: Object.values(errors).join(' '), type: 'error' }),
        onFinish: () => (saving.value = null),
    })
}
</script>

<template>
    <section class="rounded-lg border border-red-200 bg-white text-sm text-gray-700">
        <header class="flex items-center gap-2 border-b border-red-100 px-3 py-2">
            <FontAwesomeIcon icon="fal fa-handshake-slash" class="text-red-500" fixed-width aria-hidden="true" />
            <span class="font-semibold text-gray-900">{{ ctrans('Different from what was agreed') }}</span>
            <span class="text-gray-500">{{ ctrans('Purchasing must accept each one with a reason before this is submitted or paid') }}</span>
        </header>
        <table class="w-full">
            <thead class="text-left text-xs text-gray-500">
                <tr>
                    <th class="px-3 py-1 font-medium">{{ ctrans('What') }}</th>
                    <th class="px-3 py-1 font-medium">{{ ctrans('On the document') }}</th>
                    <th class="px-3 py-1 font-medium">{{ ctrans('Agreed') }}</th>
                    <th class="px-3 py-1 font-medium">{{ ctrans('Reason') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="flag in differences.flags" :key="flag.key" class="border-t border-gray-100 align-middle">
                    <td class="px-3 py-1.5">{{ flag.title }}</td>
                    <td class="px-3 py-1.5 tabular-nums" :class="flag.accepted ? '' : 'font-semibold text-red-600'">{{ flag.found }}</td>
                    <td class="px-3 py-1.5 tabular-nums">{{ flag.agreed }}</td>
                    <td class="px-3 py-1.5">
                        <span v-if="flag.accepted" class="text-gray-600">
                            {{ flag.accepted.reason }} <span class="text-xs text-gray-400">· {{ flag.accepted.by }}</span>
                        </span>
                        <div v-else class="flex items-center gap-2">
                            <InputText v-model="reasons[flag.key]" size="small" class="w-full" :placeholder="ctrans('Why we accept it')" />
                            <Button type="tertiary" size="xs" :label="ctrans('Accept')" :loading="saving === flag.key" :disabled="(reasons[flag.key] ?? '').trim().length < 5" @click="accept(flag.key)">
                                <template #icon>
                                    <FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
                                </template>
                            </Button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </section>
</template>
