<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'

const props = defineProps<{
    data: {
        workplace: {
            address: {
                formatted_address: string
            }
            number_clocking_machines: number
        }
    }
}>()
</script>

<template>
    <dl class="p-4 grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 sm:gap-y-8 lg:gap-x-8">

        <!-- Field: Items -->
        <!-- <div class="border-t border-gray-200 pt-4" v-if="blueprint.items.value.length > 0">
            <dt class="font-medium">{{ blueprint.items.label }}</dt>
            <dd class="mt-2 text-sm text-gray-500 text-justify">
                <span v-if="blueprint.items.value.length" class="flex gap-1">
                    <Tag v-for="item of blueprint.items.value" :key="item.id" :label="item.reference"
                        :theme="item.id" />
                </span>
                <span v-else class="text-gray-400 italic">No items in this pallet.</span>
            </dd>
        </div> -->

        <div class="border-t border-gray-200 pt-4">
            <dt class="font-medium">{{ trans("Location") }}</dt>
            <dd class="mt-2 text-sm text-gray-500 text-justify">
                <div v-html="data.workplace.address.formatted_address" class="p-3 border border-gray-300 rounded w-fit">

                </div>
            </dd>
        </div>

        <div class="border-t border-gray-200 pt-4">
            <dt class="font-medium">{{ trans("Number clocking machines") }}</dt>
            <dd class="mt-2 text-sm text-gray-500 text-justify">
                <!-- <Link v-if="blueprint.location.value.route?.name"
                    :href="route(blueprint.location.value.route.name, blueprint.location.value.route.parameters)"
                    class="primaryLink">
                {{ blueprint.location.value.resource.code }}
                </Link>
                <span v-else>{{ blueprint?.location?.value?.resource?.code }}</span> -->
                {{data.workplace.number_clocking_machines}}
            </dd>
        </div>

        <!-- <div class="border-t border-gray-200 pt-4">
            <dt class="font-medium">Info</dt>
            <dd class="mt-2 text-sm text-gray-500 text-justify">State:
                <Tag :class="'capitalize'" :label="data.data.state" ></Tag>
            </dd>
            <dd class="mt-2 text-sm text-gray-500 text-justify">Status :
                <Tag label="" :class="'text-white'"
                    :style="{ backgroundColor: data.data.status_icon.color }" >

                <template #label>
                    <div class="flex gap-2 capitalize">
                        <FontAwesomeIcon :icon="data.data.status_icon.icon"></FontAwesomeIcon>
                        <div>{{ data.data.status }}</div>
                    </div>
                </template>
                </Tag>
            </dd>
            fffffff
        </div> -->

        <!-- <div class="col-span-2 ">
            <dt class="font-medium">www</dt>
            <dd class="mt-2 text-sm text-gray-500 text-justify">
                    fffffffffffff
            </dd>
        </div> -->

        
    </dl>
</template>