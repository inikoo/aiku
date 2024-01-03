<script setup lang="ts">
import { watchEffect, reactive, ref } from 'vue'
import { RadioGroup, RadioGroupLabel, RadioGroupOption, RadioGroupDescription } from '@headlessui/vue'
import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/vue"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCircle, faCrown, faBars, faAbacus, faCommentsDollar } from '@fal'
import { faExclamationCircle ,faCheckCircle } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faCircle, faCrown, faBars, faAbacus, faCommentsDollar, faExclamationCircle, faCheckCircle)

const props = defineProps<{
    form?: any
    fieldName: string
    options: string[] | {}
    fieldData?: {
    }
}>()

interface selectedJob {
    [key: string]: string
}

const optionsJob = {
    admin: {
        department: "admin",
        icon: 'fal fa-crown',
        options: [
            {
                "code": "admin",
                "label": "Administrator",
            }
        ],
    },

    hr: {
        icon: "fal fa-user-hard-hat",
        department: "Human Resources",
        options: [
            {
                "code": "hr-m",
                "grade": "manager",
                "label": "Manager",
            }, {
                "code": "hr-c",
                "grade": "clerk",
                "label": "Worker",
            }
        ],
    },

    acc: {
        icon: "fal fa-abacus",
        department: "Accounting",
        options: [
            {
                "code": "acc-m",
                "grade": "manager",
                "label": "Manager",
            }, {
                "code": "acc-c",
                "grade": "clerk",
                "label": "Worker",
            }
        ],
    },

    mrk: {
        icon: "fal fa-comments-dollar",
        department: "Marketing",
        options: [
            {
                "code": "mrk-m",
                "grade": "manager",
                "label": "Manager",
            }, {
                "code": "mrk-c",
                "grade": "clerk",
                "label": "Worker",
            }
        ],
    },

    web: {
        icon: 'fal fa-globe',
        department: "Webmaster",
        options: [
            {
                "code": "web-m",
                "grade": "manager",
                "label": "Manager",
            }, {
                "code": "web-c",
                "grade": "clerk",
                "label": "Worker",
            }
        ],
    },

    buy: {
        // icon: "fal fa-user ",
        department: "Buyer",
        options: [
            {
                "code": "buy",
                "grade": "buyer",
                "label": "Buyer",
            }
        ],
    },

    wah: {
        // icon: "fal fa-user ",
        department: "Warehouse",
        options: [
            {
                "code": "wah-m",
                "grade": "manager",
                "label": "Manager",
            }, {
                "code": "wah-sk",
                "grade": "clerk",
                "label": "Stock Keeper",
            }, {
                "code": "wah-sc",
                "grade": "clerk",
                "label": "Stock Controller",
            }
        ],
    },

    dist: {
        // icon: "fal fa-user ",
        department: "Dispatch",
        options: [
            {
                "code": "dist-m",
                "grade": "manager",
                "label": "Manager",
            }, {
                "code": "dist-pik",
                "grade": "clerk",
                "label": "Picker",
            }, {
                "code": "dist-pak",
                "grade": "clerk",
                "label": "Packer",
            }
        ],
    },

    prod: {
        // icon: "fal fa-user ",
        department: "Production",
        options: [
            {
                "code": "prod-m",
                "grade": "manager",
                "label": "Manager",
            }, {
                "code": "prod-w",
                "grade": "clerk",
                "label": "Worker",
            }
        ],
    },

    cus: {
        // icon: "fal fa-user ",
        department: "Customer Service",
        options: [
            {
                "code": "cus-m",
                "grade": "manager",
                "label": "Manager",
            }, {
                "code": "cus-c",
                "grade": "clerk",
                "label": "Worker",
            }
        ],
    },
}

// Temporary data
const selectedBox: selectedJob = reactive({})
const selectShop = reactive({})

// To preserved on first load (so the box is selected)
for (const key in optionsJob) {
    for (const item of optionsJob[key].options) {
        if ((props.form[props.fieldName].map((option: any) => option = option.code)).includes(item.code)) {
            selectedBox[key] = item.code
        }
    }
}

// When the box is clicked
const handleClickBox = (jobGroupName: string, jobCode: any) => {
    if(selectedBox[jobGroupName] == 'admin'){  // If the box clicked is 'admin'
        if(selectedBox[jobGroupName] == jobCode) {  // When active box clicked
            selectedBox[jobGroupName] = ""  // Deselect value
        } else {
            selectedBox[jobGroupName] = jobCode
        }
    } else { // If the box clicked is not 'admin'
        if(selectedBox[jobGroupName] == jobCode && props.form[props.fieldName].length > 1) {  // When active box clicked
            selectedBox[jobGroupName] = ""  // Deselect value
        } else {
            selectedBox[jobGroupName] = jobCode
        }
    }
    props.form.errors[props.fieldName] = ''
}

// To save the temporary data (selectedBox) to props.form
watchEffect(() => {
    const tempObject = {...selectedBox}
    selectedBox.admin ? '' : delete tempObject.admin
    props.form[props.fieldName] = selectedBox.admin ? [selectedBox.admin] : Object.values(tempObject).filter(item=> item)
})

</script>

<template>
    <div class="relative">
    <!-- <pre>dd {{ selectShop }} aa</pre> -->
    <!-- <pre>{{ selectedBox }}</pre> -->
        <div class="flex flex-col text-xs divide-y-[1px]">
            <div v-for="(jobGroup, keyJob) in optionsJob" class="grid grid-cols-3 gap-x-1.5 px-2 items-center">
                <!-- The box -->
                <div class="flex items-center capitalize gap-x-1.5">
                    <FontAwesomeIcon v-if="jobGroup.icon" :icon="jobGroup.icon" class='text-gray-400' aria-hidden='true' />
                    {{ jobGroup.department }}
                </div>

                <!-- Section: Radio (the clickable area) -->
                <div class="h-fit col-span-2 grid"
                >
                    <div v-if="jobGroup.department == 'Customer Service' || jobGroup.department == 'Warehouse' || jobGroup.department == 'Webmaster'"
                        class="pt-2 h-full flex items-center row-span-1">
                        <RadioGroup v-model="selectShop[jobGroup.department]">
                            <RadioGroupLabel class="text-base font-semibold leading-6 text-gray-700 sr-only">Select the radio</RadioGroupLabel>
                            <div class="flex gap-x-4 justify-around">
                                <!-- Select: All Shop -->
                                <RadioGroupOption as="template" :key="jobGroup.department + keyJob + 1" value="ww" v-slot="{ active, checked }">
                                    <div class="relative flex cursor-pointer rounded-lg border bg-white py-2 px-3 shadow-sm focus:outline-none"
                                        :class="[checked ? 'ring-2 ring-gray-600' : 'border-gray-300']">
                                        All shop
                                    </div>
                                </RadioGroupOption>

                                <!-- Multiselect: Select Shop -->
                                <RadioGroupOption as="template" :key="jobGroup.department + keyJob + 2" value="qq" v-slot="{ active, checked }">
                                    <div :class="[
                                        'relative flex cursor-pointer rounded-lg border bg-white py-2 px-3 shadow-sm focus:outline-none',
                                        checked ? 'ring-2 ring-gray-600' : 'border-gray-300'
                                    ]">
                                        <Menu as="div" class="relative inline-block text-left">
                                            <MenuButton
                                                class="inline-flex min-w-fit w-32 max-w-full whitespace-nowrap justify-between items-center gap-x-2 rounded px-2.5 py-1 text-xs font-medium"
                                                :class="[ true ? 'bg-indigo-500 text-white hover:bg-indigo-600' : 'bg-slate-100 hover:bg-slate-200 text-slate-600 ring-1 ring-slate-300']"
                                            >
                                                <span class="">{{ true ? 'Select shop' : '' }}</span>
                                                <FontAwesomeIcon icon='far fa-chevron-down' class='text-xs' aria-hidden='true' />
                                            </MenuButton>
                                            <transition>
                                                <MenuItems
                                                    class="absolute left-0 mt-2 w-56 px-1 py-1 space-y-1 z-20 origin-top-right divide-y divide-gray-100 rounded bg-white shadow-lg ring-1 ring-slate-300 focus:outline-none">
                                                    <MenuItem v-for="(item, itemKey) in 4" v-slot="{ active }">
                                                        <button @click.prevent="true" :class="[
                                                            true ? 'bg-indigo-500 text-white' : active ? 'bg-indigo-200 text-indigo-600' : 'text-slate-700',
                                                            'group flex w-full items-center rounded px-2 py-2 text-sm',
                                                        ]">
                                                            {{ itemKey }}
                                                        </button>
                                                    </MenuItem>
                                                </MenuItems>
                                            </transition>
                                        </Menu>
                                    </div>
                                </RadioGroupOption>
                            </div>
                        </RadioGroup>
                        
                    </div>
                    
                    <div class="flex gap-x-2">
                        <button v-for="job in jobGroup.options"
                            @click.prevent="handleClickBox(keyJob, job.code)"
                            class="group h-full cursor-pointer flex items-center justify-start even:pl-5 odd:justify-center rounded-md py-3 px-3 font-medium capitalize disabled:text-gray-400 disabled:cursor-not-allowed disabled:ring-0 disabled:active:active:ring-offset-0"
                            :class="[
                                selectedBox[keyJob] == job.code ? 'text-lime-500' : ' text-gray-600'
                            ]"
                            :disabled="selectedBox.admin && job.code != 'admin'? true : false"
                        >
                            <span class="relative text-left">
                                <div class="absolute -left-1 -translate-x-full top-1/2 -translate-y-1/2">
                                    <FontAwesomeIcon v-if="selectedBox[keyJob] == 'admin'" icon='fas fa-check-circle' fixed-width aria-hidden='true' />
                                    <FontAwesomeIcon v-else-if="selectedBox[keyJob] == job.code && !selectedBox.admin" icon='fas fa-check-circle' fixed-width aria-hidden='true' />
                                    <FontAwesomeIcon v-else icon='fal fa-circle' fixed-width aria-hidden='true' />
                                </div>
                                <span :class="[
                                    selectedBox.admin && selectedBox[keyJob] != 'admin' ? 'text-gray-300' : ' text-gray-500 group-hover:text-gray-800'
                                ]">
                                    {{ job.label }}
                                </span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- State: error icon & error description -->
        <div v-if="form.errors[fieldName] || form.recentlySuccessful " class="mt-1 flex items-center gap-x-1.5 pointer-events-none">
            <FontAwesomeIcon icon="fas fa-exclamation-circle" v-if="form.errors[fieldName]" class="h-5 w-5 text-red-500" aria-hidden="true" />
            <FontAwesomeIcon icon="fas fa-check-circle" v-if="form.recentlySuccessful" class="h-5 w-5 text-green-500" aria-hidden="true"/>
            <p v-if="form.errors[fieldName]" class="text-sm text-red-600 ">{{ form.errors[fieldName] }}</p>
        </div>

    </div>
</template>
