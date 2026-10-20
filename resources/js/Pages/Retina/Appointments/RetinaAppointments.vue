<script setup lang="ts">
import { computed, ref } from "vue"
import { Head, router } from "@inertiajs/vue3"
import Dialog from "primevue/dialog"
import Textarea from "primevue/textarea"
import PageHeading from "@/Components/Headings/PageHeadingPublic.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import AppointmentSlotPicker from "@/Components/CMS/Webpage/Appointment/AppointmentSlotPicker.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { PageHeadingTypes } from "@/types/PageHeading"
import { notify } from "@kyvg/vue3-notification"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCalendarCheck, faCalendarEdit, faCheck, faMapMarkerAlt, faTimes, faUser, faVideo } from "@fal"

library.add(faCalendarCheck, faCalendarEdit, faCheck, faMapMarkerAlt, faTimes, faUser, faVideo)

type CustomerAppointment = {
    id: number
    appointment_type_id: number
    appointment_type: string | null
    meeting_mode: "store_visit" | "video_call" | null
    media: "whatsapp" | "zoom" | null
    zoom_join_url: string | null
    location: string | null
    date_label: string
    time: string
    ends_time: string
    timezone: string
    state: "requested" | "accepted" | "declined" | "completed" | "no_show" | "cancelled"
    state_label: string
    staff_name: string | null
    can_change: boolean
}

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    upcoming: CustomerAppointment[]
    past: CustomerAppointment[]
    free_slots: Record<number, Record<string, string[]>>
}>()

const groups = computed(() => [
    { key: "upcoming", heading: ctrans("Upcoming"), rows: props.upcoming, empty: ctrans("You have no upcoming appointments.") },
    { key: "past", heading: ctrans("Past"), rows: props.past, empty: ctrans("No past appointments.") },
])

const stateClass = (state: CustomerAppointment["state"]) => ({
    requested: "bg-amber-50 text-amber-800 ring-amber-200",
    accepted: "bg-green-50 text-green-800 ring-green-200",
    completed: "bg-green-50 text-green-800 ring-green-200",
    declined: "bg-red-50 text-red-800 ring-red-200",
    cancelled: "bg-red-50 text-red-800 ring-red-200",
    no_show: "bg-gray-50 text-gray-700 ring-gray-200",
}[state])

const selected = ref<CustomerAppointment | null>(null)
const mode = ref<"" | "reschedule" | "cancel">("")
const isSending = ref(false)
const errors = ref<Record<string, string>>({})
const newDate = ref<Date | null>(null)
const newTime = ref<string | null>(null)
const cancelReason = ref("")

const isRescheduleOpen = computed({
    get: () => mode.value === "reschedule",
    set: (isOpen) => !isOpen && (mode.value = ""),
})
const isCancelOpen = computed({
    get: () => mode.value === "cancel",
    set: (isOpen) => !isOpen && (mode.value = ""),
})

const open = (appointment: CustomerAppointment, next: "reschedule" | "cancel") => {
    selected.value = appointment
    errors.value = {}
    newDate.value = null
    newTime.value = null
    cancelReason.value = ""
    mode.value = next
}

const slotsOfSelected = computed(() => (selected.value ? props.free_slots?.[selected.value.appointment_type_id] ?? {} : {}))

const toIsoDate = (date: Date): string => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`

const send = (action: "cancel" | "reschedule", payload: Record<string, unknown>) => {
    if (!selected.value) {
        return
    }
    isSending.value = true
    errors.value = {}
    router.post(route(`retina.models.appointment.${action}`, { appointment: selected.value.id }), payload, {
        preserveScroll: true,
        onSuccess: () => {
            mode.value = ""
            notify({
                title: action === "cancel" ? ctrans("Appointment cancelled") : ctrans("Appointment moved"),
                text: action === "cancel"
                    ? ctrans("We have emailed you the confirmation.")
                    : ctrans("We have emailed you the new time with a calendar invitation."),
                type: "success",
            })
        },
        onError: (serverErrors) => (errors.value = serverErrors as Record<string, string>),
        onFinish: () => (isSending.value = false),
    })
}

const reschedule = () => {
    if (!newDate.value || !newTime.value) {
        return
    }
    send("reschedule", { date: toIsoDate(newDate.value), time: newTime.value })
}

const cancel = () => send("cancel", { reason: cancelReason.value })
</script>

<template>
    <div>
        <Head :title="capitalize(title)" />
        <PageHeading :data="pageHead" />

        <div class="space-y-8 px-4 py-6">
            <section v-for="group in groups" :key="group.key">
                <h2 class="text-base font-semibold text-gray-800">{{ group.heading }}</h2>
                <p v-if="!group.rows.length" class="mt-2 text-sm text-gray-500">{{ group.empty }}</p>
                <ul v-else class="mt-3 divide-y divide-gray-100 rounded-lg border border-gray-200 bg-white">
                    <li v-for="appointment in group.rows" :key="appointment.id" class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-gray-800">{{ appointment.appointment_type }}</span>
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset" :class="stateClass(appointment.state)">{{ appointment.state_label }}</span>
                            </div>
                            <div class="mt-1 text-sm text-gray-700">
                                {{ appointment.date_label }}, {{ appointment.time }}–{{ appointment.ends_time }}
                                <span class="text-gray-400">({{ appointment.timezone }})</span>
                            </div>
                            <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                                <span v-if="appointment.meeting_mode === 'video_call'">
                                    <FontAwesomeIcon icon="fal fa-video" fixed-width aria-hidden="true" />
                                    <a v-if="appointment.zoom_join_url" :href="appointment.zoom_join_url" target="_blank" rel="noopener" class="text-[--app-accent] hover:underline">{{ ctrans("Join the Zoom meeting") }}</a>
                                    <template v-else>{{ appointment.media === "zoom" ? ctrans("Video call on Zoom") : ctrans("Video call on WhatsApp") }}</template>
                                </span>
                                <span v-if="appointment.location">
                                    <FontAwesomeIcon icon="fal fa-map-marker-alt" fixed-width aria-hidden="true" /> {{ appointment.location }}
                                </span>
                                <span v-if="appointment.staff_name">
                                    <FontAwesomeIcon icon="fal fa-user" fixed-width aria-hidden="true" /> {{ appointment.staff_name }}
                                </span>
                            </div>
                        </div>
                        <div v-if="appointment.can_change" class="flex shrink-0 flex-wrap gap-2">
                            <Button type="tertiary" size="s" :label="ctrans('Choose another time')" @click="open(appointment, 'reschedule')">
                                <template #icon>
                                    <FontAwesomeIcon icon="fal fa-calendar-edit" class="text-[var(--theme-color-0)]" fixed-width aria-hidden="true" />
                                </template>
                            </Button>
                            <Button type="tertiary" size="s" :label="ctrans('Cancel')" @click="open(appointment, 'cancel')">
                                <template #icon>
                                    <FontAwesomeIcon icon="fal fa-times" class="text-red-500" fixed-width aria-hidden="true" />
                                </template>
                            </Button>
                        </div>
                    </li>
                </ul>
            </section>
        </div>

        <Dialog v-model:visible="isRescheduleOpen" modal :header="selected?.appointment_type ?? ctrans('Choose another time')" :style="{ width: '56rem' }" :breakpoints="{ '960px': '95vw' }">
            <p v-if="selected" class="mb-4 text-sm text-gray-600">
                {{ ctrans("Now :date, :time.", { date: selected.date_label, time: selected.time }) }}
            </p>
            <AppointmentSlotPicker
                v-model:date="newDate"
                v-model:time="newTime"
                :slots="slotsOfSelected"
                :timezone="selected?.timezone"
                :confirmLabel="ctrans('Move')"
                :error="errors.time || errors.date || errors.state"
                :isLoading="isSending"
                @confirm="reschedule" />
        </Dialog>

        <Dialog v-model:visible="isCancelOpen" modal :header="ctrans('Cancel appointment')" :style="{ width: '28rem' }" :breakpoints="{ '575px': '95vw' }">
            <p v-if="selected" class="mb-3 text-sm text-gray-600">
                {{ ctrans(":type, :date at :time. The time becomes free again for other visitors.", { type: selected.appointment_type ?? "", date: selected.date_label, time: selected.time }) }}
            </p>
            <label for="retina-appointment-cancel-reason" class="mb-1 block text-sm font-medium text-gray-700">{{ ctrans("Reason") }}</label>
            <Textarea id="retina-appointment-cancel-reason" v-model="cancelReason" rows="3" autoResize maxlength="500" class="w-full" />
            <p v-if="errors.reason || errors.state" class="mt-1 text-sm text-red-600">{{ errors.reason || errors.state }}</p>
            <template #footer>
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
            </template>
        </Dialog>
    </div>
</template>
