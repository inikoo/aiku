<script setup lang="ts">
import { Ref, ref } from 'vue'
import { Collapse } from 'vue-collapsed'
import CardPermissions from './Components/Permissions/Card.vue'
import { get } from 'lodash'
import EmployeePosition from '@/Components/Forms/Fields/EmployeePosition.vue'

const props = defineProps<{
    form: {
        [key: string]: {
            position_name: string  // Administrator
            organisations: {
                [key: string]: string[]  // aw: [uk, de, fr]
            }
            shops: string[]
            warehouses: string[]
            fulfilments: string[]
        }[]
    }
    fieldName: string
    options?: any
    fieldData?: {
        
    }
}>()

const handleBox = (shopsSelected: string[], shopSlug: string) => {
    // console.log('ffff', shopsSelected)
    // if (shopsSelected.includes(shopSlug)) {
        
    //     const indexShopSlug = shopsSelected.indexOf(shopSlug)
    //     if (indexShopSlug !== -1) {
    //         shopsSelected.splice(indexShopSlug, 1)
    //     } else {
    //         shopsSelected.push(shopSlug)
    //     }
    // }
}

// const xxx = [
//     {
//         "Super admin": {
//             "organisations": ['awa', 'aw']
//         }
//     }
// ]

// const abc = {
//     "Human resources supervisor": {
//         "Ancient Wisdom": {
//             "shops": ['uk', 'ed', 'awa'],
//             "warehouse": ['ed'],
//         },
//         "AW Gifts": {
//             "fulfilment": ['awf', 'es'],
//         }
//     },
//     "Super Admin": {
//         "AW Europe": {
//             "warehouse": ['ed', 'bl'],
//         }
//     }
// }

const organisation = [
    {
        label: 'Ancient Wisdom',
        slug: 'aw'
    },
    {
        label: 'Ancient Wisdom SRO',
        slug: 'sk'
    },
    {
        label: 'Aromatics',
        slug: 'aroma'
    },
    {
        label: 'AW Spain',
        slug: 'es'
    },
]
const selectedOrganisation = ref<typeof organisation[number] | null>(organisation[0])
console.log('ppp', props.fieldData)

</script>

<template>
    <div class="flex flex-col gap-y-6">
        <div class="grid">
            <div class="flex justify-between px-2 border-b border-gray-300 bg-gray-100 py-2 mb-2">
                <div>
                    Organisations
                </div>
                <div>
                    Number positions
                </div>
            </div>
            <div v-for="(review, slugReview) in props.fieldData.organisation_list.data"
                class="flex flex-col mb-1 gap-y-1"
            >
                <div
                    @click="selectedOrganisation?.slug == review.slug ? selectedOrganisation = null : selectedOrganisation = review"
                    class="cursor-pointer py-1 px-2 flex justify-between"
                    :class="review.slug === selectedOrganisation?.slug ? 'rounded bg-indigo-100 text-indigo-500' : 'hover:bg-gray-200/70 '"
                >
                    <div class="">{{ review.name }}</div>
                    <div class="pl-3 pr-2">{{ review.number_job_positions }}</div>
                </div>
                <Collapse as="section" :when="review.slug == selectedOrganisation?.slug">
                    <div v-if="options?.[review.slug]" class="border border-gray-300 rounded-md mb-2">
                        <EmployeePosition
                            :key="'employeePosition' + review.slug " :form="form[fieldName]" :fieldData
                            :fieldName="review.slug" :options="options?.[review.slug]" />
                    </div>
                    <div v-else class="text-center border border-gray-300 rounded-md mb-2">
                        No data positions
                    </div>
                </Collapse>
            </div>
        </div>


        <!-- <pre>{{ form[fieldName] }}</pre> -->
        <!-- <div class="">
            <nav class="isolate flex divide-x divide-gray-200 rounded-lg shadow" aria-label="Tabs">
                <div v-for="(org, idxOrg) in organisation" :key="idxOrg" @click="selectedOrganisation = org" :class="[
                        selectedOrganisation?.slug === org.slug ? 'text-gray-900' : 'text-gray-500 hover:text-gray-700',
                        idxOrg === 0 ? 'rounded-l-lg' : '',
                        idxOrg === idxOrg - 1 ? 'rounded-r-lg' : ''
                    ]"
                    class="cursor-pointer relative min-w-0 flex-1 overflow-hidden bg-white px-4 py-4 text-center text-sm font-medium hover:bg-gray-50 focus:z-10">
                    <span>{{ org.label }}</span>
                    <span aria-hidden="true"
                        :class="[selectedOrganisation?.slug === org.slug ? 'bg-indigo-500' : 'bg-transparent', 'absolute inset-x-0 bottom-0 h-0.5']" />
                </div>
            </nav>
        </div> -->
        {{ selectedOrganisation?.slug }} --- {{ form[fieldName][selectedOrganisation?.slug] }}

        

    </div>
</template>
