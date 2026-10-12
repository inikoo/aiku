<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'
import { notify } from '@kyvg/vue3-notification'
import { ToggleSwitch, InputNumber } from 'primevue'
import { ctrans } from '@/Composables/useTrans'
import Button from '@/Components/Elements/Buttons/Button.vue'
import ModalConfirmation from '@/Components/Utils/ModalConfirmation.vue'
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
    stats: Record<string, number>
}

export interface AbTest {
    enabled: boolean
    is_variant: boolean
    can_edit_settings: boolean
    is_blocked_by_second_wave: boolean
    size_percentage: number
    winner_mailshot_id: number | null
    winner_sent_at: string | null
    can_send_winner: boolean
    versions: AbTestVersion[]
    update_route: routeType
    send_winner_route: routeType
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
    { key: 'open_rate', label: ctrans('Open rate'), isRate: true, isHigherBetter: true },
    { key: 'click_rate', label: ctrans('Click rate'), isRate: true, isHigherBetter: true },
    { key: 'spam_rate', label: ctrans('Spam rate'), isRate: true, isHigherBetter: false },
    { key: 'unsubscribe_rate', label: ctrans('Unsubscribe rate'), isRate: true, isHigherBetter: false },
    { key: 'bounce_rate', label: ctrans('Bounce rate'), isRate: true, isHigherBetter: false },
]

const sizePercentage = ref(props.abTest.size_percentage)
watch(() => props.abTest.size_percentage, value => sizePercentage.value = value)

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

const splitDescription = computed(() => {
    if (sizePercentage.value >= 100) {
        return ctrans('The whole list is split in two random, equal groups: half get A, half get B.')
    }

    return ctrans(':half% get A and :half% get B. You pick the winner afterwards and send it to the remaining :rest%.', {
        half: Math.round(sizePercentage.value / 2 * 10) / 10,
        rest: 100 - sizePercentage.value,
    })
})

const canEditVersionContent = (version: AbTestVersion) =>
    props.canEdit && ['in_process', 'ready'].includes(version.state)

const errorMessage = (exception: any, fallback: string): string => {
    const errors = exception?.response?.data?.errors ?? {}
    return (Object.values(errors)[0] as string[] | undefined)?.[0] ?? fallback
}

const saveSettings = async (payload: { enabled?: boolean, size_percentage?: number }) => {
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

const sendWinner = async (version: AbTestVersion) => {
    isSaving.value = true
    try {
        await axios.post(route(props.abTest.send_winner_route.name, props.abTest.send_winner_route.parameters), {
            winner_mailshot_id: version.id,
        })
        notify({
            type: 'success',
            title: ctrans('Success'),
            text: ctrans('Version :label is being sent to the rest of the list', { label: version.label }),
        })
    } catch (exception) {
        notify({
            type: 'error',
            title: ctrans('Error'),
            text: errorMessage(exception, ctrans('Failed to send the winner')),
        })
    } finally {
        isSaving.value = false
        router.reload()
    }
}

const winner = computed(() => props.abTest.versions.find(version => version.id === props.abTest.winner_mailshot_id))
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

                <template v-if="abTest.enabled">
                    <span class="h-4 w-px bg-gray-300"></span>
                    <label class="text-sm text-gray-600">{{ ctrans('Test on') }}</label>
                    <InputNumber v-model="sizePercentage" :min="10" :max="100" suffix="%" :disabled="isSaving"
                        inputClass="!h-8 !py-1 !text-sm text-center w-20" />
                    <span class="text-sm text-gray-600">{{ ctrans('of the list') }}</span>
                    <Button v-if="sizePercentage !== abTest.size_percentage" type="save" :loading="isSaving"
                        @click="saveSettings({ size_percentage: sizePercentage })" />
                </template>
            </template>

            <span v-if="abTest.is_variant" class="text-sm text-gray-600">
                {{ ctrans('This is version B. Sending is controlled from version A.') }}
            </span>
        </div>

        <p v-if="abTest.enabled && abTest.can_edit_settings" class="text-sm text-gray-500">{{ splitDescription }}</p>

        <div v-if="abTest.enabled" class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left">
                        <th class="py-2 pr-4 font-medium text-gray-500"></th>
                        <th v-for="version in abTest.versions" :key="version.id" class="py-2 pr-4 align-top font-normal">
                            <div class="flex items-center gap-2">
                                <span class="rounded bg-indigo-100 px-2 py-0.5 font-semibold text-indigo-700">
                                    {{ version.label }}
                                </span>
                                <span v-if="winner?.id === version.id"
                                    class="rounded bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">
                                    {{ ctrans('Winner') }}
                                </span>
                                <span class="text-xs text-gray-500">{{ version.state_label }}</span>
                            </div>
                            <div class="mt-1 font-medium text-gray-800">{{ version.subject }}</div>
                            <div class="text-xs text-gray-500">{{ ctrans('From') }}: {{ version.sender_name }}</div>
                            <div class="mt-1 flex gap-3 text-xs">
                                <Link :href="route(version.route.name, version.route.parameters)"
                                    class="text-indigo-600 hover:underline">
                                    {{ ctrans('View') }}
                                </Link>
                                <Link v-if="canEditVersionContent(version)"
                                    :href="route(version.workshop_route.name, version.workshop_route.parameters)"
                                    class="text-indigo-600 hover:underline">
                                    {{ ctrans('Edit content') }}
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

        <div v-if="abTest.can_send_winner" class="flex flex-wrap items-center gap-3">
            <span class="text-sm text-gray-700">
                {{ ctrans('Send the winner to the remaining :rest% of the list:', { rest: 100 - abTest.size_percentage }) }}
            </span>
            <ModalConfirmation v-for="version in abTest.versions" :key="version.id"
                :title="ctrans('Send version :label to the rest of the list?', { label: version.label })"
                :description="ctrans('Everyone on the list who did not receive the test will get version :label. This cannot be undone.', { label: version.label })">
                <template #default="{ changeModel }">
                    <Button :label="ctrans('Send :label', { label: version.label })" icon="fal fa-paper-plane"
                        type="secondary" :disabled="isSaving" @click="changeModel" />
                </template>
                <template #btn-yes="{ closeModal }">
                    <Button :label="ctrans('Yes, send :label', { label: version.label })" :loading="isSaving"
                        @click="() => { closeModal(); sendWinner(version) }" />
                </template>
            </ModalConfirmation>
        </div>
        <p v-else-if="abTest.enabled && abTest.size_percentage < 100 && !abTest.winner_sent_at" class="text-sm text-gray-500">
            {{ ctrans('Once both versions have been sent, you can send the winner to the remaining :rest% of the list.', { rest: 100 - abTest.size_percentage }) }}
        </p>
        <p v-if="winner && abTest.winner_sent_at" class="text-sm text-gray-700">
            {{ ctrans('Version :label was sent to the rest of the list on :date', { label: winner.label, date: new Date(abTest.winner_sent_at).toLocaleString() }) }}
        </p>
    </div>
</template>
