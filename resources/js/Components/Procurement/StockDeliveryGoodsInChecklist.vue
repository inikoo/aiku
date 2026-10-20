<!--
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { notify } from '@kyvg/vue3-notification'
import Dialog from 'primevue/dialog'
import Select from 'primevue/select'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { ctrans } from '@/Composables/useTrans'
import { routeType } from '@/types/route'
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCheck, faTimes, faTasks, faExclamationTriangle } from '@fal'

library.add(faCheck, faTimes, faTasks, faExclamationTriangle)

interface Check {
    key: string
    label: string
    detail: string | null
    category: string
    severity: string
    result: 'passed' | 'failed' | null
    checked_by: string | null
    checked_at: string | null
    incident_id: number | null
}

const props = defineProps<{
    data: {
        sections: { key: string, title: string, subtitle: string | null, purchase_order: string | null, checks: Check[] }[]
        total: number
        checked: number
        failed: number
        can_check: boolean
        incident_types: { value: string, label: string }[]
        route: routeType
    }
}>()

const saving = ref<string | null>(null)
const failing = ref<Check | null>(null)
const incident = ref({ incident_type: 'packing_labels', incident_quantity: null as number | null, body: '' })

const isFailDialogOpen = computed({
    get: () => failing.value !== null,
    set: (open: boolean) => { if (!open) failing.value = null },
})

const record = (check: Check, result: 'passed' | 'failed', extra: Record<string, unknown> = {}) => {
    saving.value = check.key
    router.post(route(props.data.route.name, props.data.route.parameters), { key: check.key, result, ...extra }, {
        preserveScroll: true,
        only: ['checklist', 'tabs'],
        onSuccess: () => { failing.value = null },
        onError: (errors) => notify({ title: ctrans('Not saved'), text: Object.values(errors).join(' '), type: 'error' }),
        onFinish: () => (saving.value = null),
    })
}

const openFail = (check: Check) => {
    incident.value = { incident_type: 'packing_labels', incident_quantity: null, body: '' }
    failing.value = check
}

const formatDate = (value: string | null) => value ? new Date(value).toLocaleString() : ''
</script>

<template>
    <div class="px-4 py-4 text-sm text-gray-700">
        <div v-if="!data.total" class="py-8 text-center text-gray-500">
            {{ ctrans('Procurement has recorded no packing or product notes for the suppliers and products on this delivery') }}
        </div>

        <template v-else>
            <p class="mb-3 text-gray-500">
                {{ ctrans(':checked of :total checked', { checked: data.checked, total: data.total }) }}
                <span v-if="data.failed" class="text-red-600"> · {{ ctrans(':count failed, an incident was opened for procurement', { count: data.failed }) }}</span>
            </p>

            <table class="w-full">
                <thead class="text-left text-xs text-gray-500">
                    <tr class="border-b border-gray-200">
                        <th class="px-3 py-1 font-medium">{{ ctrans('Check') }}</th>
                        <th class="px-3 py-1 font-medium">{{ ctrans('What was agreed') }}</th>
                        <th class="px-3 py-1 font-medium">{{ ctrans('Result') }}</th>
                        <th class="px-3 py-1" />
                    </tr>
                </thead>
                <tbody v-for="section in data.sections" :key="section.key">
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <td colspan="4" class="px-3 py-1.5">
                            <span class="font-semibold text-gray-900">{{ section.title }}</span>
                            <span v-if="section.subtitle" class="ml-2 text-gray-500">{{ section.subtitle }}</span>
                            <span v-if="section.purchase_order" class="ml-2 text-xs text-gray-500">{{ ctrans('On purchase order') }} {{ section.purchase_order }}</span>
                        </td>
                    </tr>
                    <tr v-for="check in section.checks" :key="check.key" class="border-b border-gray-100 align-top">
                        <td class="px-3 py-1.5">
                            <FontAwesomeIcon v-if="check.severity !== 'info'" icon="fal fa-exclamation-triangle" :class="check.severity === 'critical' ? 'text-red-500' : 'text-amber-500'" fixed-width aria-hidden="true" />
                            {{ check.label }}
                        </td>
                        <td class="whitespace-pre-line px-3 py-1.5 text-gray-600">{{ check.detail }}</td>
                        <td class="whitespace-nowrap px-3 py-1.5">
                            <span v-if="check.result === 'passed'" class="text-green-600"><FontAwesomeIcon icon="fal fa-check" fixed-width aria-hidden="true" /> {{ ctrans('Passed') }}</span>
                            <span v-else-if="check.result === 'failed'" class="text-red-600"><FontAwesomeIcon icon="fal fa-times" fixed-width aria-hidden="true" /> {{ ctrans('Failed, incident opened') }}</span>
                            <span v-else class="text-gray-400">{{ ctrans('Not checked') }}</span>
                            <div v-if="check.checked_by" class="text-xs text-gray-500">{{ check.checked_by }} · {{ formatDate(check.checked_at) }}</div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-1.5 text-right">
                            <div v-if="data.can_check" class="inline-flex gap-1">
                                <Button type="tertiary" size="xs" :label="ctrans('Passed')" :loading="saving === check.key" :disabled="check.result === 'passed'" @click="record(check, 'passed')">
                                    <template #icon><FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" /></template>
                                </Button>
                                <Button type="tertiary" size="xs" :label="ctrans('Failed')" :disabled="check.result === 'failed' || saving === check.key" @click="openFail(check)">
                                    <template #icon><FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" /></template>
                                </Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </template>

        <Dialog v-model:visible="isFailDialogOpen" modal :header="ctrans('Report an incident')" :style="{ width: '28rem' }">
            <div v-if="failing" class="space-y-3 text-sm">
                <p class="text-gray-600">{{ failing.label }}<template v-if="failing.detail">: {{ failing.detail }}</template></p>
                <div class="grid grid-cols-2 gap-2">
                    <Select v-model="incident.incident_type" :options="data.incident_types" optionLabel="label" optionValue="value" :placeholder="ctrans('Issue type')" fluid />
                    <InputNumber v-model="incident.incident_quantity" :minFractionDigits="0" :maxFractionDigits="3" :min="0" :placeholder="ctrans('Quantity affected')" fluid />
                </div>
                <Textarea v-model="incident.body" rows="4" class="w-full" :placeholder="ctrans('What is wrong')" />
                <div class="flex justify-end gap-2">
                    <Button type="tertiary" :label="ctrans('Cancel')" @click="failing = null">
                        <template #icon><FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" /></template>
                    </Button>
                    <Button type="tertiary" :label="ctrans('Open incident')" :loading="saving === failing.key" :disabled="incident.body.trim().length === 0" @click="record(failing, 'failed', incident)">
                        <template #icon><FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" /></template>
                    </Button>
                </div>
            </div>
        </Dialog>
    </div>
</template>
