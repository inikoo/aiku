<script setup lang="ts">
import Table from '@/Components/Table/Table.vue';
import { ref } from 'vue'
import JsonViewer from 'vue-json-viewer'
import { faPlus, faMinus } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"

library.add(faPlus,faMinus)

defineProps<{
    data: object,
    tab?:string
}>()

const index = ref(null)
const ExpandData = ref(null)

const formatDate = (dateIso: string) => {
    const date = new Date(dateIso)
    const year = date.getFullYear()
    const month = (date.getMonth() + 1).toString().padStart(2, '0')
    const day = date.getDate().toString().padStart(2, '0')
    const hours = date.getHours().toString().padStart(2, '0')
    const minutes = date.getMinutes().toString().padStart(2, '0')

    return `${year}-${month}-${day} ${hours}:${minutes}`
}

const onExpand = (data) => {
    ExpandData.value = data
    index.value = data.rowIndex
}


const onCloseExpand = (data) => {
    ExpandData.value = null
    index.value = null
}

</script>

<template>
    <!-- <pre>{{ data }}</pre> {{ tab }} -->
    <Table :resource="data" class="mt-5" :name="tab" :useExpandTable="true">
        <template #cell(expand)="{ item: user }">
            <div v-if="user?.rowIndex === index" class="p-4">
                <FontAwesomeIcon  @click="() => onCloseExpand(user)" icon='fas fa-minus' />
            </div>
            <div v-else class="p-4">
                <FontAwesomeIcon  @click="() => onExpand(user)" icon='fas fa-plus' />
            </div>
        </template>

        <template #cell(datetime)="{ item: user }">
            <span>{{ formatDate(user.datetime) }}</span>
        </template>

        <template #cell(old_values)="{ item: history }">
            <JsonViewer :value="history['old_values']" copyable sort />
        </template>

        <template #cell(new_values)="{ item: history }">
            <JsonViewer :value="history['new_values']" copyable sort />
        </template>

        <template #expandRow="{ item: data }">
            <div v-if="data?.rowIndex === index" class="bg-gray-50">
                <div class="p-4 bg-gray-50">
                        <dl class="grid grid-cols-1 sm:grid-cols-3">
                            <div class=" border-gray-100 px-4 py-6 sm:col-span-1 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900">IP Address</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:mt-2">{{ ExpandData.ip_address }}</dd>
                            </div>
                            <div class=" border-gray-100 px-4 py-6 sm:col-span-1 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900">User Agent</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:mt-2">{{ ExpandData.user_agent }}</dd>
                            </div>
                            <div class="border-gray-100 px-4 py-6 sm:col-span-1 sm:px-0">
                                <dt class="text-sm font-medium leading-6 text-gray-900">Auditable Type</dt>
                                <dd class="mt-1 text-sm leading-6 text-gray-700 sm:mt-2">{{ ExpandData.auditable_type }}</dd>
                            </div>
                        </dl>
                </div>
            </div>
        </template>
    </Table>
</template>
