<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import { notify } from '@kyvg/vue3-notification'
import { ToggleSwitch, InputNumber, RadioButton, Select } from 'primevue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faInfoCircle, faCheck } from '@fal'
import { ctrans } from '@/Composables/useTrans'
import Button from '@/Components/Elements/Buttons/Button.vue'
import MailshotSubjectEdit from '@/Components/Workshop/Mailshot/MailshotSubjectEdit.vue'
import { routeType } from '@/types/route'

interface AbTestVersion {
    label: 'A' | 'B'
    id: number
    subject: string
    sender_name: string
    state: string
    state_label: string
    route: routeType
    workshop_route: routeType
    copy: { subject: string, name: string | null, preview_text: string | null, sender_name: string | null }
    update_route: routeType
    suggest_copy_route: routeType
    stats: Record<string, number>
}

export interface AbTest {
    enabled: boolean
    is_variant: boolean
    can_edit_settings: boolean
    estimated_recipients: number | null
    winning_metric: string
    winning_metric_options: { value: string, label: string, disabled: boolean }[]
    is_blocked_by_second_wave: boolean
    variant_delay_hours: number
    variant_sends_at: string | null
    tracking_hours: number
    tracking_ends_at: string | null
    winner_id: number | null
    versions: AbTestVersion[]
    update_route: routeType
}

const props = defineProps<{
    abTest: AbTest
    canEdit?: boolean
}>()

const metrics: { key: string, label: string, isRate: boolean, isHigherBetter: boolean }[] = [
    { key: 'sent', label: ctrans('Sent'), isRate: false, isHigherBetter: true },
    { key: 'delivered', label: ctrans('Delivered'), isRate: false, isHigherBetter: true },
    { key: 'opened', label: ctrans('Total opened'), isRate: false, isHigherBetter: true },
    { key: 'clicked', label: ctrans('Total clicked'), isRate: false, isHigherBetter: true },
    { key: 'placed_orders', label: ctrans('Placed orders'), isRate: false, isHigherBetter: true },
    { key: 'open_rate', label: ctrans('Open rate'), isRate: true, isHigherBetter: true },
    { key: 'click_rate', label: ctrans('Click rate'), isRate: true, isHigherBetter: true },
    { key: 'placed_order_rate', label: ctrans('Placed order rate'), isRate: true, isHigherBetter: true },
    { key: 'spam_rate', label: ctrans('Spam rate'), isRate: true, isHigherBetter: false },
    { key: 'unsubscribe_rate', label: ctrans('Unsubscribe rate'), isRate: true, isHigherBetter: false },
    { key: 'bounce_rate', label: ctrans('Bounce rate'), isRate: true, isHigherBetter: false },
]

const variantDelayHours = ref(props.abTest.variant_delay_hours)
const trackingDays = ref(Math.floor(props.abTest.tracking_hours / 24))
const trackingHoursOfDay = ref(props.abTest.tracking_hours % 24)
const winningMetric = ref(props.abTest.winning_metric)
watch(() => props.abTest, abTest => {
    variantDelayHours.value = abTest.variant_delay_hours
    trackingDays.value = Math.floor(abTest.tracking_hours / 24)
    trackingHoursOfDay.value = abTest.tracking_hours % 24
    winningMetric.value = abTest.winning_metric
})

const trackingHours = computed(() => trackingDays.value * 24 + trackingHoursOfDay.value)

const hourOptions = Array.from({ length: 24 }, (_, hours) => ({ value: hours, label: ctrans(':count hours', { count: hours }) }))
const dayOptions = Array.from({ length: 31 }, (_, days) => ({ value: days, label: ctrans(':count days', { count: days }) }))

const isSettingsChanged = computed(() =>
    variantDelayHours.value !== props.abTest.variant_delay_hours
    || trackingHours.value !== props.abTest.tracking_hours
    || winningMetric.value !== props.abTest.winning_metric
)

const saveAllSettings = () => saveSettings({
    variant_delay_hours: variantDelayHours.value ?? 0,
    tracking_hours: trackingHours.value,
    winning_metric: winningMetric.value,
})

const isTrackingResults = computed(() =>
    !!props.abTest.tracking_ends_at && new Date(props.abTest.tracking_ends_at) > new Date()
)

const formatDate = (date: string) => new Date(date).toLocaleString()

const isSaving = ref(false)

const hasResults = computed(() => props.abTest.versions.every(version => version.stats.delivered > 0))

const isLeading = (metric: typeof metrics[number], version: AbTestVersion): boolean => {
    if (!metric.isRate || !hasResults.value) {
        return false
    }
    const other = props.abTest.versions.find(candidate => candidate.id !== version.id)
    if (!other || other.stats[metric.key] === version.stats[metric.key]) {
        return false
    }

    return metric.isHigherBetter
        ? version.stats[metric.key] > other.stats[metric.key]
        : version.stats[metric.key] < other.stats[metric.key]
}

const canEditVersionContent = (version: AbTestVersion) =>
    props.canEdit && ['in_process', 'ready'].includes(version.state)

const errorMessage = (exception: any, fallback: string): string => {
    const errors = exception?.response?.data?.errors ?? {}
    return (Object.values(errors)[0] as string[] | undefined)?.[0] ?? fallback
}

const saveSettings = async (payload: { enabled?: boolean, variant_delay_hours?: number, tracking_hours?: number, winning_metric?: string }) => {
    isSaving.value = true
    try {
        await axios.post(route(props.abTest.update_route.name, props.abTest.update_route.parameters), payload)
    } catch (exception) {
        notify({
            type: 'error',
            title: ctrans('Error'),
            text: errorMessage(exception, ctrans('Failed to update the A/B test')),
        })
    } finally {
        isSaving.value = false
        router.reload()
    }
}

const winningMetricLabel = computed(() =>
    props.abTest.winning_metric_options.find(option => option.value === props.abTest.winning_metric)?.label ?? ''
)
</script>

<template>
    <div class="mx-4 my-4 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 space-y-3">
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-sm font-semibold text-gray-700">{{ ctrans('A/B test') }}</span>

            <template v-if="abTest.can_edit_settings">
                <ToggleSwitch :modelValue="abTest.enabled" :disabled="isSaving || abTest.is_blocked_by_second_wave"
                    @update:modelValue="(value: boolean) => saveSettings({ enabled: value })" />
                <span v-if="abTest.is_blocked_by_second_wave" class="text-sm text-gray-500">
                    {{ ctrans('Turn off the 2nd wave to run an A/B test.') }}
                </span>

            </template>

            <span v-if="abTest.is_variant" class="text-sm text-gray-600">
                {{ ctrans('This is version B. Sending is controlled from version A.') }}
            </span>
        </div>

        <div v-if="abTest.enabled && abTest.can_edit_settings" class="space-y-5 rounded-md border border-gray-200 bg-white p-4">
            <fieldset>
                <legend class="text-sm font-semibold text-gray-800">{{ ctrans('Winning metric') }}</legend>
                <p class="text-xs text-gray-500">{{ ctrans('Select the metric that will determine the test winner.') }}</p>
                <div class="mt-2 space-y-1.5">
                    <label v-for="option in abTest.winning_metric_options" :key="option.value"
                        class="flex w-fit items-center gap-2 text-sm"
                        :class="option.disabled ? 'cursor-not-allowed text-gray-400' : 'cursor-pointer text-gray-700'">
                        <RadioButton v-model="winningMetric" :value="option.value" name="winning_metric"
                            :disabled="isSaving || option.disabled" />
                        {{ option.label }}
                        <span v-if="option.disabled" class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500">
                            {{ ctrans('Coming soon') }}
                        </span>
                    </label>
                </div>
            </fieldset>

            <div class="flex flex-wrap items-center gap-3 rounded-md border border-gray-200 px-4 py-3">
                <div class="flex-1">
                    <div class="text-sm font-semibold text-gray-800">{{ ctrans('Size distribution') }}</div>
                    <p class="text-xs text-gray-500">{{ ctrans('Everyone on the list randomly gets version A or version B.') }}</p>
                </div>
                <div class="flex gap-2 text-xs">
                    <span v-for="label in ['A', 'B']" :key="label"
                        class="rounded-full bg-[--app-accent-soft] px-2 py-0.5 font-medium text-[--app-accent-strong]">
                        {{ label }} 50%
                    </span>
                </div>
                <div v-if="abTest.estimated_recipients !== null" class="text-xs text-gray-500">
                    {{ ctrans('Est. :count profiles each', { count: Math.round(abTest.estimated_recipients / 2).toLocaleString() }) }}
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <div class="text-sm font-semibold text-gray-800">{{ ctrans('Send version B') }}</div>
                    <p class="text-xs text-gray-500">{{ ctrans('Hours after version A starts sending. 0 sends both at the same time.') }}</p>
                    <InputNumber v-model="variantDelayHours" :min="0" :max="168" :suffix="` ${ctrans('hours after A')}`"
                        :disabled="isSaving" class="mt-2" inputClass="!h-8 !py-1 !text-sm w-44" />
                </div>
                <div>
                    <div class="flex items-center gap-1.5 text-sm font-semibold text-gray-800">
                        {{ ctrans('Test duration') }}
                        <FontAwesomeIcon :icon="faInfoCircle" class="text-gray-400" fixed-width
                            v-tooltip="ctrans('Opens, clicks and orders are counted from each version\'s send until this long after the last version finished sending. Then the winner is decided.')" />
                    </div>
                    <p class="text-xs text-gray-500">{{ ctrans('How long results are tracked before the winner is decided.') }}</p>
                    <div class="mt-2 flex gap-2">
                        <Select v-model="trackingHoursOfDay" :options="hourOptions" optionLabel="label" optionValue="value"
                            :disabled="isSaving" class="w-36" size="small" />
                        <Select v-model="trackingDays" :options="dayOptions" optionLabel="label" optionValue="value"
                            :disabled="isSaving" class="w-36" size="small" />
                    </div>
                    <p v-if="trackingHours < 1" class="mt-1 text-xs text-red-600">{{ ctrans('Track results for at least 1 hour.') }}</p>
                </div>
            </div>

            <div v-if="isSettingsChanged" class="flex justify-end">
                <Button type="tertiary" :label="ctrans('Save A/B test settings')" :loading="isSaving"
                    :disabled="trackingHours < 1" @click="saveAllSettings">
                    <template #icon>
                        <FontAwesomeIcon :icon="faCheck" class="text-green-600" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>
        </div>
        <p v-if="abTest.variant_sends_at" class="text-sm text-gray-700">
            {{ ctrans('Version B sends on :date', { date: formatDate(abTest.variant_sends_at) }) }}
        </p>
        <p v-if="isTrackingResults" class="text-sm text-gray-700">
            {{ ctrans('Tracking results until :date, then the winner is decided.', { date: formatDate(abTest.tracking_ends_at!) }) }}
        </p>

        <div v-if="abTest.enabled" class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left">
                        <th class="py-2 pr-4 font-medium text-gray-500"></th>
                        <th v-for="version in abTest.versions" :key="version.id" class="py-2 pr-4 align-top font-normal">
                            <div class="flex items-center gap-2">
                                <span class="rounded bg-[--app-accent-soft] px-2 py-0.5 font-semibold text-[--app-accent-strong]">
                                    {{ version.label }}
                                </span>
                                <span v-if="abTest.winner_id === version.id"
                                    v-tooltip="ctrans('Higher :metric', { metric: winningMetricLabel.toLowerCase() })"
                                    class="rounded bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">
                                    {{ ctrans('Winner') }}
                                </span>
                                <span class="text-xs text-gray-500">{{ version.state_label }}</span>
                            </div>
                            <div class="mt-1 flex items-center gap-1 font-medium text-gray-800">
                                {{ version.subject }}
                                <MailshotSubjectEdit v-if="canEditVersionContent(version)"
                                    :mailshot="version.copy" :updateMailshotRoute="version.update_route"
                                    :suggestCopyRoute="version.suggest_copy_route" @saved="router.reload()" />
                            </div>
                            <div class="text-xs text-gray-500">{{ ctrans('From') }}: {{ version.sender_name }}</div>
                            <div class="mt-1 flex gap-3 text-xs">
                                <Link :href="route(version.route.name, version.route.parameters)"
                                    class="text-[--app-accent] hover:underline">
                                    {{ ctrans('View') }}
                                </Link>
                                <Link v-if="canEditVersionContent(version)"
                                    :href="route(version.workshop_route.name, version.workshop_route.parameters)"
                                    v-tooltip="ctrans('Header and hero image, call-to-action buttons, body and offers')"
                                    class="text-[--app-accent] hover:underline">
                                    {{ ctrans('Edit email design') }}
                                </Link>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="metric in metrics" :key="metric.key" class="border-b border-gray-100">
                        <td class="py-1.5 pr-4 text-gray-500">{{ metric.label }}</td>
                        <td v-for="version in abTest.versions" :key="version.id" class="py-1.5 pr-4 tabular-nums"
                            :class="isLeading(metric, version) ? 'font-semibold text-green-700' : 'text-gray-800'">
                            {{ version.stats[metric.key] }}{{ metric.isRate ? '%' : '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
