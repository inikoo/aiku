<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 27 Nov 2023 14:23:03 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faPaperPlane } from '@fas/'
import { faEnvelope, faPhone, faHouse } from '@fal/'
import { library } from '@fortawesome/fontawesome-svg-core'
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue'
import PureInput from '@/Components/Pure/PureInput.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { trans } from "laravel-vue-i18n";
import { useLocaleStore } from "@/Stores/locale";
import Popover from '@/Components/Utils/Popover.vue'
import { get, startCase } from 'lodash'
import Tag from "@/Components/Tag.vue"
import { ref } from "vue"
import { notify } from "@kyvg/vue3-notification"
import axios from "axios"

library.add(faPaperPlane, faEnvelope, faPhone, faHouse)
const props = defineProps<{
    option: {
        data: {
            id: number
            name: string
            number_items: number
        }[]
    }
}>()

const emits = defineEmits();

const value = ref({
    quantity: get(props.option, ["constrains", "prospect_last_contacted", "data", "quantity"], 1),
    unit: get(props.option, ["constrains", "prospect_last_contacted", "data", "unit"], 'week')
})

const findIcon = (data) => {
    if (data == 'email') return 'fal fa-envelope'
    if (data == 'phone') return 'fal fa-phone'
    if (data == 'address') return 'fal fa-house'
}


const onChangeLastContact = async (closed) => {
    try {
        const response = await axios.get(
            route('org.crm.shop.prospects.mailshots.query.number-items', { ...route().params, query: props.option.slug }),
            {params: { ...value.value }}
        );
       onSuccessful(response.data)

    } catch (error) {
        notify({
            title: "Failed",
            text: "Failed to update data, please try again",
            type: "error"
        });
    }
}

const onSuccessful=(response)=>{
    const newData = {...props.option}
    newData.constrains.prospect_last_contacted.data.quantity = value.value.quantity
    newData.constrains.prospect_last_contacted.data.unit = value.value.unit
    props.option.number_items = response.count
    emits("update:option", newData); 
}

</script>

<template>
    <div class="flex items-center gap-x-2">
        <!-- Icon: Email -->
        <div v-if="option.constrains.can_contact_by.fields.length > 0" class="inline-flex items-start">
            <div class="relative inline-flex">
                <Popover :width="'w-full'" position="right-[-60px] top-[-70px]" ref="_popover">
                    <template #button>
                        <div class="relative" title="testing email">
                            <font-awesome-icon :icon="['fas', 'paper-plane']" />
                        </div>
                    </template>
                    <template #content="{ close: closed }">
                        <div class="flex gap-2">
                            <div v-for="(item, index) in option.constrains.can_contact_by.fields">
                                <Tag :theme="index + 1" size="sm">
                                    <template #label>
                                        <FontAwesomeIcon :icon="findIcon(item)" class='' aria-hidden='true' />
                                        {{ startCase(item) }}
                                    </template>

                                </Tag>
                            </div>
                        </div>

                    </template>
                </Popover>
            </div>
        </div>
        <p v-if="!option.has_arguments" class="text-gray-500">(Not contacted yet)</p>
        <p v-else class="text-gray-500 whitespace-nowrap">
            (Last contacted at:
        <div class="relative inline-flex">
            <Popover :width="'w-full'" position="right-[-60px]" ref="_popover">
                <template #button>
                    <div class="font-bold specialUnderlineOrg py-1 focus:outline-none focus:ring-0">
                        {{ get(option, ['constrains', 'prospect_last_contacted', 'data', 'quantity'], 0) }}
                        {{ get(option, ['constrains', 'prospect_last_contacted', 'data', 'unit'], 'week') }}
                    </div>
                </template>
                <template #content="{ close: closed }">
                    <div class="flex flex-col">
                        <div class="text-center text-base font-semibold">Interval</div>
                        <div class="flex gap-x-2">
                            <div class="w-20">
                                <PureInput v-model.number="value.quantity" type="number" :minValue="1" :caret="false"
                                    placeholder="days" required />
                            </div>
                            <div class="w-40">
                                <PureMultiselect v-model="value.unit" :options="['day', 'week', 'month']" required />
                            </div>
                        </div>
                        <div class="mt-2 text-gray-500 italic flex justify-between">
                            <p>Last contacted <span class="font-bold">
                                    {{ option.constrains.prospect_last_contacted?.data?.quantity }}
                                    {{ option.constrains.prospect_last_contacted.data.unit }}</span></p>
                            <Button label="OK" size="xxs" @click="onChangeLastContact(closed)"  />
                        </div>
                    </div>
                </template>
            </Popover>
        </div>
        )
        </p>
    </div>
</template>
