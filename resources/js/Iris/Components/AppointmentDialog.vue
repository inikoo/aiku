<script setup lang="ts">
import { inject, ref, watch } from 'vue'
import axios from 'axios'
import Dialog from 'primevue/dialog'
import { ctrans } from '@/Composables/useTrans'
import AppointmentBooking from '@/Components/CMS/Webpage/Appointment/AppointmentBooking.vue'
import type { AppointmentBookingPayload, AppointmentTypeOption } from '@/Components/CMS/Webpage/Appointment/AppointmentBooking.vue'

const isVisible = defineModel<boolean>('visible', { required: true })

defineProps<{
    appointmentTypeId?: number | null
}>()

const layout: any = inject('layout', {})

const appointmentTypes = ref<AppointmentTypeOption[]>([])
const timezone = ref(Intl.DateTimeFormat().resolvedOptions().timeZone)
const organizerName = ref<string>(layout?.iris?.website?.name ?? '')
const defaultCountry = ref<string | null>(null)
const isLoading = ref(true)
const hasLoaded = ref(false)

const loadAppointmentTypes = async () => {
    isLoading.value = true
    try {
        const { data } = await axios.get(route('iris.json.appointments.index'))
        appointmentTypes.value = data.appointment_types
        timezone.value = data.timezone
        organizerName.value = data.shop_name
        defaultCountry.value = data.country_code
        hasLoaded.value = true
    } catch {
        appointmentTypes.value = []
    }
    isLoading.value = false
}

watch(isVisible, (visible) => {
    if (visible && !hasLoaded.value) {
        loadAppointmentTypes()
    }
})

const book = async (payload: AppointmentBookingPayload) => {
    const { data } = await axios.post(route('iris.models.appointment.store'), payload)
    return data
}
</script>

<template>
    <Dialog
        v-model:visible="isVisible"
        modal
        dismissableMask
        :draggable="false"
        :header="ctrans('Book an appointment')"
        :style="{ width: '65rem', maxWidth: '100vw' }"
        :breakpoints="{ '640px': '100vw' }"
    >
        <AppointmentBooking
            :appointmentTypes="appointmentTypes"
            :timezone="timezone"
            :defaultCountry="defaultCountry"
            :organizerName="organizerName"
            :showPickerHeading="false"
            :isLoading="isLoading"
            :showMarketingOptIn="true"
            :prefill="{ contact_name: layout?.iris_variables?.name, email: layout?.iris_variables?.email }"
            :initialTypeId="appointmentTypeId"
            :book="book"
        />
    </Dialog>
</template>
