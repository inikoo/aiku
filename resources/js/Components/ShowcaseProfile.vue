
<script setup lang="ts">
import {  } from '../../../resources/private/pro-solid-svg-icons';
import { faEnvelope, faPhone, faMapMarkerAlt, faCopy, faPersonDolly, faBoxFull, faBan, faArrowUp } from '../../../resources/private/pro-light-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

library.add(faEnvelope, faPhone, faPersonDolly, faBoxFull, faCopy, faBan, faArrowUp, faMapMarkerAlt);

const props = defineProps<{
    data,
}>()

console.log(props.data)

const copyText = (text: string) => {
    const textarea = document.createElement('textarea')
    textarea.value = text
    document.body.appendChild(textarea)
    textarea.select()
    document.execCommand('copy')
    textarea.remove()
}

</script>

<template>
    <div class="grid grid-flow-col w-fit">
        <div class="relative rounded-md h-40 w-40 shadow overflow-hidden grid justify-center m-2">
            <img class="object-fit" src="https://source.unsplash.com/featured/300x300" alt="">
            <div class="absolute bottom-0 w-full h-2/6 bg-gradient-to-t from-gray-900 from-20% "></div>
            <div class="absolute bottom-1.5 left-2 font-semibold text-white">{{ data.contact_name }}</div>
        </div>
        <div class="pl-3 pt-3">
            <div class="grid grid-flow-col  space-x-1 w-full">
                <div class="flex items-center">
                    <p class="inline">{{ data.company_name }}
                        <span class="group cursor-pointer pl-0.5 pr-1.5 inline justify-center text-xl "
                            @click="copyText(data.company_name)">
                            <FontAwesomeIcon icon="fal fa-copy" class="text-sm  mr-1 opacity-30 group-hover:opacity-75 group-active:opacity-100"
                                aria-hidden="true" />
                        </span>
                    </p>
                </div>
            </div>

            <!-- Contact Section -->
            <div class="pt-4 flex flex-col text-sm pb-2 space-y-1">
                <div class="grid grid-flow-col justify-start items-center pl-0.5">
                    <FontAwesomeIcon icon="fal fa-map-marker-alt" class="mr-2" aria-hidden="true" />
                    {{ data.location[2] }}, {{ data.location[1] }}
                </div>
                <div class="grid grid-flow-col justify-start items-center">
                    <FontAwesomeIcon icon="fal fa-envelope" class="mr-2" aria-hidden="true" />
                    <a :href="`mailto:${data.email}`" class="hover:text-indigo-500">{{ data.email }}</a>
                    <div class="group cursor-pointer px-1.5 flex justify-center text-xl " @click="copyText(data.email)">
                        <FontAwesomeIcon icon="fal fa-copy"
                            class="text-sm leading-none mr-1 opacity-30 group-hover:opacity-75 group-active:opacity-100" aria-hidden="true" />
                    </div>
                </div>
                <div class="grid grid-flow-col justify-start items-center">
                    <FontAwesomeIcon icon="fal fa-phone" class="mr-2" aria-hidden="true" />
                    {{ data.phone }}
                    <div class="group cursor-pointer px-1.5 flex justify-center text-xl " @click="copyText(data.phone)">
                        <FontAwesomeIcon icon="fal fa-copy"
                            class="text-sm leading-none mr-1 opacity-30 group-hover:opacity-75 group-active:opacity-100" aria-hidden="true" />
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<style lang="scss" scoped></style>