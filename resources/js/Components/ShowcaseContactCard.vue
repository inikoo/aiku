<script setup lang="ts">
import {
    faEnvelope, faPhone,
    faMapMarkerAlt, faCopy,
    faPersonDolly, faBoxFull,
    faBan, faArrowUp,
    faBuilding, faMale
} from "../../../resources/private/pro-light-svg-icons";
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

library.add(faEnvelope, faPhone, faPersonDolly, faBoxFull, faCopy, faBan, faArrowUp, faMapMarkerAlt, faMale, faBuilding);

const props = defineProps<{
    data: {
        company?: string
        contact?: string
        email?: string
        phone?: string
        address?: string
        photo?: string
    }
}>();

console.log(props.data);

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
    <div class="grid grid-flow-col w-fit">
        <div class="relative rounded-md h-40 w-40 shadow overflow-hidden grid justify-center m-2">
            <img class="object-fit" src="https://source.unsplash.com/featured/300x300" alt="">
        </div>
        <div class="pl-3 pt-3">

            <!-- Contact Section -->
            <div class="pt-4 flex flex-col text-sm pb-2 space-y-1">
                <div v-if="data.company" class="grid grid-flow-col justify-start items-center">
                    <FontAwesomeIcon  fixed-width icon="fal fa-building" class="mr-4" aria-hidden="true" />
                    {{ data.company }}
                    <div class="group cursor-pointer px-1.5 flex justify-center text-xl " @click="copyText(data.company)">
                        <FontAwesomeIcon icon="fal fa-copy"
                                         class="text-sm leading-none mr-1 opacity-20 group-hover:opacity-75 group-active:opacity-100" aria-hidden="true" />
                    </div>
                </div>

                <div v-if="data.contact" class="grid grid-flow-col justify-start items-center pb-2">
                    <FontAwesomeIcon  fixed-width icon="fal fa-male" class="mr-4" aria-hidden="true" />
                    {{ data.contact }}
                    <div class="group cursor-pointer px-1.5 flex justify-center text-xl " @click="copyText(data.contact)">
                        <FontAwesomeIcon icon="fal fa-copy"
                                         class="text-sm leading-none mr-1 opacity-20 group-hover:opacity-75 group-active:opacity-100" aria-hidden="true" />
                    </div>
                </div>

                <div class="grid grid-flow-col justify-start items-center">
                    <FontAwesomeIcon  fixed-width icon="fal fa-envelope" class="mr-4" aria-hidden="true" />
                    <a :href="`mailto:${data.email}`" class="hover:text-indigo-500">{{ data.email }}</a>
                    <div class="group cursor-pointer px-1.5 flex justify-center text-xl " @click="copyText(data.email)">
                        <FontAwesomeIcon icon="fal fa-copy"
                                         class="text-sm leading-none mr-1 opacity-20 group-hover:opacity-75 group-active:opacity-100" aria-hidden="true" />
                    </div>
                </div>

                <div class="grid grid-flow-col justify-start items-center">
                    <FontAwesomeIcon  fixed-width icon="fal fa-phone" class="mr-4" aria-hidden="true" />
                    {{ data.phone }}
                    <div class="group cursor-pointer px-1.5 flex justify-center text-xl " @click="copyText(data.phone)">
                        <FontAwesomeIcon icon="fal fa-copy"
                                         class="text-sm leading-none mr-1 opacity-20 group-hover:opacity-75 group-active:opacity-100" aria-hidden="true" />
                    </div>
                </div>
                <div v-if="data.address" class="grid grid-flow-col justify-start items-center pl-0.5">
                    <FontAwesomeIcon  fixed-width icon="fal fa-map-marker-alt" class="mr-4" aria-hidden="true" />
                    <p>
                        <!-- <pre>{{ data.address }}</pre> -->
                        {{ data.address.address_line_1 }} <span v-if="data.address.address_line_2">({{ data.address.address_line_2 }})</span>
                        <br>{{ data.address.locality }}<span v-if="data.address.administrative_area">, {{ data.address.administrative_area }}</span>
                        <br>{{ data.address.country.data.name }}, {{ data.address.postal_code }}
                    </p>
                </div>
            </div>

        </div>
    </div>
</template>

<style lang="scss" scoped></style>
