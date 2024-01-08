<script setup lang="ts">
import { watchEffect, reactive, ref } from 'vue'
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue'
import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/vue"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCircle, faCrown, faBars, faAbacus, faCommentsDollar, faCheckDouble, faQuestionCircle, faTimes } from '@fal'
import { faExclamationCircle ,faCheckCircle } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import { options } from 'floating-vue'
library.add(faCircle, faCrown, faBars, faAbacus, faCommentsDollar, faCheckDouble, faQuestionCircle, faTimes, faExclamationCircle, faCheckCircle)

const props = defineProps<{
    form?: any
    fieldName: string
    options: {
        positions: {
            data: {
                id: number
                slug: string
                name: string
                number_employees: number
            }[]
        }
        organisations: {}
        shops: {
            data: {
                id: number
                slug: string
                code: string
                name: string
                type: string
            }[]
        }
        warehouses: {}
    }
    fieldData?: {
    }
}>()

interface selectedJob {
    [key: string]: string
}

interface optionsJob {
    [key: string]: {
        department: string
        icon?: string
        subDepartment: {
            slug: string
            label: string
            grade?: string
            number_employees: number
        }[]
        options?: {}
        value: any
    }
}

const dummyShops = [
    {
        "id": 4,
        "slug": "au",
        "code": "au",
        "name": "Au b2b shop",
        "type": "b2b"
    },
    {
        "id": 5,
        "slug": "af",
        "code": "af",
        "name": "AFF Dom Shop",
        "type": "b2b"
    },
    {
        "id": 6,
        "slug": "po",
        "code": "po",
        "name": "PRO Only 55 AP SHOP",
        "type": "b2b"
    }
]

const dummyWarehouses = [
    {
        "id": 4,
        "slug": "wl",
        "code": "wl",
        "name": "Warehouse Lembeng Bali",
        "type": "b2b"
    },
    {
        "id": 5,
        "slug": "wd",
        "code": "wd",
        "name": "Warehouse Dom Shop",
        "type": "b2b"
    },
    {
        "id": 6,
        "slug": "wu",
        "code": "wu",
        "name": "WH UK Semantic",
        "type": "b2b"
    }
]

const optionsJob: optionsJob = reactive({
    admin: {
        department: "admin",
        icon: 'fal fa-crown',
        subDepartment: [
            {
                "slug": "admin",
                "label": "Administrator",
                number_employees: props.options.positions.data.find(position => position.slug == 'admin')?.number_employees ?? 0,
            }
        ],
        value: null
    },

    hr: {
        icon: "fal fa-user-hard-hat",
        department: "Human Resources",
        subDepartment: [
            {
                "slug": "hr-m",
                "grade": "manager",
                "label": "Manager",
                number_employees: props.options.positions.data.find(position => position.slug == 'hr-m')?.number_employees ?? 0,
            }, {
                "slug": "hr-c",
                "grade": "clerk",
                "label": "Worker",
                number_employees: props.options.positions.data.find(position => position.slug == 'hr-c')?.number_employees ?? 0,
            }
        ],
        value: null
    },

    acc: {
        icon: "fal fa-abacus",
        department: "Accounting",
        subDepartment: [
            {
                "slug": "acc-m",
                "grade": "manager",
                "label": "Manager",
                number_employees: props.options.positions.data.find(position => position.slug == 'acc-m')?.number_employees ?? 0,
            }, {
                "slug": "acc-c",
                "grade": "clerk",
                "label": "Worker",
                number_employees: props.options.positions.data.find(position => position.slug == 'acc-c')?.number_employees ?? 0,
            }
        ],
        value: null
    },

    mrk: {
        icon: "fal fa-comments-dollar",
        department: "Marketing",
        subDepartment: [
            {
                "slug": "mrk-m",
                "grade": "manager",
                "label": "Manager",
                number_employees: props.options.positions.data.find(position => position.slug == 'mrk-m')?.number_employees ?? 0,
            }, {
                "slug": "mrk-c",
                "grade": "clerk",
                "label": "Worker",
                number_employees: props.options.positions.data.find(position => position.slug == 'mrk-c')?.number_employees ?? 0,
            }
        ],
        options: dummyShops,
        value: null
    },

    web: {
        icon: 'fal fa-globe',
        department: "Webmaster",
        subDepartment: [
            {
                "slug": "web-m",
                "grade": "manager",
                "label": "Manager",
                number_employees: props.options.positions.data.find(position => position.slug == 'web-m')?.number_employees ?? 0,
            }, {
                "slug": "web-c",
                "grade": "clerk",
                "label": "Worker",
                number_employees: props.options.positions.data.find(position => position.slug == 'web-c')?.number_employees ?? 0,
            }
        ],
        options: dummyShops,
        value: null
    },

    buy: {
        department: "Buyer",
        subDepartment: [
            {
                "slug": "buy",
                "grade": "buyer",
                "label": "Buyer",
                number_employees: props.options.positions.data.find(position => position.slug == 'buy')?.number_employees ?? 0,
            }
        ],
        value: null
    },

    wah: {
        department: "Warehouse",
        subDepartment: [
            {
                "slug": "wah-m",
                "grade": "manager",
                "label": "Manager",
                number_employees: props.options.positions.data.find(position => position.slug == 'wah-m')?.number_employees ?? 0,
            }, {
                "slug": "wah-sk",
                "grade": "clerk",
                "label": "Stock Keeper",
                number_employees: props.options.positions.data.find(position => position.slug == 'wah-sk')?.number_employees ?? 0,
            }, {
                "slug": "wah-sc",
                "grade": "clerk",
                "label": "Stock Controller",
                number_employees: props.options.positions.data.find(position => position.slug == 'wah-sc')?.number_employees ?? 0,
            }
        ],
        options: dummyWarehouses,
        value: null
    },

    dist: {
        department: "Dispatch",
        subDepartment: [
            {
                "slug": "dist-m",
                "grade": "manager",
                "label": "Manager",
                number_employees: props.options.positions.data.find(position => position.slug == 'dist-m')?.number_employees ?? 0,
            }, {
                "slug": "dist-pik",
                "grade": "clerk",
                "label": "Picker",
                number_employees: props.options.positions.data.find(position => position.slug == 'dist-pik')?.number_employees ?? 0,
            }, {
                "slug": "dist-pak",
                "grade": "clerk",
                "label": "Packer",
                number_employees: props.options.positions.data.find(position => position.slug == 'dist-pak')?.number_employees ?? 0,
            }
        ],
        options: dummyWarehouses,
        value: null
    },

    prod: {
        department: "Production",
        subDepartment: [
            {
                "slug": "prod-m",
                "grade": "manager",
                "label": "Manager",
                number_employees: props.options.positions.data.find(position => position.slug == 'prod-m')?.number_employees ?? 0,
            }, {
                "slug": "prod-w",
                "grade": "clerk",
                "label": "Worker",
                number_employees: props.options.positions.data.find(position => position.slug == 'prod-w')?.number_employees ?? 0,
            }
        ],
        value: null
    },

    cus: {
        department: "Customer Service",
        subDepartment: [
            {
                "slug": "cus-m",
                "grade": "manager",
                "label": "Manager",
                number_employees: props.options.positions.data.find(position => position.slug == 'cus-m')?.number_employees ?? 0,
            }, {
                "slug": "cus-c",
                "grade": "clerk",
                "label": "Worker",
                number_employees: props.options.positions.data.find(position => position.slug == 'cus-c')?.number_employees ?? 0,
            }
        ],
        options: dummyShops,
        value: null
    },
})

// Temporary data
const openFinetune = ref('')
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
    for (const item of optionsJob[key].subDepartment) {
        if ((props.form[props.fieldName].map((option: any) => option = option.slug)).includes(item.slug)) {
            selectedBox[key] = item.slug
        }
    }
}

// When the box is clicked
const handleClickSubDepartment = (department: string, subDepartmentSlug: any) => {
    if(department == 'admin'){  // If the box clicked is 'admin'
        optionsJob[department].value = optionsJob[department].value == subDepartmentSlug ? "" : subDepartmentSlug
    } else { // If the box clicked is not 'admin'
        if(optionsJob[department].options){  // If have options shops/warehouses
            console.log('eeee', optionsJob[department].value)
            if(optionsJob[department].value && Object.values(optionsJob[department].value ?? {}).every(value => value === subDepartmentSlug)){
                // console.log('www')
                optionsJob[department].value = null
            } else {
                // console.log('qqq')
                optionsJob[department].value = dummyShops.reduce((accumulator, shop) => {
                    accumulator[shop.slug] = subDepartmentSlug
                    return accumulator
                }, {})
            }
        } else {
            console.log('eeeeeeee')
            optionsJob[department].value = optionsJob[department].value == subDepartmentSlug ? "" : subDepartmentSlug
        }
    }

    // console.log(optionsJob[department].value)
    props.form.errors[props.fieldName] = ''
}

// When the box warehouses/shops is clicked
const onClickJobFinetune = (departmentName: string, jobGroupName: string, jobCode: any) => {
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

// Method: check if array value is exist in array of object is includes same values
const isEqual = (arrayString: string[], arrayObject: {slug: string}[]) => {
    if(!arrayString.length) {
        // arrayString == 0
        return false
    }
    return arrayString.every(value => arrayObject.some(obj => obj.slug === value))
}

// On select shop
const onSelectShop = (departmentName: string, shopName: string) => {
    const index = selectedShop[departmentName].selectedShops.indexOf(shopName)

    index === -1
        ? selectedShop[departmentName].selectedShops.push(shopName)
        : selectedShop[departmentName].selectedShops.splice(index, 1)
}

// On click select all/unselect all
const onSelectUnselectShops = (departmentName: string) => {
    if(isEqual(selectedShop[departmentName].selectedShops, props.options.shops.data)){
        // if all shop is alreayd selected then make it empty
        selectedShop[departmentName].selectedShops = []
    } else {
        selectedShop[departmentName].selectedShops = []
        props.options.shops.data.forEach(shop => {
            // console.log('foreach', shop)
            selectedShop[departmentName].selectedShops.push(shop.slug)
        })
        // selectedShop[departmentName].selectedShops = [...props.options.shops.data]
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
    <pre>{{ optionsJob.hr.value }} ======</pre>
    <pre>{{ selectedShop }}</pre>
        <div class="flex flex-col text-xs divide-y-[1px]">
            <div v-for="(jobGroup, departmentName) in optionsJob" class="grid grid-cols-3 gap-x-1.5 px-2 items-center even:bg-gray-50">
                <!-- Section: Department -->
                <div class="flex items-center capitalize gap-x-1.5">
                    <FontAwesomeIcon v-if="jobGroup.icon" :icon="jobGroup.icon" class='text-gray-400' aria-hidden='true' />
                    {{ jobGroup.department }}
                </div>

                <!-- Section: Radio (the clickable area) -->
                <div class="h-fit col-span-2 flex-col transition-all duration-200 ease-in-out">
                    <div class="flex items-center divide-x divide-slate-300">
                        <!-- Button: Radio position -->
                        <div class="pl-2 flex items-center gap-x-4">
                            <button v-for="job in jobGroup.subDepartment"
                                @click.prevent="handleClickSubDepartment(departmentName, job.slug)"
                                class="group h-full cursor-pointer flex items-center justify-start rounded-md py-3 px-3 font-medium capitalize disabled:text-gray-400 disabled:cursor-not-allowed disabled:ring-0 disabled:active:active:ring-offset-0"
                                :class="[
                                    (optionsJob[departmentName].options ? optionsJob[departmentName].value : optionsJob[departmentName].value == job.slug) && (optionsJob[departmentName].options ? Object.values(optionsJob[departmentName].value ?? {}).every(value => value === job.slug) : true) ? 'text-lime-500' : ' text-gray-600'
                                ]"
                                :disabled="optionsJob.admin.value && job.slug != 'admin' ? true : false"
                            >
                                <span class="relative text-left">
                                    <div class="absolute -left-1 -translate-x-full top-1/2 -translate-y-1/2">
                                        <FontAwesomeIcon v-if="optionsJob.admin.value" icon='fas fa-check-circle' fixed-width aria-hidden='true' />
                                        <FontAwesomeIcon v-else-if="(optionsJob[departmentName].options ? optionsJob[departmentName].value : optionsJob[departmentName].value == job.slug) && (optionsJob[departmentName].options ? Object.values(optionsJob[departmentName].value ?? {}).every(value => value === job.slug) : true) && !optionsJob.admin.value" icon='fas fa-check-circle' fixed-width aria-hidden='true' />
                                        <FontAwesomeIcon v-else icon='fal fa-circle' fixed-width aria-hidden='true' />
                                    </div>
                                    <span v-tooltip="job.number_employees + ' employees on this position'" :class="[
                                        selectedBox.admin && selectedBox[departmentName] != 'admin' ? 'text-gray-300' : ' text-gray-500 group-hover:text-gray-800'
                                    ]">
                                        {{ job.label }} ({{ job.number_employees }})
                                    </span>
                                </span>
                            </button>
                        </div>

                        <!-- Section: All shops & Fine tunes -->
                        <div v-if="jobGroup.department == 'Customer Service' || jobGroup.department == 'Marketing' || jobGroup.department == 'Webmaster' || jobGroup.department == 'Dispatch' || jobGroup.department == 'Warehouse'" class="flex gap-x-2 px-3">
                            <div class="flex gap-x-1 items-center">
                                <input type="checkbox" :name="jobGroup.department + 'allshops'" :id="jobGroup.department + 'allshops'" class="h-3 w-3 appearance-none">
                                <label :for="jobGroup.department + 'allshops'" class="cursor-pointer">{{ jobGroup.department == 'Dispatch' || jobGroup.department == 'Warehouse' ? 'All warehouses' : 'All shops'}}</label>
                            </div>
                            <div @click="() => openFinetune = jobGroup.department" class="underline whitespace-nowrap cursor-pointer">Fine tunes</div>
                        </div>
                    </div>

                    <!-- Fine tune content -->
                    <transition mode="in-out">
                        <div v-if="openFinetune == jobGroup.department" class="qwezxc">
                            <div v-for="shop in jobGroup.options" class="flex gap-x-4">
                                <div class="font-semibold">{{ shop.name }} </div>
                                <div class="flex">
                                    <button v-for="job in jobGroup.subDepartment"
                                        @click.prevent="onClickJobFinetune(jobGroup.department, departmentName, job.slug)"
                                        class="group h-full cursor-pointer flex items-center justify-start rounded-md px-3 font-medium capitalize disabled:text-gray-400 disabled:cursor-not-allowed disabled:ring-0 disabled:active:active:ring-offset-0"
                                        :class="[
                                            true ? 'text-lime-500' : ' text-gray-600'
                                        ]"
                                        :disabled="selectedBox.admin && job.slug != 'admin'? true : false"
                                    >
                                        <span class="relative text-left">
                                            <div class="absolute -left-0.5 -translate-x-full top-1/2 -translate-y-1/2">
                                                <FontAwesomeIcon v-if="selectedBox[departmentName] == 'admin'" icon='fas fa-check-circle' fixed-width aria-hidden='true' />
                                                <FontAwesomeIcon v-else-if="selectedBox[departmentName] == job.slug && !selectedBox.admin" icon='fas fa-check-circle' fixed-width aria-hidden='true' />
                                                <FontAwesomeIcon v-else icon='fal fa-circle' fixed-width aria-hidden='true' />
                                            </div>
                                            <span v-tooltip="job.number_employees + ' employees on this position'" :class="[
                                                selectedBox.admin && selectedBox[departmentName] != 'admin' ? 'text-gray-300' : ' text-gray-500 group-hover:text-gray-700'
                                            ]">
                                                {{ job.label }} ({{ job.number_employees }})
                                            </span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                            <div @click="openFinetune = ''" class="mt-4 w-fit text-red-400 hover:text-red-500 cursor-pointer hover:">
                                <FontAwesomeIcon icon='fal fa-times' class='' aria-hidden='true' />
                                Close
                            </div>
                        </div>
                    </transition>

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
