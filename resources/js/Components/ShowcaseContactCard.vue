<!--suppress JSDeprecatedSymbols -->
<script setup lang="ts">
import { trans } from 'laravel-vue-i18n'
import AddressLocation from '@/Components/AddressLocation.vue'
import {
    faEnvelope, faPhone,
    faMapMarkerAlt, faCopy,
    faPersonDolly, faBoxFull,
    faBan, faArrowUp,
    faBuilding, faMale
} from '@fal';
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

library.add(faEnvelope, faPhone, faPersonDolly, faBoxFull, faCopy, faBan, faArrowUp, faMapMarkerAlt, faMale, faBuilding);

const props = defineProps<{
    data: {
        company?: string
        contact?: string
        email?: string
        phone?: string
        address?: {
            address_line_1: string
            address_line_2: string
            sorting_code: string
            postal_code: number
            locality: string
            dependent_locality: string
            administrative_area: string
            country_code: string
            country_id: number
            checksum: string
            created_at: string
            updated_at: string
            country: {
                data: {
                    code: string
                    iso3: string
                    name: string
                }
            }
        }
        image_id?: any
    }
}>();

const copyText = (text: string) => {
    const textarea = document.createElement("textarea");
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand("copy");
    textarea.remove();
};

</script>

<template>
    <div class="grid md:grid-flow-col w-fit">
        <!-- Images -->
        <div v-if="props.data.image_id" class="relative place-self-center md:place-self-start rounded-md h-40 w-40 shadow overflow-hidden grid justify-center text-xs items-center bg-gray-300">
            <img class="object-fit" :src="`/media/group/${props.data.image_id}`" :alt="trans('Supplier Photo')">
        </div>

        <!-- Contact Section -->
        <div class="pl-3">
            <div class="pt-0.5 flex flex-col text-sm pb-2 space-y-1 text-gray-500">

                <!-- Company name -->
                <div v-if="props.data.company" class="grid grid-flow-col justify-start items-center">
                    <FontAwesomeIcon  fixed-width icon="fal fa-building" class="mr-4 text-gray-400" aria-hidden="true" />
                    {{ props.data.company }}
                    <div class="group cursor-pointer px-1.5 flex justify-center text-xl " @click="copyText(props.data.company)">
                        <FontAwesomeIcon icon="fal fa-copy"
                                         class="text-sm leading-none mr-1 opacity-20 group-hover:opacity-75 group-active:opacity-100" aria-hidden="true" />
                    </div>
                </div>

                <!-- Contact name -->
                <div v-if="props.data.contact" class="grid grid-flow-col justify-start items-center pb-2">
                    <FontAwesomeIcon  fixed-width icon="fal fa-male" class="mr-4 text-gray-400" aria-hidden="true" />
                    {{ props.data.contact }}
                    <div class="group cursor-pointer px-1.5 flex justify-center text-xl " @click="copyText(props.data.contact)">
                        <FontAwesomeIcon icon="fal fa-copy"
                                         class="text-sm leading-none mr-1 opacity-20 group-hover:opacity-75 group-active:opacity-100" aria-hidden="true" />
                    </div>
                </div>

                <!-- Email address -->
                <div v-if="data.email &&  props.data.email.length != 0" class="grid grid-flow-col justify-start items-center">
                    <FontAwesomeIcon  fixed-width icon="fal fa-envelope" class="mr-4 text-gray-400" aria-hidden="true" />
                    <a :href="`mailto:${props.data.email}`" class="hover:text-indigo-500">{{ props.data.email }}</a>
                    <div class="group cursor-pointer px-1.5 flex justify-center text-xl " @click="copyText(props.data.email)">
                        <FontAwesomeIcon icon="fal fa-copy"
                                         class="text-sm leading-none mr-1 opacity-20 group-hover:opacity-75 group-active:opacity-100" aria-hidden="true" />
                    </div>
                </div>

                <!-- Telephone address -->
                <div v-if="data.phone && props.data.phone.length != 0" class="grid grid-flow-col justify-start items-center">
                    <FontAwesomeIcon  fixed-width icon="fal fa-phone" class="mr-4 text-gray-400" aria-hidden="true" />
                    {{ props.data.phone }}
                    <div class="group cursor-pointer px-1.5 flex justify-center text-xl " @click="copyText(props.data.phone)">
                        <FontAwesomeIcon icon="fal fa-copy"
                                         class="text-sm leading-none mr-1 opacity-20 group-hover:opacity-75 group-active:opacity-100" aria-hidden="true" />
                    </div>
                </div>

                <!-- Location address -->
                <div v-if="data.address" class="grid grid-flow-col justify-start items-start pl-0">
                    <AddressLocation :data="props.data.address"/>
                </div>
            </div>

        </div>
    </div>
</template>

<style lang="scss" scoped></style>
