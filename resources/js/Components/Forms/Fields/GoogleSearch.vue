<script setup lang='ts'>
import PureInput from '@/Components/Pure/PureInput.vue'
import PureTextarea from '@/Components/Pure/PureTextarea.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faInfoCircle } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { trans } from 'laravel-vue-i18n'
library.add(faInfoCircle)

const props = defineProps<{
    form?: any
    fieldName: string
    options: string[] | {}
    fieldData?: {
    }
}>()

// console.log('======', props.form[props.fieldName])

// const dummyValue = reactive({
//     seourl: 'https://www.nenecourtemporium.co.uk/collections/frenchic-wall-paint',
//     seotitle: 'Frenchic Wall paint - NEXT WORKING DAY Delivery',
//     seodescription: 'Frenchic Wall Paint - Transform your space with the eco-friendly, luxurious choice for discerning decorators. Our high-quality, low-VOC paint provides a durable, elegannt finish, perfect for any room'
// })
</script>

<template>
    <div class="max-w-2xl bg-gray-50 p-6 rounded-md">
    <!-- <pre>{{ fieldData.chanonicalPrefix }}</pre> -->

        <!-- Preview: Google Search -->
        <div class="min-h-44 mx-auto mb-4 bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-blue-600 hover:text-blue-700 font-medium text-2xl break-words">
                {{ form[fieldName].seotitle || 'Wall paint - Bight, Shine, Protection'}}
            </div>
            <div class="text-green-700">
                {{ fieldData.chanonicalPrefix.label }}{{ form[fieldName].seourl || '' }}
            </div>
            <div class="text-gray-600 break-words">
                {{ form[fieldName].seodescription || 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Illum inventore vitae quas accusamus, numquam quo aut. Vero modi nihil provident!' }}
            </div>
        </div>

        <!-- Section: Field -->
        <div class="space-y-4">
            <div>
                <label for="seotitle" class="text-gray-600 font-semibold cursor-pointer">Title</label>
                <PureInput v-model="form[fieldName].seotitle" inputName="seotitle" :maxLength="70" placeholder="Wall paint - Bight, Shine, Protection" />
                <div class="mt-1 text-gray-500 italic">{{ form[fieldName].seotitle?.length || 0 }} of 70 characters used</div>
            </div>

            <div>
                <label for="seodescription" class="text-gray-600 font-semibold cursor-pointer">SEO Description</label>
                <PureTextarea v-model="form[fieldName].seodescription" inputName="seodescription" rows="6" maxLength="320" placeholder="Lorem ipsum dolor, sit amet consectetur adipisicing elit. Illum inventore vitae quas accusamus, numquam quo aut. Vero modi nihil provident!" />
                <div class="mt-1 text-gray-500 italic">{{ form[fieldName].seodescription?.length || 0 }} of 320 characters used</div>
            </div>

            <div>
                <label for="seoUrl" class="text-gray-600 font-semibold cursor-pointer">Chanonical URL</label>
                <PureInput v-model="form[fieldName].seourl" inputName="seoUrl" placeholder="profile" :prefix="{label: fieldData.chanonicalPrefix.label}"/>
            </div>

            <div>
                <label for="redirecturl" class="text-gray-600 font-semibold cursor-pointer">
                    <span>Redirect URL</span>
                    <FontAwesomeIcon v-tooltip="trans('Separate each URL with comma')" icon='fal fa-info-circle' class='ml-1 text-gray-400' fixed-width aria-hidden='true' />
                </label>
                <PureTextarea v-model="form[fieldName].redirecturl" inputName="redirecturl" rows="6" maxLength="320" placeholder="Lorem ipsum dolor, sit amet consectetur adipisicing elit. Illum inventore vitae quas accusamus, numquam quo aut. Vero modi nihil provident!" />
                <div class="mt-1 text-gray-500 italic tabular-nums">{{ form[fieldName].redirecturl?.split(',').map(item => item.trim()).filter(item => item !== '').length }} URL will be redirected</div>
            </div>
        </div>
    </div>
</template>