<script setup lang='ts'>
import { ref, onMounted, Ref, watch, reactive } from 'vue'
import 'v-calendar/style.css'
import axios from 'axios'
import Button from '@/Components/Elements/Buttons/Button.vue'
import LoginSmall from '@/Components/Iris/LoginSmall.vue'
import Modal from '@/Components/Utils/Modal.vue'
import Steps from '@/Components/Utils/Steps.vue'
import AppointmentSummary from '@/Components/Iris/Steps/AppointmentSummary.vue'
import SelectAppointmentDate from '@/Components/Iris/Steps/SelectAppointmentDate.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faArrowAltRight } from '@fas'
import { faClock } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faArrowAltRight, faClock)

const availableSchedulesOnMonth = ref<{ [key: string]: { [key: string]: string[] }[] }>({})  // {2023-6: {2023-11-25 ['09:00, '10:00', ...]} }
const selectedDate: any = ref(new Date())  // on select date in DatePicker
const isLoading = ref(false)  // Loading on fetch
const isModalSteps = ref(false)
const currentStep = ref(1)

const emailField: any = reactive({
    value: '',
    status: false,
    description: ''
})

const passwordField = reactive({
    value: '',
    valueRepeat: '',
    description: ''
})

const meetEvent = reactive({
    value: null,
    options: [
        {
            name: 'callback',
            label: 'Callback'
        },
        {
            name: 'in-person',
            label: 'In person'
        }
    ]
})

// On click button available hour
const onSelectHour = (time: string) => {
    const timeSplit = time.split(':')
    selectedDate.value = new Date(selectedDate.value)
    selectedDate.value.setHours(timeSplit[0])
    selectedDate.value.setMinutes(timeSplit[1])
}


// Fetch available schedule for whole month
const fetchAvailableOnMonth = async (year: number, month: number) => {
    if (!year || !month) return
    isLoading.value = true
    // try {
    //     const response = await axios.get(
    //         route('public.appointment.schedule'),
    //         {
    //             params: {
    //                 year: year,
    //                 month: month
    //             }
    //         }
    //     )
    //     console.log(response.data)
    //     availableSchedulesOnMonth.value = {
    //         [`${year}-${month}`]: response.data.availableSchedules,
    //         ...availableSchedulesOnMonth.value
    //     }
    // }
    // catch (error: any) {
    //     console.log('error', error)
    // }
    isLoading.value = false
}


onMounted(() => {
    const today = new Date()
    fetchAvailableOnMonth(today.getFullYear(), today.getMonth() + 1)
})

watch(selectedDate, () => {
    if (!availableSchedulesOnMonth.value[`${selectedDate.value?.getFullYear()}-${selectedDate.value?.getMonth() + 1}`]) {
        fetchAvailableOnMonth(selectedDate.value?.getFullYear(), selectedDate.value?.getMonth() + 1)
    }
})

const stepsOptions = [
    {
        label: 'Step 1: Login',
        // icon: 'fal fa-check'
    },
    {
        label: 'Step 2: Select date',
        // icon: 'fal fa-check'
    },
    {
        label: 'Final: Summary',
        // icon: 'fal fa-check'
    },
]
</script>

<template>
    <div class="py-8 px-6">
        <div class="mx-auto justify-center grid grid-cols-2">
            <!-- Section: Content -->
            <img src="https://kelas-work.s3.ap-southeast-1.amazonaws.com/bucket-prod-98123hsandknaknd1u3/upload/files/img/blog_cover/blogcover-zph64e5c4c8538dc.webp" alt="" class="mx-auto mb-3 shadow-sm">
            <section class="">
                <h5 class="text-2xl font-bold ">Request an appointment with us</h5>
                <p class="text-xs mt-2 mb-8 text-justify max-w-lg">
                    Ready to take the next step? Schedule an appointment with one of our experienced professionals
                    today! We offer personalized consultations to address your specific needs and answer any questions
                    you may have. <br><br>
                    Our convenient online booking system makes scheduling a breeze. Simply choose a time that works best
                    for you and confirm your details. We look forward to connecting with you soon!
                </p>
            
                <Button @click="isModalSteps = true" :style="'rainbow'" label="Click here" />
            </section>

        </div>
    </div>

    <Modal :isOpen="isModalSteps" @onClose="isModalSteps = false">
        <div class="h-96 overflow-y-auto">

            <Steps :options="stepsOptions" :currentStep="currentStep" @previousStep="currentStep--"
                @nextStep="currentStep++" />

            <transition name="slide-to-left" mode="out-in">
                <!-- First Step: Login -->
                <div v-if="currentStep === 0" class="flex gap-x-2 pb-2 justify-center">
                    <LoginSmall v-model:email="emailField.value" v-model:password="passwordField.value"
                        v-model:passwordRepeat="passwordField.valueRepeat" :emailField="emailField"
                        :passwordField="passwordField" @loginSuccess="currentStep++"
                        loginRoute="public.appointment.login" checkEmailRoute="public.appointment.check.email"
                        registerRoute="public.appointment.register" />
                </div>

                <!-- Second Step: Select date -->
                <div v-else-if="currentStep === 1" class="">
                    <SelectAppointmentDate v-model="selectedDate" title="hehehehe" :isLoading="isLoading"
                        :availableSchedulesOnMonth="availableSchedulesOnMonth" :meetEvent="meetEvent"
                        @onFinish="currentStep++" @onSelectHour="(newValue) => onSelectHour(newValue)" />
                </div>

                <!-- Third Step: Summary review -->
                <div v-else class="max-w-2xl mx-auto py-4">
                    <AppointmentSummary :selectedDate="selectedDate" @onFinish="isModalSteps = false"
                        :meetEvent="meetEvent.value" />
                </div>
            </transition>
        </div>
    </Modal>
</template>

<style>
.title-text {
    padding: 10px 0px;

}

.description-text {
    font-size: 12px;
}

.image-header {
    display: flex;
    justify-content: center;
}
</style>