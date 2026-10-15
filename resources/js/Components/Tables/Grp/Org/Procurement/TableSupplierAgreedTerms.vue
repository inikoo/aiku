<!--
  - Copyright (c) 2026, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import Dialog from 'primevue/dialog'
import Select from 'primevue/select'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import DatePicker from 'primevue/datepicker'
import ToggleSwitch from 'primevue/toggleswitch'
import FileUpload from 'primevue/fileupload'
import { notify } from '@kyvg/vue3-notification'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faHandshake, faPen, faBan, faCheck, faTimes, faPaperclip, faFileAlt } from '@fal'
import Table from '@/Components/Table/Table.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { ctrans } from '@/Composables/useTrans'
import { routeType } from '@/types/route'

library.add(faHandshake, faPen, faBan, faCheck, faTimes, faPaperclip, faFileAlt)

type Option = { value: string | number, label: string, has_amount?: boolean }

type Term = {
    id: number
    term_type: string
    term: string
    value_type: string
    amount: string | null
    currency_id: number | null
    value_text: string | null
    tolerance: string | null
    value: string
    level: string
    level_label: string
    product_code: string | null
    organisation_id: number | null
    organisation_code: string | null
    agreed_on: string
    agreed_by: string | null
    agreed_by_user_id: number | null
    agreed_with: string | null
    has_evidence: boolean
    evidence: { name: string, route: routeType }[]
    valid_to: string | null
    state: string
    state_label: string
    in_force: boolean
    visibility: string
    visibility_label: string
    show_on_po: boolean
}

const props = defineProps<{
    data: object
    tab?: string
    agreement?: {
        can_write: boolean
        is_supervisor: boolean
        user_id: number
        routes: { store_term: routeType } | null
        options: {
            term_types: Option[]
            value_types: Option[]
            visibilities: Option[]
            currencies: Option[]
            organisations: Option[]
            organisation_id: number | null
        } | null
    }
}>()

const isOpen = ref(false)
const replaced = ref<Term | null>(null)
const onlyThisOrganisation = ref(false)
const agreedOn = ref<Date | null>(new Date())

const blankTerm = () => ({
    term_type: null,
    value_type: null,
    amount: null,
    currency_id: null,
    value_text: '',
    tolerance: null,
    agreed_on: null,
    agreed_with: '',
    visibility: 'internal',
    show_on_po: false,
    organisation_id: null,
    supersedes_id: null,
    attachments: [],
})

const form = useForm<Record<string, any>>(blankTerm())
const formKey = ref(0)

const hasAmount = computed(() => props.agreement?.options?.value_types.find((option) => option.value === form.value_type)?.has_amount ?? false)
const textLabel = computed(() => {
    if (form.term_type === 'incoterm') {
        return ctrans('Incoterm and place, e.g. FOB Ningbo')
    }
    if (form.term_type === 'other_charge') {
        return ctrans('What the charge is for')
    }

    return ctrans('Details')
})

function canChange(term: Term) {
    return !!props.agreement?.can_write && term.state === 'active'
        && (props.agreement.is_supervisor || term.agreed_by_user_id === props.agreement.user_id)
}

function open(term: Term | null = null) {
    form.defaults(blankTerm())
    form.reset()
    form.clearErrors()
    formKey.value++
    replaced.value = term
    agreedOn.value = new Date()
    onlyThisOrganisation.value = !!term?.organisation_id
    if (term) {
        form.term_type = term.term_type
        form.value_type = term.value_type
        form.amount = term.amount === null ? null : Number(term.amount)
        form.currency_id = term.currency_id
        form.value_text = term.value_text ?? ''
        form.tolerance = term.tolerance === null ? null : Number(term.tolerance)
        form.visibility = term.visibility
        form.show_on_po = term.show_on_po
        form.supersedes_id = term.id
    }
    isOpen.value = true
}

function isoDate(date: Date | null) {
    if (!date) {
        return null
    }

    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

function save() {
    const storeRoute = props.agreement?.routes?.store_term
    if (!storeRoute) {
        return
    }

    form
        .transform((data) => ({
            ...data,
            amount: hasAmount.value ? data.amount : null,
            currency_id: hasAmount.value && data.value_type !== 'percentage' ? data.currency_id : null,
            agreed_on: isoDate(agreedOn.value),
            organisation_id: onlyThisOrganisation.value ? (replaced.value?.organisation_id ?? props.agreement?.options?.organisation_id ?? data.organisation_id) : null,
        }))
        .post(route(storeRoute.name, storeRoute.parameters), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                isOpen.value = false
                router.reload({ only: [props.tab ?? 'agreed_terms', 'agreement'] })
            },
        })
}

function withdraw(term: Term) {
    router.patch(route('grp.models.supplier_agreed_term.withdraw', { supplierAgreedTerm: term.id }), {}, {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: [props.tab ?? 'agreed_terms', 'agreement'] }),
        onError: (errors) => notify({ title: ctrans('Something went wrong'), text: Object.values(errors)[0] ?? '', type: 'error' }),
    })
}
</script>

<template>
    <div>
        <Table :resource="data" :name="tab">
            <template v-if="agreement?.can_write" #add-on-button>
                <Button :label="ctrans('Add agreed term')" type="tertiary" size="xs" @click="open()">
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-handshake" class="text-green-600" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </template>

            <template #cell(term)="{ item }">
                <span class="font-medium" :class="item.in_force ? 'text-gray-900' : 'text-gray-400 line-through'">{{ item.term }}</span>
            </template>

            <template #cell(value)="{ item }">
                <span :class="item.in_force ? '' : 'text-gray-400 line-through'">{{ item.value }}</span>
            </template>

            <template #cell(applies_to)="{ item }">
                <span>{{ item.product_code ?? item.level_label }}</span>
                <span v-if="item.organisation_code" class="ml-1 text-xs text-gray-500">{{ ctrans('Only :organisation', { organisation: item.organisation_code }) }}</span>
            </template>

            <template #cell(agreed_on)="{ item }">
                <div class="whitespace-nowrap tabular-nums">{{ item.agreed_on }}</div>
                <div class="text-xs text-gray-500">
                    {{ item.agreed_by ?? '-' }}<template v-if="item.agreed_with"> · {{ ctrans('with :name', { name: item.agreed_with }) }}</template>
                </div>
            </template>

            <template #cell(evidence)="{ item }">
                <a v-for="file in item.evidence" :key="file.name" :href="route(file.route.name, file.route.parameters)" target="_blank" class="flex items-center gap-1 text-gray-700 underline decoration-gray-300 hover:decoration-gray-500">
                    <FontAwesomeIcon icon="fal fa-paperclip" class="text-gray-400" fixed-width aria-hidden="true" />
                    {{ file.name }}
                </a>
                <span v-if="!item.has_evidence" class="text-gray-400">-</span>
                <span v-else-if="!item.evidence.length" class="text-gray-500">{{ ctrans('Linked message') }}</span>
            </template>

            <template #cell(shown)="{ item }">
                <div>{{ item.visibility_label }}</div>
                <div v-if="item.show_on_po" class="flex items-center gap-1 text-xs text-gray-500">
                    <FontAwesomeIcon icon="fal fa-file-alt" fixed-width aria-hidden="true" />
                    {{ ctrans('Printed on the purchase order') }}
                </div>
            </template>

            <template #cell(state)="{ item }">
                <span v-if="item.state === 'active' && !item.in_force" class="text-gray-500">{{ ctrans('Overridden') }}</span>
                <span v-else :class="item.state === 'active' ? 'text-green-700' : 'text-gray-500'">{{ item.state_label }}</span>
                <div v-if="item.valid_to" class="text-xs text-gray-500 tabular-nums">{{ ctrans('until :date', { date: item.valid_to }) }}</div>
            </template>

            <template #cell(actions)="{ item }">
                <div v-if="canChange(item)" class="flex justify-end gap-1">
                    <Button :label="ctrans('Change')" type="tertiary" size="xs" @click="open(item)">
                        <template #icon>
                            <FontAwesomeIcon icon="fal fa-pen" class="text-gray-500" fixed-width aria-hidden="true" />
                        </template>
                    </Button>
                    <Button :label="ctrans('Withdraw')" type="tertiary" size="xs" @click="withdraw(item)">
                        <template #icon>
                            <FontAwesomeIcon icon="fal fa-ban" class="text-red-500" fixed-width aria-hidden="true" />
                        </template>
                    </Button>
                </div>
            </template>
        </Table>

        <Dialog
            v-model:visible="isOpen"
            modal
            :draggable="false"
            :header="replaced ? ctrans('Change agreed term') : ctrans('Add agreed term')"
            :style="{ width: '36rem', maxWidth: 'calc(100vw - 2rem)' }"
        >
            <div v-if="agreement?.options" class="flex flex-col gap-3 text-sm">
                <p v-if="replaced" class="rounded border border-gray-200 bg-gray-50 px-3 py-2 text-gray-600">
                    {{ ctrans('The current term (:value) is kept in the history as superseded.', { value: replaced.value }) }}
                </p>

                <div class="grid grid-cols-2 gap-3">
                    <label class="flex flex-col gap-1">
                        <span class="font-medium text-gray-700">{{ ctrans('Term') }}</span>
                        <Select v-model="form.term_type" :options="agreement.options.term_types" optionLabel="label" optionValue="value" :disabled="!!replaced" :invalid="!!form.errors.term_type" />
                        <span v-if="form.errors.term_type" class="text-xs text-red-600">{{ form.errors.term_type }}</span>
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="font-medium text-gray-700">{{ ctrans('What was agreed') }}</span>
                        <Select v-model="form.value_type" :options="agreement.options.value_types" optionLabel="label" optionValue="value" :invalid="!!form.errors.value_type" />
                        <span v-if="form.errors.value_type" class="text-xs text-red-600">{{ form.errors.value_type }}</span>
                    </label>
                </div>

                <div v-if="hasAmount" class="grid grid-cols-3 gap-3">
                    <label class="flex flex-col gap-1">
                        <span class="font-medium text-gray-700">{{ form.value_type === 'percentage' ? ctrans('Percentage') : ctrans('Amount') }}</span>
                        <InputNumber v-model="form.amount" :minFractionDigits="0" :maxFractionDigits="4" :min="0" :invalid="!!form.errors.amount" fluid />
                        <span v-if="form.errors.amount" class="text-xs text-red-600">{{ form.errors.amount }}</span>
                    </label>
                    <label v-if="form.value_type !== 'percentage'" class="flex flex-col gap-1">
                        <span class="font-medium text-gray-700">{{ ctrans('Currency') }}</span>
                        <Select v-model="form.currency_id" :options="agreement.options.currencies" optionLabel="label" optionValue="value" filter :invalid="!!form.errors.currency_id" />
                        <span v-if="form.errors.currency_id" class="text-xs text-red-600">{{ form.errors.currency_id }}</span>
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="font-medium text-gray-700">{{ ctrans('Tolerance') }}</span>
                        <InputNumber v-model="form.tolerance" :minFractionDigits="0" :maxFractionDigits="4" :min="0" fluid />
                    </label>
                </div>

                <label class="flex flex-col gap-1">
                    <span class="font-medium text-gray-700">{{ textLabel }}</span>
                    <InputText v-model="form.value_text" :invalid="!!form.errors.value_text" />
                    <span v-if="form.errors.value_text" class="text-xs text-red-600">{{ form.errors.value_text }}</span>
                </label>

                <div class="grid grid-cols-2 gap-3">
                    <label class="flex flex-col gap-1">
                        <span class="font-medium text-gray-700">{{ ctrans('Agreed on') }}</span>
                        <DatePicker v-model="agreedOn" dateFormat="dd/mm/yy" :maxDate="new Date()" showIcon :invalid="!!form.errors.agreed_on" />
                        <span v-if="form.errors.agreed_on" class="text-xs text-red-600">{{ form.errors.agreed_on }}</span>
                    </label>
                    <label class="flex flex-col gap-1">
                        <span class="font-medium text-gray-700">{{ ctrans('Agreed with') }}</span>
                        <InputText v-model="form.agreed_with" :placeholder="ctrans('Contact at the supplier or agent')" />
                    </label>
                </div>

                <div class="flex flex-col gap-1">
                    <span class="font-medium text-gray-700">{{ ctrans('Evidence') }}</span>
                    <FileUpload :key="formKey" mode="basic" :auto="false" customUpload multiple :chooseLabel="ctrans('Attach the email, chat or document')" @select="(event) => form.attachments = event.files" />
                    <span v-if="form.errors.evidence_message_id" class="text-xs text-red-600">{{ form.errors.evidence_message_id }}</span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <label class="flex flex-col gap-1">
                        <span class="font-medium text-gray-700">{{ ctrans('Who may see it') }}</span>
                        <Select v-model="form.visibility" :options="agreement.options.visibilities" optionLabel="label" optionValue="value" />
                    </label>
                    <div class="flex flex-col justify-end gap-2">
                        <label class="flex items-center gap-2">
                            <ToggleSwitch v-model="form.show_on_po" />
                            <span>{{ ctrans('Print on the purchase order') }}</span>
                        </label>
                        <label v-if="agreement.options.organisation_id && !replaced" class="flex items-center gap-2">
                            <ToggleSwitch v-model="onlyThisOrganisation" />
                            <span>{{ ctrans('Only for this organisation') }}</span>
                        </label>
                    </div>
                </div>
                <p v-if="form.show_on_po && form.visibility === 'internal'" class="text-xs text-amber-700">
                    {{ ctrans('An internal term is never printed: choose agent or supplier for it to appear on the purchase order.') }}
                </p>
                <label v-if="!agreement.options.organisation_id && !replaced" class="flex flex-col gap-1">
                    <span class="font-medium text-gray-700">{{ ctrans('Applies to') }}</span>
                    <Select v-model="form.organisation_id" :options="agreement.options.organisations" optionLabel="label" optionValue="value" showClear :placeholder="ctrans('Every organisation')" @update:modelValue="(value) => onlyThisOrganisation = !!value" />
                </label>
            </div>

            <template #footer>
                <Button :label="ctrans('Cancel')" type="tertiary" @click="isOpen = false">
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
                    </template>
                </Button>
                <Button :label="ctrans('Save')" type="tertiary" :loading="form.processing" :disabled="form.processing || !form.term_type || !form.value_type" @click="save">
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </template>
        </Dialog>
    </div>
</template>
