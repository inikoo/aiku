<script setup lang="ts">
import { ref } from 'vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import PureMultiselect from '@/Components/Pure/PureMultiselect.vue'
import { cloneDeep } from 'lodash'
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import Popover from '@/Components/Popover.vue'
import PureInput from '@/Components/Pure/PureInput.vue'

import { library } from "@fortawesome/fontawesome-svg-core"
import { faShieldAlt, faTimes } from "@fas"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn } from "@fortawesome/free-brands-svg-icons";

library.add(faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn, faShieldAlt, faTimes)

const props = defineProps<{
    modelValue: any,
}>();

const emits = defineEmits<{
    (e: 'update:modelValue', value: {}): void
}>();


const icons = [
    { 
			label: "Facebook",
			value: ['fab', 'facebook-f'],
	},
    { 
			label: "Instagram",
			value: "fab fa-instagram",
	},
    { 
			label: "Tik Tok",
			value: "fab fa-tiktok",
	},
    { 
			label: "Pinterest",
			value: "fab fa-pinterest",
	},
    { 
			label: "Youtube",
			value: "fab fa-youtube",
	},
    { 
			label: "Linkedin",
			value: "fab fa-linkedin-in",
	},

];

const addPayments = () => {
    let data = cloneDeep(props.modelValue);
    data.push(
        { 
			label: "Facebook",
			icon : ['fab', 'facebook-f'],
			link : ""
		}
    );
    emits('update:modelValue',  data );
};


const changeIcon = (icon, data, index) => {
    let set = cloneDeep(props.modelValue);
    set[index] = {
        label: icon.label,
        icon: icon.value,
        link: data.link
    }
    emits('update:modelValue',set);
}

const deleteSocial = (event,index) => {
    event.stopPropagation();
    event.preventDefault();
    let set = cloneDeep(props.modelValue);
    set.splice(index,1)
    emits('update:modelValue',set);
}

</script>

<template>
    <div>
        <div v-for="(item, index) of modelValue" :key="index" class="p-1">
            <Disclosure v-slot="{ open }">
                <DisclosureButton :class="!open ? 'rounded-lg' : 'rounded-t-lg'"
                    class="flex w-full justify-between bg-slate-200  px-4 py-2 text-left text-sm font-medium  hover:bg-purple-200 focus:outline-none focus-visible:ring focus-visible:ring-purple-500/75">
                    <span class="font-medium text-sm">{{ item.label }}</span>
                    <FontAwesomeIcon :icon="['fas', 'times']" class="text-red-500 p-1" @click="(e)=>deleteSocial(e,index)"/>
                </DisclosureButton>
                <DisclosurePanel class="px-4 pb-2 pt-4 text-sm text-gray-500 bg-slate-100 rounded-b-lg">
                    <div>
                        <div class="p-1">
                            <span class="text-xs  my-2"> Icon : </span>
                            <Popover class="relative h-full" width="w-[350px]">
                                <template #button>
                                            <Button type="dashed" :full="true" ><FontAwesomeIcon :icon="item.icon"></FontAwesomeIcon></Button>
                                </template>
                                <template #content="{ close: closed }">
                                    <div class="grid grid-cols-3 gap-6 p-1">
                                        <div v-for="icon in icons" :key="icon.label" @click="()=>changeIcon(icon,item,index)" class="cursor-pointer flex flex-col items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-100 transition duration-200">
                                            <FontAwesomeIcon :icon="icon.value" class="text-xl mb-2"></FontAwesomeIcon>
                                            <span class="text-xs font-medium text-gray-700">{{ icon.label }}</span>
                                        </div>
                                    </div>
                                </template>
                            </Popover>
                        </div>

                        <div class="p-1">
                            <span class="text-xs  my-2"> Label : </span>
                            <PureInput v-model="item.label" placeholder="Label" />
                        </div>

                        <div class="p-1">
                            <span class="text-xs  my-2">Link : </span>
                            <PureInput v-model="item.link" placeholder="Link" />
                        </div>
                    </div>
                </DisclosurePanel>
            </Disclosure>

        </div>
        <Button type="dashed" icon="fal fa-plus" label="Add Payments Method" full size="s" class="mt-2"
            @click="addPayments" />
    </div>
</template>

<style scoped></style>
