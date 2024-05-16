    <script setup lang="ts">
import { inject, reactive, ref } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCircle, faCrown, faBars, faAbacus, faCommentsDollar, faCheckDouble, faQuestionCircle, faTimes, faCheckCircle as falCheckCircle } from '@fal'
import { faExclamationCircle, faCheckCircle as fasCheckCircle } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import { get } from 'lodash'
import { layoutStructure } from '@/Composables/useLayoutStructure'
library.add(faCircle, faCrown, faBars, faAbacus, faCommentsDollar, faCheckDouble, faQuestionCircle, faTimes, faExclamationCircle, fasCheckCircle, falCheckCircle)

interface TypeShop {
    id: number
    slug: string
    code: string
    name: string
    type: string
    state: string
}

interface TypeWarehouse {
    name: string
    slug: string
    state: string
}

interface TypeFulfilments {
    code: string
    id: number
    name: string
    sales: {}
    slug: string
    state: string
    type: string
}

interface optionsJob {
    department: string
    icon?: string
    subDepartment: {
        slug: string
        label: string
        grade?: string
        optionsType?: string[]
        number_employees: number
    }[]
    options?: TypeShop[] | TypeWarehouse[]
    optionsSlug?: string[]
    optionsClosed?: TypeShop[] | TypeWarehouse[]
    optionsType?: string
    value?: any
}

const layout = inject('layout', layoutStructure)

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
        fulfilments: {
            data: TypeFulfilments
        }
        organisations: {}
        shops: {
            data: TypeShop[]
        }
        warehouses: {
            data: TypeWarehouse[]
        }
    }
    fieldData?: {
    }
}>()

// console.log(props.options)

const optionsJob = reactive<{ [key: string]: optionsJob }>({
    admin: {
        department: "admin",
        icon: 'fal fa-crown',
        subDepartment: [
            {
                slug: "admin",
                label: "Administrator",
                number_employees: props.options.positions.data.find(position => position.slug == 'admin')?.number_employees || 0,
            }
        ],
        // value: null
    },

    hr: {
        icon: "fal fa-user-hard-hat",
        department: "Human Resources",
        subDepartment: [
            {
                slug: "hr-m",
                grade: "manager",
                label: "Supervisor",
                number_employees: props.options.positions.data.find(position => position.slug == 'hr-m')?.number_employees || 0,
            },
            {
                slug: "hr-c",
                grade: "clerk",
                label: "Worker",
                number_employees: props.options.positions.data.find(position => position.slug == 'hr-c')?.number_employees || 0,
            }
        ],
        // value: null
    },

    acc: {
        icon: "fal fa-abacus",
        department: "Accounting",
        subDepartment: [
            {
                slug: "acc-m",
                grade: "manager",
                label: "Supervisor",
                number_employees: props.options.positions.data.find(position => position.slug == 'acc-m')?.number_employees || 0,
            },
            {
                slug: "acc-c",
                grade: "clerk",
                label: "Worker",
                number_employees: props.options.positions.data.find(position => position.slug == 'acc-c')?.number_employees || 0,
            }
        ],
        // value: null
    },

    mrk: {
        icon: "fal fa-comments-dollar",
        department: "Deals",
        subDepartment: [
            {
                slug: "mrk-m",
                grade: "manager",
                label: "Supervisor",
                optionsType: ['shops'],
                number_employees: props.options.positions.data.find(position => position.slug == 'mrk-m')?.number_employees || 0,
            },
            {
                slug: "mrk-c",
                grade: "clerk",
                label: "Worker",
                optionsType: ['shops'],
                number_employees: props.options.positions.data.find(position => position.slug == 'mrk-c')?.number_employees || 0,
            }
        ],
        optionsClosed: props.options.shops.data?.filter(job => job.state != 'open'),
        optionsSlug: props.options.shops.data?.filter(job => job.state == 'open').map(job => job.slug),
        // value: null
    },

    web: {
        icon: 'fal fa-globe',
        department: "Webmaster",
        subDepartment: [
            {
                slug: "web-m",
                grade: "manager",
                label: "Supervisor",
                optionsType: ['shops'],
                number_employees: props.options.positions.data.find(position => position.slug == 'web-m')?.number_employees || 0,
            },
            {
                slug: "web-c",
                grade: "clerk",
                label: "Worker",
                optionsType: ['shops'],
                number_employees: props.options.positions.data.find(position => position.slug == 'web-c')?.number_employees || 0,
            }
        ],
        optionsClosed: props.options.shops.data?.filter(job => job.state != 'open'),
        optionsSlug: props.options.shops.data?.filter(job => job.state == 'open').map(job => job.slug),
        // value: null
    },

    buy: {
        department: "Buyer",
        subDepartment: [
            {
                slug: "buy",
                grade: "buyer",
                label: "Buyer",
                number_employees: props.options.positions.data.find(position => position.slug == 'buy')?.number_employees || 0,
            }
        ],
        // value: null
    },

    wah: {
        department: "Warehouse",
        subDepartment: [
            {
                slug: "wah-m",
                grade: "manager",
                label: "Supervisor",
                optionsType: ['warehouses'],
                number_employees: props.options.positions.data.find(position => position.slug == 'wah-m')?.number_employees || 0,
            },
            {
                slug: "wah-sk",
                grade: "clerk",
                label: "Stock Keeper",
                optionsType: ['warehouses'],
                number_employees: props.options.positions.data.find(position => position.slug == 'wah-sk')?.number_employees || 0,
            },
            {
                slug: "wah-sc",
                grade: "clerk",
                label: "Stock Controller",
                optionsType: ['warehouses'],
                number_employees: props.options.positions.data.find(position => position.slug == 'wah-sc')?.number_employees || 0,
            }
        ],
        // value: null
    },

    dist: {
        department: "Dispatch",
        subDepartment: [
            {
                slug: "dist-m",
                grade: "manager",
                label: "Supervisor",
                optionsType: ['warehouses'],
                number_employees: props.options.positions.data.find(position => position.slug == 'dist-m')?.number_employees || 0,
            },
            {
                slug: "dist-pik",
                grade: "clerk",
                label: "Picker",
                optionsType: ['warehouses'],
                number_employees: props.options.positions.data.find(position => position.slug == 'dist-pik')?.number_employees || 0,
            },
            {
                slug: "dist-pak",
                grade: "clerk",
                label: "Packer",
                optionsType: ['warehouses'],
                number_employees: props.options.positions.data.find(position => position.slug == 'dist-pak')?.number_employees || 0,
            }
        ],
        // value: null
    },

    prod: {
        department: "Manufacturing",
        subDepartment: [
            {
                slug: "prod-m",
                grade: "manager",
                label: "Supervisor",
                number_employees: props.options.positions.data.find(position => position.slug == 'prod-m')?.number_employees || 0,
            },
            {
                slug: "prod-w",
                grade: "clerk",
                label: "Worker",
                number_employees: props.options.positions.data.find(position => position.slug == 'prod-w')?.number_employees || 0,
            }
        ],
        // value: null
    },

    cus: {
        department: "Customer Service",
        subDepartment: [
            {
                slug: "cus-m",
                grade: "manager",
                label: "Supervisor",
                optionsType: ['shops'],
                number_employees: props.options.positions.data.find(position => position.slug == 'cus-m')?.number_employees || 0,
            },
            {
                slug: "cus-c",
                grade: "clerk",
                label: "Worker",
                optionsType: ['shops'],
                number_employees: props.options.positions.data.find(position => position.slug == 'cus-c')?.number_employees || 0,
            }
        ],
        optionsClosed: props.options.shops.data?.filter(job => job.state != 'open'),
        optionsSlug: props.options.shops.data?.filter(job => job.state == 'open').map(job => job.slug),
        // value: null
    },

    ful: {
        department: "Fulfilment",
        subDepartment: [
            {
                slug: "ful-m",
                grade: "manager",
                label: "Supervisor",
                optionsType: ['fulfilments', 'warehouses'],
                number_employees: props.options.positions.data.find(position => position.slug == 'cus-m')?.number_employees || 0,
            },
            {
                slug: "ful-wc",
                grade: "clerk",
                label: "Warehouse Clerk",
                optionsType: ['warehouses'],
                number_employees: props.options.positions.data.find(position => position.slug == 'ful-wc')?.number_employees || 0,
            },
            {
                slug: "ful-c",
                grade: "clerk",
                label: "Office Clerk",
                optionsType: ['fulfilments'],
                number_employees: props.options.positions.data.find(position => position.slug == 'ful-c')?.number_employees || 0,
            }
        ],
        optionsSlug: props.options.warehouses.data.map(job => job.slug),
        // value: null
    },
})

const optionsList = {
    shops: props.options.shops.data?.filter(job => job.state == 'open'),
    fulfilments: props.options.fulfilments.data,
    warehouses: props.options.warehouses.data,
    positions: props.options.positions.data
}

console.log('options', props.options)

// console.log('options Job', props.options.warehouses.data)
// Temporary data
const openFinetune = ref('')

// When the radio is clicked
const handleClickSubDepartment = (department: string, subDepartmentSlug: any, optionType: string[]) => {
    // ('mrk', 'mrk-c', ['shops', 'fulfilment'])

    // If click on the active subDepartment, then unselect it
    if (props.form[props.fieldName][subDepartmentSlug]) {
        delete props.form[props.fieldName][subDepartmentSlug]
    } else {
        for (const key in props.form[props.fieldName]) {
            // key == wah-m || mrk-c || hr-c
            // Check if the 'wah-m' contain the substring 'wah'
            if (key.includes(department)) {
                // If the selected radio is not same group ('manager' group or 'clerk' group)
                if (optionsJob[department].subDepartment.find(sub => sub.slug == key)?.grade != optionsJob[department].subDepartment.find(sub => sub.slug == subDepartmentSlug)?.grade) {
                    // Delete mrk-c
                    delete props.form[props.fieldName][key]
                }
            }
        }

        // If department have 'options' (i.e. web, wah, cus)
        if(optionType.some(option => optionsList[option])){
            props.form[props.fieldName][subDepartmentSlug] = {}  // declare empty object so able to put new key
            for (const type in optionType) {
                // type == 'fulfilment' | 'warehouse' | 'shop'
                props.form[props.fieldName][subDepartmentSlug][optionType[type]] = optionsList[optionType[type]].map(xxx => xxx.slug)
            }
        } else {
            // If department is simple department (have no shops/warehouses)
            props.form[props.fieldName][subDepartmentSlug] = []
        }
    }

    props.form.errors[props.fieldName] = ''
}

// Method: on clicked radio inside 'Advanced selection'
const onClickJobFinetune = (departmentName: string, shopName: string, subDepartmentSlug: any, optionType: string) => {
    // ('mrk', 'mrk-c', ['shops', 'fulfilment'])

    // If 'uk' is exist in mrk-m then delete it
    if (get(props.form[props.fieldName], [subDepartmentSlug, optionType], []).includes(shopName)) {
        if (props.form[props.fieldName][subDepartmentSlug][optionType].length === 1) {
            // if mrk-m.shops: ['uk'] (only 1 length), then delete mrk-m
            delete props.form[props.fieldName][subDepartmentSlug]
        } else {
            // if mrk-m.shops: ['uk', 'ed'] (more than 1 length), then delete
            const indexShopName = get(props.form[props.fieldName], [subDepartmentSlug, optionType], []).indexOf(shopName)
            if (indexShopName !== -1) {
                props.form[props.fieldName][subDepartmentSlug][optionType].splice(indexShopName, 1)
            }
        }
    } else {
        for (const key in props.form[props.fieldName]) {
            // // key == wah-m || mrk-c || hr-c
            // if wah-m include wah
            if (key.includes(departmentName)) {
                // console.log('pp', get(props.form[props.fieldName], [key, optionType], []))
                // console.log('pp', shopName)
                const indexShopName = get(props.form[props.fieldName], [key, optionType], []).indexOf(shopName)
                if (indexShopName !== -1) {
                    // if wah-c: ['uk'] then delete 'uk'
                    props.form[props.fieldName][key][optionType].splice(indexShopName, 1)

                    // if wah-c.shops: [] then delete it
                    if (!props.form[props.fieldName][key][optionType].length) {
                        delete props.form[props.fieldName][key]
                    }
                }
            }
        }

        // if mrk-m already exist
        if (get(props.form[props.fieldName], [subDepartmentSlug, optionType], false)) {
            props.form[props.fieldName][subDepartmentSlug][optionType].push(shopName)
        }

        // if mrk-m not exist then create array ['uk']
        else {
            props.form[props.fieldName][subDepartmentSlug] = {
                [optionType]: [shopName]
            }
        }
    }

    props.form.errors[props.fieldName] = ''
}

const fulfilmentLength = layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_fulfilments.length || 0
const shopLength = layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_shops.length || 0
const warehouseLength = layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_warehouses.length || 0
const productionLength = layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.authorised_productions.length || 0

// console.log('===', layout.organisations.data)
// console.log('===', layout.currentParams)
// console.log('===', fulfilmentLength)
</script>

<template>
    <div class="relative">
        <div class="flex flex-col text-xs divide-y-[1px]">
            <template v-for="(jobGroup, departmentName, idxJobGroup) in optionsJob" :key="departmentName + idxJobGroup">
                <div v-if="(departmentName === 'prod'  && productionLength > 0) || departmentName !== 'prod'" class="grid grid-cols-3 gap-x-1.5 px-2 items-center even:bg-gray-50 transition-all duration-200 ease-in-out">
                    <!-- Section: Department -->
                    <div class="flex items-center capitalize gap-x-1.5">
                        <FontAwesomeIcon v-if="jobGroup.icon" :icon="jobGroup.icon" class='text-gray-400' aria-hidden='true' />
                        {{ jobGroup.department }}
                    </div>
                    <!-- Section: Radio (the clickable area) -->
                    <div class="h-full col-span-2 flex-col transition-all duration-200 ease-in-out">
                        <div class="flex items-center divide-x divide-slate-300">
                            <!-- Button: Radio position -->
                            <div class="pl-2 flex items-center gap-x-4">
                                <template v-for="subDepartment, idxSubDepartment in jobGroup.subDepartment">
                                    <!-- If subDepartment is have atleast 1 Fulfilment, or have atleast 1 Shop, or have atleast 1 Warehouse, or have atleast 1 Production, or is a simple sub department (i.e buyer, administrator, etc) -->
                                    <button
                                        v-if="(subDepartment.optionsType?.includes('fulfilments') && fulfilmentLength > 0) || (subDepartment.optionsType?.includes('shops') && shopLength > 0) || (subDepartment.optionsType?.includes('warehouses') && warehouseLength > 0) || (subDepartment.optionsType?.includes('productions') && productionLength > 0) || !subDepartment.optionsType"
                                        @click.prevent="handleClickSubDepartment(departmentName, subDepartment.slug, subDepartment.optionsType)"
                                        class="group h-full cursor-pointer flex items-center justify-start rounded-md py-3 px-3 font-medium capitalize disabled:text-gray-400 disabled:cursor-not-allowed disabled:ring-0 disabled:active:active:ring-offset-0"
                                        :class="Object.keys(form[fieldName]).includes('admin') && subDepartment.slug == 'admin' ? 'text-green-500' : ''"
                                        :disabled="Object.keys(form[fieldName]).includes('admin') && subDepartment.slug != 'admin' ? true : false"
                                    >
                                        <span class="relative text-left">
                                            <!-- dd{{ layout.organisations.data.find(organisation => organisation.slug == layout.currentParams.organisation)?.[`authorised_${subDepartment.optionsType}`] }} -->
                                            <div class="absolute -left-1 -translate-x-full top-1/2 -translate-y-1/2">
                                                <template v-if="Object.keys(form[fieldName]).includes('admin')">
                                                    <FontAwesomeIcon v-if="idxSubDepartment === 0" icon='fas fa-check-circle' class="" fixed-width aria-hidden='true' />
                                                    <FontAwesomeIcon v-else icon='fal fa-circle' class="" fixed-width aria-hidden='true' />
                                                </template>
                                                <template v-else-if="Object.keys(form[fieldName]).includes(subDepartment.slug)">
                                                <!-- a{{ jobGroup.optionsSlug?.some(value => form[fieldName][subDepartment.slug]?.includes(value)) }}d -->
                                                    <FontAwesomeIcon v-if="jobGroup.optionsSlug?.every(value => get(form[fieldName], [subDepartment.slug, subDepartment.optionsType], []).includes(value))" icon='fas fa-check-circle' class="text-green-500" fixed-width aria-hidden='true' />
                                                    <FontAwesomeIcon v-else-if="jobGroup.optionsSlug?.some(value => get(form[fieldName], [subDepartment.slug, subDepartment.optionsType], []).includes(value))" icon='fal fa-check-circle' class="text-green-500" fixed-width aria-hidden='true' />
                                                    <FontAwesomeIcon v-else icon='fas fa-check-circle' class="text-green-500" fixed-width aria-hidden='true' />
                                                </template>
                                                <FontAwesomeIcon v-else icon='fal fa-circle' fixed-width aria-hidden='true' />
                                            </div>
                                            <span v-tooltip="subDepartment.number_employees + ' employees on this position'" :class="[
                                                Object.keys(form[fieldName]).includes('admin') && departmentName != 'admin' ? 'text-gray-400' : 'text-gray-600 group-hover:text-gray-800'
                                            ]">
                                                {{ subDepartment.label }}
                                            </span>
                                        </span>
                                    </button>
                                </template>
                            </div>

                            <!-- Button: Advanced selection -->
                            <div v-if="jobGroup.subDepartment.some(subDep => subDep.optionsType?.some(option => optionsList[option]?.length > 1))" class="flex gap-x-2 px-3">
                                <button @click.prevent="() => openFinetune = openFinetune == jobGroup.department ? '' : jobGroup.department"
                                    class="underline disabled:no-underline whitespace-nowrap cursor-pointer disabled:cursor-auto disabled:text-gray-400"
                                >
                                    Advanced selection
                                </button>
                            </div>
                        </div>

                        <!-- Fine tune content -->
                        <Transition mode="in-out">
                            <div v-if="openFinetune == jobGroup.department" class="relative bg-slate-400/10 border border-gray-300 rounded-md py-2 px-2 mb-3">
                                <div class="space-y-0.5 mb-3">
                                    <div v-for="option in jobGroup.options" class="grid grid-cols-3 hover:bg-gray-700/10 py-[2px] pl-2 rounded">
                                        <div class="font-semibold">{{ option.name }} </div>
                                        <div class="col-span-2 flex gap-x-2">
                                            <button v-for="(subDepartment, idxSubDepartment) in jobGroup.subDepartment"
                                                @click.prevent="onClickJobFinetune(departmentName, option.slug, subDepartment.slug, subDepartment.optionsType)"
                                                class="group h-full cursor-pointer flex items-center justify-start rounded-md px-3 font-medium capitalize disabled:text-gray-400 disabled:cursor-not-allowed disabled:ring-0 disabled:active:active:ring-offset-0"
                                                :disabled="!!Object.keys(form[fieldName]).includes('admin')"
                                            >
                                                <div class="relative text-left">
                                                    <div class="absolute -left-0.5 -translate-x-full top-1/2 -translate-y-1/2">
                                                        <template v-if="Object.keys(form[fieldName]).includes('admin')">
                                                            <FontAwesomeIcon v-if="idxSubDepartment === 0" icon='fas fa-check-circle' class="" fixed-width aria-hidden='true' />
                                                            <FontAwesomeIcon v-else icon='fal fa-circle' class="" fixed-width aria-hidden='true' />
                                                        </template>
                                                        <template v-else-if="get(form[fieldName], [subDepartment.slug, subDepartment.optionsType], []).includes(option.slug)">
                                                        <!-- a{{ jobGroup.optionsSlug?.some(value => form[fieldName][subDepartment.slug]?.includes(value)) }}d -->
                                                            <FontAwesomeIcon v-if="Object.keys(get(form[fieldName], [subDepartment.slug, subDepartment.optionsType], {})).includes('admin')" icon='fal fa-circle' class="" fixed-width aria-hidden='true' />
                                                            <FontAwesomeIcon v-else icon='fas fa-check-circle' class="text-green-500" fixed-width aria-hidden='true' />
                                                        </template>
                                                        <FontAwesomeIcon v-else icon='fal fa-circle' fixed-width aria-hidden='true' />
                                                    </div>
                                                    <span v-tooltip="subDepartment.number_employees + ' employees on this position'" :class="[
                                                        Object.keys(form[fieldName]).includes('admin') && departmentName != 'admin'? 'text-gray-300' : ' text-gray-500 group-hover:text-gray-700'
                                                    ]">
                                                        {{ subDepartment.label }}
                                                    </span>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="jobGroup.optionsClosed?.length" class="px-2 bg-gray-400/20 py-2 rounded">
                                    <div class="flex items-center gap-x-1">
                                        <FontAwesomeIcon icon='fal fa-info-circle' class='h-3' fixed-width aria-hidden='true' />
                                        These {{jobGroup.optionsType}} can't be selected due closed:
                                    </div>
                                    <div v-for="option, idxOption in jobGroup.optionsClosed" class="inline opacity-70">
                                        <template v-if="idxOption != 0">, </template>
                                        {{ option.name }}
                                    </div>
                                </div>
                                <div @click="openFinetune = ''" class="absolute top-1 right-2 w-fit px-1 text-slate-400 hover:text-slate-500 cursor-pointer hover:">
                                    <FontAwesomeIcon icon='fal fa-times' class='' aria-hidden='true' />
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </template>
        </div>

        <!-- State: error icon & error description -->
        <Transition name="spin-to-down">
            <FontAwesomeIcon v-if="form.errors[fieldName]" icon="fas fa-exclamation-circle" class="absolute top-0 right-5 h-6 w-6 text-red-500" aria-hidden="true" />
            <FontAwesomeIcon v-else-if="form.recentlySuccessful" icon="fas fa-check-circle" class="absolute top-0 right-5 h-6 w-6 text-green-500" aria-hidden="true"/>
        </Transition>
        <div v-if="form.errors[fieldName] " class="mt-1 flex items-center gap-x-1.5 pointer-events-none">
            <p class="text-sm text-red-500 italic">*{{ form.errors[fieldName] }}</p>
        </div>

    </div>

    <!-- <pre>{{ props.form[props.fieldName] }}</pre> -->
</template>
