<script setup lang='ts'>
import { useFormatTime } from '@/Composables/useFormatTime'
import { usePage } from '@inertiajs/vue3'
import { notify } from '@kyvg/vue3-notification'
import Button from '@/Components/Elements/Buttons/Button.vue'
import CopyButton from '@/Components/Utils/CopyButton.vue'
import axios from 'axios'
import { DataToSubmit } from '@/types/Iris/Appointment'


import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faClock, faCalendarAlt, faMapMarkerAlt, faLink, faUser, faEnvelope, faStoreAlt } from '@fal'
import { faArrowAltRight, faPaperPlane } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import { onMounted, ref } from 'vue'
library.add(faClock, faCalendarAlt, faMapMarkerAlt, faLink, faUser, faEnvelope, faStoreAlt, faArrowAltRight, faPaperPlane)

const props = defineProps<{
    dataAppointmentToSubmit: DataToSubmit
}>()

const emits = defineEmits<{
    (e: 'onFinish'): void
    (e: 'decreaseStep'): void
}>()

const isLoading = ref(false)

// When submit Appointment
const onClickMakeAppointment = async () => {
    isLoading.value = true
    try {
        const response = await axios.post(route('iris.crm.appointment.store'), {
            schedule_at: props.dataAppointmentToSubmit.selectedDateHour,
            event: props.dataAppointmentToSubmit.meetType,
            company_name: props.dataAppointmentToSubmit.company_name || undefined,
            contact_name: props.dataAppointmentToSubmit.contact_name,
            email: props.dataAppointmentToSubmit.email,
            event_address: 'zoom',
            type: 'lead'
        })
        // console.log('response', response)

        notify({
            title: "Appointment successfuly created.",
            // text: error,
            type: "success"
        })
        emits('onFinish')
    } catch (error) {
        notify({
            title: "Error while ",
            // text: error,
            type: "error"
        })
        console.error(error)
    }

    isLoading.value = false
}

onMounted(() => {
    if(!props.dataAppointmentToSubmit.email || !props.dataAppointmentToSubmit.contact_name) {
        emits('decreaseStep')
    }
})

</script>

<template>
    <div class="font-medium text-gray-600 text-sm md:text-lg leading-none mb-2 md:mb-3">Summary of your appointment</div>

    <!-- Section: Appointment Card -->
    <div class="bg-white border border-gray-300 rounded-lg overflow-hidden p-4 grid md:grid-cols-2 mb-4 gap-y-4 md:gap-y-0">

        <div class="col-span-2 grid grid-cols-2">
            <div class="text-4xl font-semibold">
                {{ dataAppointmentToSubmit.meetType }}
            </div>
            <div class="place-self-end">
                <img src="https://seeklogo.com/images/Z/zoom-fondo-blanco-vertical-logo-F819E1C283-seeklogo.com.png"
                    class="h-10" />
            </div>
        </div>

        <div class="md:mt-4 flex flex-col gap-y-2 text-gray-500 order-3 col-span-2">
            <!-- Section: Link -->
            <div class="inline-flex items-center gap-x-1">
                <FontAwesomeIcon fixed-width icon='fal fa-link' class='h-4 aspect-square text-gray-400 '
                    aria-hidden='true' />
                <div class="text-sm md:text-base leading-none italic">{{ dataAppointmentToSubmit.url }}</div>
                <CopyButton :text="dataAppointmentToSubmit.url" />
            </div>
            <!-- <div class="inline-flex items-center gap-x-1">
                <FontAwesomeIcon fixed-width icon='fal fa-map-marker-alt' class='h-4 md:h-5 aspect-square text-gray-400 '
                    aria-hidden='true' />
                <div class="text-sm md:text-base leading-none">Zoom</div>
            </div> -->

            <!-- Section: Name -->
            <div class="inline-flex items-center gap-x-1">
                <FontAwesomeIcon fixed-width icon='fal fa-user' class='h-4 md:h-5 aspect-square text-gray-400 '
                    aria-hidden='true' />
                <div class="text-sm md:text-base leading-none">{{ dataAppointmentToSubmit.contact_name || '-' }}</div>
            </div>

            <!-- Section: Email -->
            <div v-if="dataAppointmentToSubmit.email" class="inline-flex items-center gap-x-1">
                <FontAwesomeIcon fixed-width icon='fal fa-envelope' class='h-4 md:h-5 aspect-square text-gray-400 '
                    aria-hidden='true' />
                <div class="text-sm md:text-base leading-none">{{ dataAppointmentToSubmit.email }}</div>
            </div>

            <!-- Section: Company name -->
            <div class="inline-flex items-center gap-x-1">
                <FontAwesomeIcon fixed-width icon='fal fa-store-alt' class='h-4 md:h-5 aspect-square text-gray-400 '
                    aria-hidden='true' />
                <div class="text-sm md:text-base leading-none">{{ dataAppointmentToSubmit.company_name || '-' }}</div>
            </div>

            <!-- Section: Calendar -->
            <div class="inline-flex items-center gap-x-1">
                <FontAwesomeIcon fixed-width icon='fal fa-calendar-alt' class='h-4 md:h-5 aspect-square text-gray-400 '
                    aria-hidden='true' />
                <div class="text-sm md:text-base leading-none">{{ useFormatTime(dataAppointmentToSubmit.selectedDateHour) }}</div>
            </div>

            <!-- Section: Hours -->
            <div class="inline-flex items-center gap-x-1">
                <FontAwesomeIcon fixed-width icon='fal fa-clock' class='h-4 md:h-5 aspect-square text-gray-400 '
                    aria-hidden='true' />
                <div class="flex items-center space-x-1">
                    <span class="tabular-nums text-sm md:text-base leading-none">
                        {{ (dataAppointmentToSubmit.selectedDateHour.getHours()).toString().padStart(2, '0') + ':' +
                            (dataAppointmentToSubmit.selectedDateHour.getMinutes()).toString().padStart(2, '0') }}
                    </span>
                    <span class="text-xs md:text-sm text-gray-500 ">
                        {{ dataAppointmentToSubmit.selectedDateHour.getHours() > 11 ? 'PM' : 'AM' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <Button @click="onClickMakeAppointment()" label="Make appointment" iconRight="fas fa-paper-plane" full :loading="isLoading" :disabled="isLoading" />
</template>
