<script setup lang="ts">
import { watchEffect, reactive, ref } from 'vue'
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue'
import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/vue"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCircle, faCrown, faBars, faAbacus, faCommentsDollar, faCheckDouble, faQuestionCircle } from '@fal'
import { faExclamationCircle ,faCheckCircle } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faCircle, faCrown, faBars, faAbacus, faCommentsDollar, faCheckDouble, faQuestionCircle, faExclamationCircle, faCheckCircle)

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

interface optionsJob {
    [key: string]: {
        options: {
            code: string
            label: string
            grade?: string
        }[]
        department: string
        icon?: string
    }
}

const optionsJob: optionsJob = {
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

const availableShops = ['inikoo', 'Aiku', 'AW', 'AWA']

// Temporary data
const selectedBox: selectedJob = reactive({})
const selectedShop: {[key: string]: { value: string, selectedShops: string[]}} = reactive({
    Webmaster: {
        value: '',
        selectedShops: []
    },
    Warehouse: {
        value: '',
        selectedShops: []
    },
    "Customer Service": {
        value: '',
        selectedShops: []
    },
})

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

// Method: check if 2 arrays is includes same values
const isEqual = (a: string[], b: string[]) => JSON.stringify([...a].sort()) === JSON.stringify([...b].sort())

// On select shop
const onSelectShop = (departmentName: string, shopName: string) => {
    const index = selectedShop[departmentName].selectedShops.indexOf(shopName)

    index === -1
        ? selectedShop[departmentName].selectedShops.push(shopName)
        : selectedShop[departmentName].selectedShops.splice(index, 1)
}

const onSelectUnselectShops = (departmentName: string) => {
    if(isEqual(selectedShop[departmentName].selectedShops, availableShops)){
        selectedShop[departmentName].selectedShops = []
    } else {
        selectedShop[departmentName].selectedShops = [...availableShops]
    }
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
    <!-- <pre>dd {{ selectedShop }} aa</pre> -->
    <!-- <pre>{{ selectedBox }}</pre> -->
        <div class="flex flex-col text-xs divide-y-[1px]">
            <div v-for="(jobGroup, keyJob) in optionsJob" class="grid grid-cols-3 gap-x-1.5 px-2 items-center">
                <!-- Section: Department -->
                <div class="flex items-center capitalize gap-x-1.5">
                    <FontAwesomeIcon v-if="jobGroup.icon" :icon="jobGroup.icon" class='text-gray-400' aria-hidden='true' />
                    {{ jobGroup.department }}
                </div>

                <!-- Section: Radio (the clickable area) -->
                <div class="h-fit col-span-2 grid">
                    <div v-if="jobGroup.department == 'Customer Service' || jobGroup.department == 'Warehouse' || jobGroup.department == 'Webmaster'"
                        class="pt-2 h-full flex items-center row-span-1">
                        <RadioGroup v-model="selectedShop[jobGroup.department].value">
                            <RadioGroupLabel class="text-base font-semibold leading-6 text-gray-700 sr-only">Select the radio</RadioGroupLabel>
                            <div class="flex gap-x-4 justify-around">
                                <!-- Select: All Shop -->
                                <RadioGroupOption as="template" value="All shops" :key="jobGroup.department + keyJob + 1" v-slot="{ active, checked }">
                                    <div class="relative flex items-center gap-x-1 cursor-pointer rounded-lg border bg-white py-2 px-3 shadow-sm focus:outline-none"
                                        :class="[checked ? 'ring-2 ring-indigo-500' : 'border-gray-300']">
                                        All shops and future shops
                                        <FontAwesomeIcon v-tooltip="'This option will select all current shops and new shop added in the future.'" icon='fal fa-question-circle' class='text-gray-500 cursor-pointer' aria-hidden='true' />
                                    </div>
                                </RadioGroupOption>

                                <!-- Multiselect: Select Shop -->
                                <RadioGroupOption as="template" value="selectShop" :key="jobGroup.department + keyJob + 2" v-slot="{ active, checked }">
                                    <div :class="[
                                        'relative flex cursor-pointer rounded-lg border bg-white py-2 px-3 shadow-sm focus:outline-none',
                                        checked ? 'ring-2 ring-indigo-500' : 'border-gray-300'
                                    ]">
                                        <div v-if="selectedShop[jobGroup.department].value != 'selectShop'" class="bg-transparent inset-0 absolute z-10" />
                                        <Menu as="div" class="relative inline-block text-left">
                                            <MenuButton
                                                :disabled="selectedShop[jobGroup.department].value != 'selectShop'"
                                                class="inline-flex min-w-fit w-32 max-w-full whitespace-nowrap justify-between items-center gap-x-2 rounded px-2.5 py-1 text-xs font-medium"
                                                :class="[ selectedShop[jobGroup.department].value == 'selectShop' ? selectedShop[jobGroup.department].selectedShops.length ? 'bg-indigo-500 text-white hover:bg-indigo-600' : 'bg-slate-100 hover:bg-slate-200 text-slate-600 ring-1 ring-slate-300' : 'bg-slate-100 text-slate-400 ring-1 ring-slate-300']"
                                            >
                                                <span class="">{{ selectedShop[jobGroup.department].selectedShops.length ? selectedShop[jobGroup.department].selectedShops.length === 1 ? selectedShop[jobGroup.department].selectedShops[0] : `${selectedShop[jobGroup.department].selectedShops[0]} and +${selectedShop[jobGroup.department].selectedShops.length-1}` : 'Select shops' }}</span>
                                                <FontAwesomeIcon icon='far fa-chevron-down' class='text-xs' aria-hidden='true' />
                                            </MenuButton>
                                            <transition>
                                                <MenuItems
                                                    class="absolute left-0 mt-2 w-56 px-1 py-1 space-y-1 z-20 origin-top-right divide-y divide-gray-100 rounded bg-white shadow-lg ring-1 ring-slate-300 focus:outline-none">
                                                    <MenuItem v-slot="{ active }">
                                                        <button @click.prevent="onSelectUnselectShops(jobGroup.department)" :class="[
                                                            'hover:bg-gray-100 text-slate-700 group flex gap-x-1 w-full items-center rounded px-2 py-2 text-sm',
                                                        ]">
                                                            <FontAwesomeIcon icon='fal fa-check-double' class='text-xs' aria-hidden='true' />
                                                            {{ isEqual(selectedShop[jobGroup.department].selectedShops, availableShops) ? 'Unselect all shops' : 'Select all shops' }}
                                                        </button>
                                                    </MenuItem>

                                                    <MenuItem v-for="(item, itemKey) in availableShops" v-slot="{ active }">
                                                        <button @click.prevent="onSelectShop(jobGroup.department, item)" :class="[
                                                            selectedShop[jobGroup.department].selectedShops.includes(item) ? 'bg-indigo-500 text-white' : active ? 'bg-indigo-200 text-indigo-600' : 'text-slate-700',
                                                            'group flex w-full items-center rounded px-2 py-2 text-sm',
                                                        ]">
                                                            {{ item }}
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
