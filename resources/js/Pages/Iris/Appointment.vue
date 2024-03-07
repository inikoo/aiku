<script setup lang='ts'>
import { ref, onMounted, Ref, watch, reactive } from 'vue'
import 'v-calendar/style.css'
import axios from 'axios'
import Button from '@/Components/Elements/Buttons/Button.vue'
import Modal from '@/Components/Utils/Modal.vue'
import Steps from '@/Components/Utils/Steps.vue'
import AppointmentSummary from '@/Components/Iris/Steps/AppointmentSummary.vue'
import SelectAppointmentDate from '@/Components/Iris/Steps/SelectAppointmentDate.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faArrowAltRight } from '@fas'
import { faClock } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Head } from '@inertiajs/vue3'
import PureInput from '@/Components/Pure/PureInput.vue'
library.add(faArrowAltRight, faClock)

const availableSchedulesOnMonth = ref<{ [key: string]: { [key: string]: string[] }[] }>({})  // {2023-6: {2023-11-25 ['09:00, '10:00', ...]} }
const selectedDate: any = ref(new Date())  // on select date in DatePicker
const isLoading = ref(false)  // Loading on fetch
const isModalSteps = ref(false)
const currentStep = ref(0)

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

const dataInputDetail = reactive({
    name: '',
    business: '',
    email: ''
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
    try {
        const response = await axios.get(
            route('iris.crm.appointment.schedule'),
            {
                params: {
                    year: year,
                    month: month
                }
            }
        )
        console.log(response.data)
        availableSchedulesOnMonth.value = {
            [`${year}-${month}`]: response.data.availableSchedules,
            ...availableSchedulesOnMonth.value
        }
    }
    catch (error: any) {
        console.error('Error while fetch year:', error.message)
    }
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
        label: 'Select date',
        // icon: 'fal fa-check'
    },
    {
        label: 'Input detail',
        // icon: 'fal fa-check'
    },
    {
        label: 'Confirmation',
        // icon: 'fal fa-check'
    },
]
</script>

<template>
    <Head title="Appointment" />
    <div class="py-8 px-6 mx-auto justify-center grid grid-cols-2">
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

    <!-- Modal: Select calendar -->
    <Modal :isOpen="isModalSteps" @onClose="isModalSteps = false">
        <div class="h-[450px] overflow-y-auto">

            <Steps :options="stepsOptions" :currentStep="currentStep" @previousStep="currentStep--"
                @nextStep="currentStep++" />

            <transition name="slide-to-left" mode="out-in">
                <!-- Second Step: Select date -->
                <div v-if="currentStep === 0" class="">
                    <SelectAppointmentDate v-model="selectedDate" :isLoading="isLoading"
                        :availableSchedulesOnMonth="availableSchedulesOnMonth" :meetEvent="meetEvent"
                        @onFinish="currentStep++" @onSelectHour="(newValue) => onSelectHour(newValue)" />
                </div>
                
                <!-- Second Step: Select date -->
                <div v-else-if="currentStep === 1" class="space-y-4">
                    <div class="text-center text-xl font-semibold text-gray-700">How we can contact you?</div>
                    <div class="flex flex-col max-w-lg mx-auto gap-x-2 gap-y-4 pl-0.5 text-gray-600">
                        <div>
                            <label for="fieldName">Name:</label>
                            <PureInput v-model="dataInputDetail.name" inputName="fieldName" placeholder="John Doe" />
                        </div>
                        <div>
                            <label for="fieldBusiness">Business name:</label>
                            <PureInput v-model="dataInputDetail.business" inputName="fieldBusiness" placeholder="Flower's Shop" />
                        </div>
                        <div>
                            <label for="fieldEmail"><span class="text-red-500">*</span>Email:</label>
                            <PureInput v-model="dataInputDetail.email" inputName="fieldEmail" placeholder="johndoe@email.com" type="email" />
                        </div>

                        <div class="mx-auto mt-4">
                            <Button label="Confirm" size="xl" />
                        </div>
                        
                        
                    </div>
                </div>

                <!-- Third Step: Summary review -->
                <div v-else class="max-w-2xl mx-auto py-4">
                    <AppointmentSummary :selectedDate="selectedDate" @onFinish="isModalSteps = false"
                        :meetEvent="meetEvent.value" />
                </div>
            </transition>

            <Button @click="currentStep++" />
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