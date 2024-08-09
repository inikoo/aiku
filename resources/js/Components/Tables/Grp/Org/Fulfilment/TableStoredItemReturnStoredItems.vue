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

  const props = defineProps<{
      data?: {}
      tab?: string
      state:any
      key:any
  }>()

  const isLoading = ref<string | boolean>(false)
  const selectedRow = ref({})

  const onShowSelected = (ButtonData) =>{
    const finalValue = []
        for(const row in selectedRow.value){
          if(selectedRow.value[row].checked) finalValue.push({quantity: selectedRow.value[row].data.quantity})
        }

    router[ButtonData.route.method](
        route(ButtonData.route.name, ButtonData.route.parameters),
        { stored_items: finalValue },
        {
            onSuccess: () => {},
            onError: (error: {} | string) => {
                notify({
                    title: 'Something went wrong.',
                    text: 'Failed to save',
                    type: 'error',
                })
            }
        })
  }

  </script>

  <template>
      <Table :resource="data" :name="'stored_items'" class="mt-5" :isCheckBox="true" @onSelectRow="(value)=>selectedRow = value">

       <template #button-save="{ linkButton : value }">
       <div>
            <Button label="Show Selected" @click="()=>onShowSelected(value)"/>
       </div>
       </template>

          <template #cell(reference)="{ item: value }">
              {{ value.reference }}
          </template>

          <template #cell(state)="{ item: palletDelivery }">
                  <Icon  :data="palletDelivery['state_icon']" class="px-1" />
          </template>

          <template #cell(quantity)="{ item: item }">
               <PureInputNumber v-model="item.quantity" :maxValue="item.total_quantity" :minValue="1" />
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
