<!--suppress JSDeprecatedSymbols -->
<script setup lang="ts">
import { trans } from 'laravel-vue-i18n'
// import AddressLocation from '@/Components/AddressLocation.vue'
import {
    faEnvelope, faPhone,
    faMapMarkerAlt, faCopy,
    faPersonDolly, faBoxFull,
    faBan, faArrowUp,
    faBuilding, faMale
} from '@fal'
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import ComponentImage from '@/Components/Image.vue'
import { useCopyText } from '@/Composables/useCopyText'
import AddressLocation from '@/Components/Elements/Info/AddressLocation.vue'
import { Agent } from '@/types/Grp/Agent'

library.add(faEnvelope, faPhone, faPersonDolly, faBoxFull, faCopy, faBan, faArrowUp, faMapMarkerAlt, faMale, faBuilding)

const props = defineProps<{
    data: Agent
}>()

</script>

<template>
    <div class="grid md:grid-flow-col w-fit">
        <!-- Images -->
        <div v-if="props.data.photo"
            class="relative place-self-center md:place-self-start rounded-md h-40 w-40 shadow overflow-hidden grid justify-center text-xs items-center">
            <!-- <img class="object-fit" :src="`/media/group/${props.data.photo}`" :alt="trans('Supplier Photo')"> -->
            <ComponentImage :src="data.photo" />
        </div>

        <!-- Contact Section -->
        <div class="pl-3">
            <div class="pt-0.5 flex flex-col text-sm pb-2 space-y-1 text-gray-500">

                <!-- Company name -->
                <div v-if="props.data.company" class="grid grid-flow-col justify-start items-center">
                    <FontAwesomeIcon fixed-width icon="fal fa-building" class="mr-4 text-gray-400" aria-hidden="true" />
                    {{ props.data.company }}
                    <div class="group cursor-pointer px-1.5 flex justify-center text-xl "
                        @click="useCopyText(props.data.company)">
                        <FontAwesomeIcon icon="fal fa-copy"
                            class="text-sm leading-none mr-1 opacity-20 group-hover:opacity-75 group-active:opacity-100"
                            aria-hidden="true" />
                    </div>
                </div>

                <!-- Contact name -->
                <div v-if="props.data.contact" class="grid grid-flow-col justify-start items-center pb-2">
                    <FontAwesomeIcon fixed-width icon="fal fa-male" class="mr-4 text-gray-400" aria-hidden="true" />
                    {{ props.data.contact }}
                    <div class="group cursor-pointer px-1.5 flex justify-center text-xl "
                        @click="useCopyText(props.data.contact)">
                        <FontAwesomeIcon icon="fal fa-copy"
                            class="text-sm leading-none mr-1 opacity-20 group-hover:opacity-75 group-active:opacity-100"
                            aria-hidden="true" />
                    </div>
                </div>

                <!-- Email address -->
                <div v-if="data.email && props.data.email.length != 0"
                    class="grid grid-flow-col justify-start items-center">
                    <FontAwesomeIcon fixed-width icon="fal fa-envelope" class="mr-4 text-gray-400" aria-hidden="true" />
                    <a :href="`mailto:${props.data.email}`" class="hover:text-indigo-500">{{ props.data.email }}</a>
                    <div class="group cursor-pointer px-1.5 flex justify-center text-xl "
                        @click="useCopyText(props.data.email)">
                        <FontAwesomeIcon icon="fal fa-copy"
                            class="text-sm leading-none mr-1 opacity-20 group-hover:opacity-75 group-active:opacity-100"
                            aria-hidden="true" />
                    </div>
                </div>

                <!-- Telephone address -->
                <div v-if="data.phone && props.data.phone.length != 0"
                    class="grid grid-flow-col justify-start items-center">
                    <FontAwesomeIcon fixed-width icon="fal fa-phone" class="mr-4 text-gray-400" aria-hidden="true" />
                    {{ props.data.phone }}
                    <div class="group cursor-pointer px-1.5 flex justify-center text-xl "
                        @click="useCopyText(props.data.phone)">
                        <FontAwesomeIcon icon="fal fa-copy"
                            class="text-sm leading-none mr-1 opacity-20 group-hover:opacity-75 group-active:opacity-100"
                            aria-hidden="true" />
                    </div>
                </div>

                <!-- Location address -->
                <div v-if="data.address" class="grid grid-flow-col gap-x-3 justify-start items-center">
                    <FontAwesomeIcon fixed-width icon="fal fa-map-marker-alt" class="text-gray-400"
                        aria-hidden="true" />
                    <div>
                        <AddressLocation :data="data.location" />
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<style lang="scss" scoped></style>
