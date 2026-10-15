<script setup lang="ts">
import { computed, ref } from "vue"
import { Head, router } from "@inertiajs/vue3"
import axios from "axios"
import Textarea from "primevue/textarea"
import Button from "@/Components/Elements/Buttons/Button.vue"
import AppointmentSlotPicker from "@/Components/CMS/Webpage/Appointment/AppointmentSlotPicker.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCalendarAlt, faCalendarEdit, faCheck, faGlobe, faMapMarkerAlt, faTimes, faUser, faVideo } from "@fal"
import { ctrans } from "@/Composables/useTrans"

library.add(faCalendarAlt, faCalendarEdit, faCheck, faGlobe, faMapMarkerAlt, faTimes, faUser, faVideo)

type VisitorAppointment = {
    id: number
    token: string
    appointment_type: string | null
    meeting_mode: "store_visit" | "video_call" | null
    meeting_mode_label: string | null
    location: string | null
    duration_minutes: number | null
    date: string
    date_label: string
    time: string
    ends_time: string
    timezone: string
    state: "requested" | "accepted" | "declined" | "completed" | "no_show" | "cancelled"
    state_label: string
    state_reason: string | null
    staff_name: string | null
    number_visitors: number
    can_change: boolean
}

const props = defineProps<{
    title: string
    shop_name: string
    appointment: VisitorAppointment
    free_slots: Record<string, string[]>
}>()

const appointment = ref<VisitorAppointment>(props.appointment)
const mode = ref<"" | "reschedule" | "cancel">("")
const isSending = ref(false)
const errors = ref<Record<string, string>>({})
const doneMessage = ref("")

const stateClass = computed(() => ({
    requested: "border-amber-200 bg-amber-50 text-amber-800",
    accepted: "border-green-200 bg-green-50 text-green-800",
    completed: "border-green-200 bg-green-50 text-green-800",
    declined: "border-red-200 bg-red-50 text-red-800",
    cancelled: "border-red-200 bg-red-50 text-red-800",
    no_show: "border-gray-200 bg-gray-50 text-gray-700",
}[appointment.value.state]))

const stateText = computed(() => ({
    requested: ctrans("We have received your request and will confirm it shortly."),
    accepted: ctrans("Your appointment is confirmed."),
    completed: ctrans("This appointment has taken place."),
    declined: ctrans("We could not see you at this time."),
    cancelled: ctrans("This appointment is cancelled."),
    no_show: ctrans("This appointment has passed."),
}[appointment.value.state]))

const toIsoDate = (date: Date): string => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`
const newDate = ref<Date | null>(null)
const newTime = ref<string | null>(null)
const cancelReason = ref("")

const open = (next: "reschedule" | "cancel") => {
    errors.value = {}
    doneMessage.value = ""
    mode.value = mode.value === next ? "" : next
}

const send = async (action: "cancel" | "reschedule", payload: Record<string, unknown>, message: string) => {
    isSending.value = true
    errors.value = {}
    try {
        const { data } = await axios.post(route(`iris.models.appointment.${action}`, { appointment: appointment.value.id, token: appointment.value.token }), payload)
        appointment.value = data
        mode.value = ""
        doneMessage.value = message
        newDate.value = null
        router.reload({ only: ["free_slots"] })
    } catch (error: any) {
        if (error?.response?.status === 422) {
            errors.value = Object.fromEntries(Object.entries(error.response.data.errors ?? {}).map(([key, value]) => [key, (value as string[])[0]]))
        } else if (error?.response?.status === 429) {
            errors.value = { general: ctrans("Too many attempts. Please wait a minute and try again.") }
        } else {
            errors.value = { general: ctrans("Something went wrong. Please try again.") }
        }
    }
    isSending.value = false
}

const reschedule = () => {
    if (!newDate.value || !newTime.value) {
        return
    }
    send("reschedule", { date: toIsoDate(newDate.value), time: newTime.value }, ctrans("Your appointment has moved. We have emailed you the new time with a calendar invitation."))
}

const cancel = () => send("cancel", { reason: cancelReason.value }, ctrans("Your appointment is cancelled. We have emailed you the confirmation."))
</script>

<template>
    <div>
        <Head :title="title" />
        <div class="w-full px-4 py-10 text-slate-700">
            <div
                class="mx-auto flex flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-[0_1px_8px_rgba(0,0,0,0.08)] transition-[max-width] duration-300 md:flex-row"
                :class="mode === 'reschedule' && newDate ? 'max-w-[1060px]' : mode ? 'max-w-[760px]' : 'max-w-[480px]'">
                <aside
                    class="px-6 py-7"
                    :class="[mode ? 'border-b border-slate-200 md:border-b-0 md:border-r' : 'w-full', mode === 'reschedule' && newDate ? 'md:w-[300px] md:shrink-0' : mode ? 'md:w-1/2' : '']">
                    <div class="text-sm font-semibold text-slate-500">{{ shop_name }}</div>
                    <h1 class="mt-1 text-[1.5rem] font-bold leading-tight text-slate-800">{{ appointment.appointment_type ?? title }}</h1>

                    <div class="mt-4 rounded-md border px-3 py-2 text-sm" :class="stateClass" role="status">
                        <span class="font-semibold">{{ appointment.state_label }}.</span> {{ doneMessage || stateText }}
                        <div v-if="appointment.state_reason && appointment.state === 'declined'" class="mt-1">{{ appointment.state_reason }}</div>
                    </div>

                    <div class="mt-6 space-y-4 text-sm font-semibold text-slate-500">
                        <div class="flex items-start gap-3">
                            <FontAwesomeIcon icon="fal fa-calendar-alt" fixed-width aria-hidden="true" class="mt-0.5 text-lg text-slate-600" />
                            <span>{{ appointment.time }}–{{ appointment.ends_time }}, {{ appointment.date_label }}</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <FontAwesomeIcon icon="fal fa-globe" fixed-width aria-hidden="true" class="mt-0.5 text-lg text-slate-600" />
                            <span>{{ appointment.timezone }}</span>
                        </div>
                        <div v-if="appointment.meeting_mode === 'video_call'" class="flex items-start gap-3">
                            <FontAwesomeIcon icon="fal fa-video" fixed-width aria-hidden="true" class="mt-0.5 text-lg text-slate-600" />
                            <span>{{ ctrans("Video call on WhatsApp") }}</span>
                        </div>
                        <div v-if="appointment.location" class="flex items-start gap-3">
                            <FontAwesomeIcon icon="fal fa-map-marker-alt" fixed-width aria-hidden="true" class="mt-0.5 text-lg text-slate-600" />
                            <span>{{ appointment.location }}</span>
                        </div>
                        <div v-if="appointment.staff_name" class="flex items-start gap-3">
                            <FontAwesomeIcon icon="fal fa-user" fixed-width aria-hidden="true" class="mt-0.5 text-lg text-slate-600" />
                            <span>{{ ctrans("With :name", { name: appointment.staff_name }) }}</span>
                        </div>
                    </div>

                    <div v-if="appointment.can_change" class="mt-8 flex flex-wrap gap-2">
                        <Button type="tertiary" :label="ctrans('Choose another time')" @click="open('reschedule')">
                            <template #icon>
                                <FontAwesomeIcon icon="fal fa-calendar-edit" class="text-[var(--theme-color-0)]" fixed-width aria-hidden="true" />
                            </template>
                        </Button>
                        <Button type="tertiary" :label="ctrans('Cancel appointment')" @click="open('cancel')">
                            <template #icon>
                                <FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
                            </template>
                        </Button>
                    </div>
                    <p v-else-if="appointment.state === 'requested' || appointment.state === 'accepted'" class="mt-8 text-sm text-slate-500">
                        {{ ctrans("This appointment can no longer be changed online. Please contact us.") }}
                    </p>

                    <p v-if="errors.general" class="mt-4 text-sm text-red-600">{{ errors.general }}</p>
                </aside>

                <AppointmentSlotPicker
                    v-if="mode === 'reschedule'"
                    v-model:date="newDate"
                    v-model:time="newTime"
                    class="px-6 py-7"
                    :slots="free_slots"
                    :timezone="appointment.timezone"
                    :confirmLabel="ctrans('Move')"
                    :error="errors.time || errors.date || errors.state"
                    :isLoading="isSending"
                    @confirm="reschedule" />

                <section v-if="mode === 'cancel'" class="flex-1 px-6 py-7">
                    <h3 class="mb-2 text-xl font-bold text-slate-800">{{ ctrans("Cancel appointment") }}</h3>
                    <p class="text-sm text-slate-600">{{ ctrans("The time becomes free again for other visitors.") }}</p>
                    <label for="appointment-cancel-reason" class="mb-1.5 mt-4 block text-sm font-bold text-slate-800">{{ ctrans("Reason") }}</label>
                    <Textarea id="appointment-cancel-reason" v-model="cancelReason" rows="3" autoResize maxlength="500" class="w-full" />
                    <p v-if="errors.reason || errors.state" class="mt-2 text-sm text-red-600">{{ errors.reason || errors.state }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <Button type="tertiary" :label="ctrans('Keep appointment')" @click="mode = ''">
                            <template #icon>
                                <FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
                            </template>
                        </Button>
                        <Button type="tertiary" :label="ctrans('Cancel appointment')" :loading="isSending" @click="cancel">
                            <template #icon>
                                <FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
                            </template>
                        </Button>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
