
<script setup>
import { ref } from 'vue'
import { library } from '@fortawesome/fontawesome-svg-core';
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue'
import Footer from './Components/Footer/index.vue'
import { faHandPointer, faHandRock } from '@/../private/pro-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { v4 as uuidv4 } from 'uuid';
library.add(faHandPointer, faHandRock)
const Dummy = {
    images: [
        {
            id: 1,
            imageSrc: 'https://tailwindui.com/img/ecommerce-images/product-page-01-featured-product-shot.jpg',
            imageAlt: "Back of women's Basic Tee in black.",
            primary: true,
        },
    ],
    tools: [
        { name: 'edit', icon: ['fas', 'fa-hand-pointer']},
        { name: 'grab', icon: ['fas',  'hand-rock']},
        // { name: 'Heather Grey', icon: ['fas', 'fa-hand-pointer']},
    ],
    theme: [
        { name: 'Light', value: '2' },
        { name: 'Dark', value: '1' },
        { name: 'Simple', value: '3' },
    ],
    columsType: [
        { name: 'Description', value: 'description' },
        { name: 'List', value: 'list' },
        { name: 'Info', value: 'info' },
    ],
}
const selectedTheme = ref(Dummy.theme[0])
const columsTypeTheme = ref(Dummy.columsType[2])
const tool = ref(Dummy.tools[0])

const DummyColums = [
    {
        title: 'add list',
        type: "list",
        id : uuidv4(),
        data: [
            { name: 'dummy', href: '#' },
            { name: 'dummy2', href: '#' },
        ],
    },
    {
        title: 'add description',
        type: "description",
        id : uuidv4(),
        data: "Lorem Ipsum is simply dummy te printernto electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
    },
    {
        title: 'add info',
        type: 'info',
        id : uuidv4(),
        data: [
            {
                title: 'location',
                value: 'Ancient Wisdom s.r.o.',
                icon: 'fas fa-map-marker-alt',
                id : uuidv4(),
            },
            {
                title: 'billingAddress',
                value: 'Billin',
                icon: 'fas fa-map',
                id : uuidv4(),
            },
            {
                title: 'vat',
                value: 'VAT: SK2120525440',
                icon: 'fas fa-balance-scale',
                id : uuidv4(),
            },
            {
                title: 'building',
                value: 'Reg: 50920600',
                icon: 'fas fa-building',
                id : uuidv4(),
            },
            {
                title: 'phone',
                value: '+421 (0)33 558 60 71',
                icon: 'fas fa-phone',
                id : uuidv4(),
            },
            {
                title: 'email',
                value: '<a href=mailto:contact@awgifts.eu>contact@awgifts.eu</a>',
                icon: 'fas fa-envelope',
                id : uuidv4(),
            },
        ],
    }
]

const navigations = ref([
    {
        title: 'company',
        type: "list",
        id : uuidv4(),
        data: [
            { name: 'About', href: '#',  id : uuidv4() },
            { name: 'Blog', href: '#' ,  id : uuidv4()},
            { name: 'Jobs', href: '#' ,  id : uuidv4()},
            { name: 'Press', href: '#',  id : uuidv4() },
            { name: 'Partners', href: '#' ,  id : uuidv4()},
        ],
    },
    {
        title: 'legal',
        type: "list",
        id : uuidv4(),
        data: [
            { name: 'Claim', href: '#' ,  id : uuidv4()},
            { name: 'Privacy', href: '#' , id : uuidv4()},
            { name: 'Terms', href: '#' , id : uuidv4()},
        ],
    },
    {
        title: 'Description',
        type: "description",
        id : uuidv4(),
        data: "Lorem Ipsum is simply dummy te printernto electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
    },
    {
        title: 'Info',
        type: 'info',
        id : uuidv4(),
        data: [
            {
                title: 'location',
                value: 'Ancient Wisdom s.r.o.',
                icon: 'fas fa-map-marker-alt',
                id : uuidv4()
            },
            {
                title: 'billingAddress',
                value: 'Billin',
                icon: 'fas fa-map',
                id : uuidv4()
            },
            {
                title: 'vat',
                value: 'VAT: SK2120525440',
                icon: 'fas fa-balance-scale',
                id : uuidv4()
            },
            {
                title: 'building',
                value: 'Reg: 50920600',
                icon: 'fas fa-building',
                id : uuidv4()
            },
            {
                title: 'phone',
                value: '+421 (0)33 558 60 71',
                icon: 'fas fa-phone',
                id : uuidv4()
            },
            {
                title: 'email',
                value: '<a href=mailto:contact@awgifts.eu>contact@awgifts.eu</a>',
                icon: 'fas fa-envelope',
                id : uuidv4(),
            },
        ],
    }

])

const socials = [
    {
        title: "Facebook",
        href: '#facebook',
        icon: ['fab', 'fa-facebook'],
    },
    {
        title: "Instagram",
        href: '#instagram',
        icon: ['fab', 'fa-instagram'],
    },
    {
        title: "Twitter",
        href: '#twitter',
        icon: ['fab', 'fa-twitter'],
    },
    {
        title: "Github",
        href: '#github',
        icon: ['fab', 'fa-github'],
    },
]
const columSelected = ref(navigations.value[0]);
const selectedColums = (value) => {
    columSelected.value = value
}


const handleColumsTypeChange = (value) => {
    if(value.title !== columSelected.value.type){
        let indexDummy = DummyColums.findIndex((item) => item.type === value.value);
    let indexColums = navigations.value.findIndex((item) => item.id === columSelected.value.id);
    const data = {...DummyColums[indexDummy], id : uuidv4()}
    navigations.value[indexColums] = data
    }else{ cosnole.log('salah')}
   
}


const saveItemTitle = (value) =>{
   const indexNavigation = navigations.value.findIndex((item)=>item.id == value.colum.id )
   const data = {...navigations.value[indexNavigation], title : value.value}
   navigations.value[indexNavigation] = data
}

const saveTextArea = (value) =>{
   const indexNavigation = navigations.value.findIndex((item)=>item.id == value.colum.id )
   const data = {...navigations.value[indexNavigation], data : value.value}
   navigations.value[indexNavigation] = data
}

const saveLink = (value) =>{
    const indexNavigation = navigations.value.findIndex((item)=>item.id == value.parentId )
    const indexChildData = navigations.value[indexNavigation].data.findIndex((item)=>item.id == value.colum.id )
    let set = value.type == 'name' ? { name : value.value } : { href : value.value }
    const data = {...navigations.value[indexNavigation].data[indexChildData] ,...set}
    navigations.value[indexNavigation].data[indexChildData] = data
    
}

</script>

<template>
    <div class="bg-white">
        <div class="pb-16 pt-6 sm:pb-24">
            <div class="mt-8  px-4 sm:px-6 lg:px-8">
                <div class="flex">
                    <!-- tools -->
                    <div class="w-1/10 p-6" style="border : 1px solid #bfbfbf">
                        <form>
                            <!-- Color picker -->
                            <div>
                                <h2 class="text-sm font-medium text-gray-900">Tools</h2>

                                <RadioGroup v-model="tool" class="mt-2">
                                    <RadioGroupLabel class="sr-only">Choose a color</RadioGroupLabel>
                                    <div class="flex items-center space-x-3">
                                        <RadioGroupOption as="template" v-for="color in Dummy.tools" :key="color.name"
                                            :value="color" v-slot="{ active, checked }">
                                            <div
                                                :class="[color.tools, active && checked ? 'ring ring-offset-1' : '', !active && checked ? 'ring-2' : '', 'relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none']">
                                                <RadioGroupLabel as="span" class="sr-only">{{ color.name }}
                                                </RadioGroupLabel>
                                                <span aria-hidden="true" class="flex items-center justify-center">
                                                    <span
                                                        class=" h-8 w-8 rounded-full border border-black border-opacity-10 flex items-center justify-center">
                                                        <span style="line-height: 1">
                                                            <FontAwesomeIcon :icon="color.icon" aria-hidden="true" />
                                                        </span>
                                                    </span>
                                                </span>
                                            </div>
                                        </RadioGroupOption>
                                    </div>
                                </RadioGroup>
                            </div>

                            <!-- Size picker -->
                            <div class="mt-8">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-sm font-medium text-gray-900">Theme</h2>
                                </div>

                                <RadioGroup v-model="selectedTheme" class="mt-2">
                                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-2">
                                        <RadioGroupOption as="template" v-for="theme in Dummy.theme" :key="theme.name"
                                            :value="theme" v-slot="{ active, checked }">
                                            <div
                                                :class="['cursor-pointer focus:outline-none', active ? 'ring-2 ring-indigo-500 ring-offset-2' : '', checked ? 'border-transparent bg-indigo-600 text-white hover:bg-indigo-700' : 'border-gray-200 bg-white text-gray-900 hover:bg-gray-50', 'flex items-center justify-center rounded-md border py-3 px-3 text-sm font-medium uppercase sm:flex-1']">
                                                <RadioGroupLabel as="span">{{ theme.name }}</RadioGroupLabel>
                                            </div>
                                        </RadioGroupOption>
                                    </div>
                                </RadioGroup>


                            </div>

                            <div class="mt-8">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-sm font-medium text-gray-900">Colums Type</h2>
                                </div>
                                <RadioGroup v-model="columsTypeTheme" class="mt-2"
                                    @update:modelValue="handleColumsTypeChange">
                                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-2">
                                        <RadioGroupOption as="template" v-for="option in Dummy.columsType" :key="option.value"
                                            :value="option" v-slot="{ active, checked }">
                                            <div :class="{
                                                'cursor-pointer': !columSelected.type == option.value,
                                                'cursor-not-allowed': columSelected.type == option.value,
                                                'bg-gray-300 text-gray-600': columSelected.type == option.value, // Apply different class when disabled
                                                'ring-2 ring-indigo-500 ring-offset-2': active,
                                                'border-transparent bg-indigo-600 text-white hover:bg-indigo-700': checked && !columSelected.type == option.value,
                                                'border-gray-200 bg-white text-gray-900 hover:bg-gray-50': !checked && !columSelected.type == option.value,
                                                'flex items-center justify-center rounded-md border py-3 px-3 text-sm font-medium uppercase sm:flex-1': true
                                            }">
                                                <RadioGroupLabel as="span">{{ option.name }}</RadioGroupLabel>
                                            </div>
                                        </RadioGroupOption>
                                    </div>
                                </RadioGroup>
                            </div>
                        </form>

                        <!-- Product details -->
                    </div>
                    <!-- Image gallery -->
                    <div style="width: 90%; background: #f2f2f2; border : 1px solid #bfbfbf;">
                        <div style="transform: scale(0.8);">
                            <Footer class="lg:col-span-2 lg:row-span-2 rounded-lg " :columSelected="columSelected"
                                :theme="selectedTheme.value" :social="socials"
                                :navigation="navigations" :selectedColums="selectedColums" :saveItemTitle="saveItemTitle" :saveTextArea="saveTextArea" :tool="tool" :saveLink="saveLink"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>