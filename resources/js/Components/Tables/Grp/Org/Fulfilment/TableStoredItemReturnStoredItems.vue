<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from '@/Components/Table/Table.vue';
import { ref } from 'vue';
import Button from '@/Components/Elements/Buttons/Button.vue';
import Icon from "@/Components/Icon.vue"
import PureInputNumber from '@/Components/Pure/PureInputNumber.vue'
import { Link, router } from "@inertiajs/vue3"
import { notify } from "@kyvg/vue3-notification"
import { Switch } from '@headlessui/vue'

const props = defineProps<{
    data?: {}
    tab?: string
    state: any
    key: any
}>()

console.log(props)

const isLoading = ref<string | boolean>(false)
const selectedRow = ref({})
const showAll = ref(true)
const _table = ref(null)

const onShowSelected = (ButtonData) => {
    showAll.value = false
    const data = props.data.data
    const finalValue = {}
    

    for (const rowId in selectedRow.value) {
        if (selectedRow.value[rowId]) {
            // Find the corresponding data entry by id
            const tempData = data.find((item) => item.id == rowId);
            if (tempData) {
                // Add the selected item to the finalValue object
                finalValue[rowId] = { quantity: tempData.quantity };
            }
        }
    }


    router[ButtonData.route.method](
        route(ButtonData.route.name, ButtonData.route.parameters),
        { stored_items: finalValue },
        {
            onSuccess: () => { },
            onError: (error: {} | string) => {
                notify({
                    title: 'Something went wrong.',
                    text: 'Failed to save',
                    type: 'error',
                })
            }
        })
}

const onShowAll = () =>{
    showAll.value = true
}

</script>

<template>
    <Table :resource="data" :name="'stored_items'" class="mt-5" :isCheckBox="true"
        @onSelectRow="(value) => selectedRow = value" ref="_table">

        <template #button-save="{ linkButton: value }">
            <div class="mx-4">
                  <Button label="Show Selected" @click="() => onShowSelected(value)" />
                <!-- <div class="flex items-center gap-x-3">
                    <Switch :class="showAll ? '' : ''"
                       @click="() => showAll ? onShowSelected() : onShowAll()"
                        class="pr-1 relative inline-flex h-5 aspect-[2/1] shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors bg-white ring-1 ring-slate-300 duration-200 shadow ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75">
                        <span aria-hidden="true"
                            :class="!showAll ? 'translate-x-5 bg-indigo-500' : 'translate-x-0 bg-slate-300'"
                            class="pointer-events-none inline-block h-full w-1/2 transform rounded-full  shadow-lg ring-0 transition duration-200 ease-in-out" />
                    </Switch>
                    <div class="text-base leading-none font-medium cursor-pointer select-none"
                        :class="!showAll ? 'text-indigo-500' : ' text-gray-400'">
                        Selected
                    </div>
                </div> -->
            </div>
        </template>

        <template #cell(reference)="{ item: value }">
            {{ value.reference }}
        </template>

        <template #cell(state)="{ item: palletDelivery }">
            <Icon :data="palletDelivery['state_icon']" class="px-1" />
        </template>

        <template #cell(quantity)="{ item: item }">
            <div class='w-full flex justify-end'>
                <div class="flex w-32 justify-end">
                    <PureInputNumber v-model="item.data.quantity" @update:modelValue="(e) => item.quantity = e" :maxValue="item.total_quantity" :minValue="1" />
                </div>
            </div>
        </template>

        <!--  <template #cell(actions)="{ item: value }">
              <div v-if="state == 'in-process'">
                  <Link :href="route(value.deleteRoute.name, value.deleteRoute.parameters)" method="delete"
                      preserve-scroll as="div" @start="() => isLoading = 'delete' + value.id"
                      v-tooltip="'Delete Stored Item'">
                     <Button icon="far fa-trash-alt" :loading="isLoading === 'delete' + value.id" type="negative" />
                  </Link>
              </div>
          </template> -->

    </Table>
</template>