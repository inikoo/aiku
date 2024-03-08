<script setup lang='ts'>
import { ref, onMounted, Ref, watch, reactive } from 'vue'
import 'v-calendar/style.css'
import axios from 'axios'
import Button from '@/Components/Elements/Buttons/Button.vue'
import Modal from '@/Components/Utils/Modal.vue'
import Steps from '@/Components/Utils/Steps.vue'
import AppointmentSummary from '@/Components/Iris/Steps/AppointmentSummary.vue'
import SelectAppointmentDate from '@/Components/Iris/Steps/SelectAppointmentDate.vue'
import { DataToSubmit } from '@/types/Iris/Appointment'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faArrowAltRight } from '@fas'
import { faClock } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { Head } from '@inertiajs/vue3'
import PureInput from '@/Components/Pure/PureInput.vue'
library.add(faArrowAltRight, faClock)

const availableSchedulesOnMonth = ref<{ [key: string]: { [key: string]: string[] }[] }>({})  // {2023-6: {2023-11-25 ['09:00, '10:00', ...]} }
// const selectedDate: any = ref(new Date())  // on select date in DatePicker
const isLoading = ref(false)  // Loading on fetch
const isModalSteps = ref(false)
const currentStep = ref(0)

const defaultData = {
    selectedDateHour: new Date(),
    meetType: '',
    contact_name: '',
    company_name: '',
    email: '',
    url: 'http://fulfilment.test'
}

// Data from all steps
const dataAppointmentToSubmit = reactive<DataToSubmit>({...defaultData})

// On click button available hour
const onSelectHour = (time: string) => {
    const timeSplit = time.split(':')
    dataAppointmentToSubmit.selectedDateHour = new Date(dataAppointmentToSubmit.selectedDateHour)
    dataAppointmentToSubmit.selectedDateHour.setHours(parseInt(timeSplit[0]))
    dataAppointmentToSubmit.selectedDateHour.setMinutes(parseInt(timeSplit[1]))
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
        // console.log(response.data)
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
    // Fetch available hour for today
    const today = new Date()
    fetchAvailableOnMonth(today.getFullYear(), today.getMonth() + 1)
})

watch(() => dataAppointmentToSubmit.selectedDateHour, () => {
    if (!availableSchedulesOnMonth.value[`${dataAppointmentToSubmit.selectedDateHour?.getFullYear()}-${dataAppointmentToSubmit.selectedDateHour?.getMonth() + 1}`]) {
        fetchAvailableOnMonth(dataAppointmentToSubmit.selectedDateHour?.getFullYear(), dataAppointmentToSubmit.selectedDateHour?.getMonth() + 1)
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

const onSubmitAppointment = () => {
    isModalSteps.value = false
    setTimeout(() => {
        currentStep.value = 0
        dataAppointmentToSubmit.selectedDateHour = defaultData.selectedDateHour
        dataAppointmentToSubmit.meetType = defaultData.meetType
        dataAppointmentToSubmit.contact_name = defaultData.contact_name
        dataAppointmentToSubmit.company_name = defaultData.company_name
        dataAppointmentToSubmit.email = defaultData.email
        dataAppointmentToSubmit.url = defaultData.url
    }, 700)
}
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
                    <SelectAppointmentDate v-model="dataAppointmentToSubmit.selectedDateHour" :isLoading="isLoading"
                        :availableSchedulesOnMonth="availableSchedulesOnMonth" :dataAppointmentToSubmit="dataAppointmentToSubmit"
                        @onFinish="currentStep++" @onSelectHour="(newValue) => onSelectHour(newValue)" />
                </div>
                
                <!-- Second Step: Select date -->
                <div v-else-if="currentStep === 1" class="space-y-4 max-w-lg mx-auto">
                    <!-- <div></div> -->
                    <div class="text-center text-xl font-semibold text-gray-700">How we can contact you?</div>
                    <div class="flex flex-col mx-auto gap-x-2 gap-y-4 pl-0.5 text-gray-600">
                        <div>
                            <label for="fieldName"><span class="text-red-500">*</span>Name:</label>
                            <PureInput v-model="dataAppointmentToSubmit.contact_name" inputName="fieldName" placeholder="John Doe" />
                        </div>
                        <div>
                            <label for="fieldEmail"><span class="text-red-500">*</span>Email:</label>
                            <PureInput v-model="dataAppointmentToSubmit.email" inputName="fieldEmail" placeholder="johndoe@email.com" type="email" />
                        </div>
                        <div>
                            <label for="fieldCompany">Company name:</label>
                            <PureInput v-model="dataAppointmentToSubmit.company_name" inputName="fieldBusiness" placeholder="Flower's Shop" />
                        </div>

                        <div v-if="dataAppointmentToSubmit.email" class="mx-auto mt-4">
                            <Button @click="() => currentStep++" label="Confirm" size="xl" />
                        </div>
                        
                        
                    </div>
                </div>

                <!-- Third Step: Summary review -->
                <div v-else class="max-w-2xl mx-auto py-4">
                    <AppointmentSummary :dataAppointmentToSubmit="dataAppointmentToSubmit"
                        @onFinish="() => onSubmitAppointment()"
                        @decreaseStep="currentStep--"
                    />
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