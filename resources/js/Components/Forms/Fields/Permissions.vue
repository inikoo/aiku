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
const selectedOrganisation = ref<typeof organisation[number] | boolean>(organisation[0])

</script>

<template>
    <div class="flex flex-col gap-y-6">
    <!-- <pre>{{ form[fieldName] }}</pre> -->
        <div class="">
            <nav class="isolate flex divide-x divide-gray-200 rounded-lg shadow" aria-label="Tabs">
                <div v-for="(org, idxOrg) in organisation"
                    :key="idxOrg"
                    @click="selectedOrganisation = org"
                    :class="[
                        selectedOrganisation.slug === org.slug ? 'text-gray-900' : 'text-gray-500 hover:text-gray-700',
                        idxOrg === 0 ? 'rounded-l-lg' : '',
                        idxOrg === idxOrg - 1 ? 'rounded-r-lg' : ''
                    ]"
                    class="cursor-pointer relative min-w-0 flex-1 overflow-hidden bg-white px-4 py-4 text-center text-sm font-medium hover:bg-gray-50 focus:z-10"
                >
                    <span>{{ org.label }}</span>
                    <span aria-hidden="true" :class="[selectedOrganisation.slug === org.slug ? 'bg-indigo-500' : 'bg-transparent', 'absolute inset-x-0 bottom-0 h-0.5']" />
                </div>
            </nav>
        </div>
        {{ selectedOrganisation.slug }} --- {{ form[fieldName][selectedOrganisation.slug] }}
        
        <EmployeePosition
            v-if="options?.[selectedOrganisation.slug]"
            :key="'employeePosition' + selectedOrganisation.slug "
            :form="form[fieldName]"
            :fieldData
            :fieldName="selectedOrganisation.slug"
            :options="options?.[selectedOrganisation.slug]"
        />

    </div>
</template>
