
<script setup>
import { ref, watch } from 'vue'
import { library } from '@fortawesome/fontawesome-svg-core';
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue'
import Footer from './Components/Footer/index.vue'
import { faHandPointer, faHandRock, faPlus } from '@/../private/pro-solid-svg-icons';
import { fab } from "@fortawesome/free-brands-svg-icons"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { v4 as uuidv4 } from 'uuid';
import HyperlinkTools from './Components/Fields/Hyperlinktools.vue'
import {get} from 'lodash'
import HyperInfoTools from './Components/Fields/InfoFieldTools.vue'
import VueResizable from 'vue-resizable'
import SocialMediaPicker from "./Components/Fields//SocialMediaTools.vue"
library.add(faHandPointer, faHandRock, fab, faPlus)

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
        { name: 'edit', icon: ['fas', 'fa-hand-pointer'] },
        { name: 'grab', icon: ['fas', 'hand-rock'] },
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
    columsToolsLink: [
        { name: 'Add Item', value: 'add' },
    ],
    columsToolsInfo: [
        { name: 'Add Item', value: 'add' },
    ],
}
const selectedTheme = ref(Dummy.theme[0])
const columsTool = ref(null)
const columsTypeTheme = ref(Dummy.columsType[0])
const tool = ref(Dummy.tools[0])

const DummyColums = [
    {
        title: 'add list',
        type: "list",
        id: uuidv4(),
        data: [
            { name: 'dummy', href: '#' },
            { name: 'dummy2', href: '#' },
        ],
    },
    {
        title: 'add description',
        type: "description",
        id: uuidv4(),
        data: "Lorem Ipsum is simply dummy te printernto electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
    },
    {
        title: 'add info',
        type: 'info',
        id: uuidv4(),
        data: [
            {
                title: 'location',
                value: 'Ancient Wisdom s.r.o.',
                icon: 'fas fa-map-marker-alt',
                id: uuidv4(),
            },
            {
                title: 'billingAddress',
                value: 'Billin',
                icon: 'fas fa-map',
                id: uuidv4(),
            },
            {
                title: 'vat',
                value: 'VAT: SK2120525440',
                icon: 'fas fa-balance-scale',
                id: uuidv4(),
            },
            {
                title: 'building',
                value: 'Reg: 50920600',
                icon: 'fas fa-building',
                id: uuidv4(),
            },
            {
                title: 'phone',
                value: '+421 (0)33 558 60 71',
                icon: 'fas fa-phone',
                id: uuidv4(),
            },
            {
                title: 'email',
                value: '<a href=mailto:contact@awgifts.eu>contact@awgifts.eu</a>',
                icon: 'fas fa-envelope',
                id: uuidv4(),
            },
        ],
    }
]

const navigations = ref([
    {
        title: 'company',
        type: "list",
        id: uuidv4(),
        data: [
            { name: 'About', href: '#', id: uuidv4() },
            { name: 'Blog', href: '#', id: uuidv4() },
            { name: 'Jobs', href: '#', id: uuidv4() },
            { name: 'Press', href: '#', id: uuidv4() },
            { name: 'Partners', href: '#', id: uuidv4() },
        ],
    },
    {
        title: 'legal',
        type: "list",
        id: uuidv4(),
        data: [
            { name: 'Claim', href: '#', id: uuidv4() },
            { name: 'Privacy', href: '#', id: uuidv4() },
            { name: 'Terms', href: '#', id: uuidv4() },
        ],
    },
    {
        title: 'Description',
        type: "description",
        id: uuidv4(),
        data: "Lorem Ipsum is simply dummy te printernto electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
    },
    {
        title: 'Info',
        type: 'info',
        id: uuidv4(),
        data: [
            {
                title: 'location',
                value: 'Ancient Wisdom s.r.o.',
                icon: 'fas fa-map-marker-alt',
                id: uuidv4()
            },
            {
                title: 'billingAddress',
                value: 'Billin',
                icon: 'fas fa-map',
                id: uuidv4()
            },
            {
                title: 'vat',
                value: 'VAT: SK2120525440',
                icon: 'fas fa-balance-scale',
                id: uuidv4()
            },
            {
                title: 'building',
                value: 'Reg: 50920600',
                icon: 'fas fa-building',
                id: uuidv4()
            },
            {
                title: 'phone',
                value: '+421 (0)33 558 60 71',
                icon: 'fas fa-phone',
                id: uuidv4()
            },
            {
                title: 'email',
                value: 'contact@awgifts.eu',
                icon: 'fas fa-envelope',
                id: uuidv4(),
            },
        ],
    }

])

const copyRight = ref({ label: 'AW Advantage', href: '*' })

const socials = ref([
    {
        label: "Facebook",
        href: '#facebook',
        icon: 'fab fa-facebook',
        id: uuidv4()
    },
    {
        label: "Instagram",
        href: '#instagram',
        icon: 'fab fa-instagram',
        id: uuidv4()
    },
    {
        label: "Twitter",
        href: '#twitter',
        icon: 'fab fa-twitter',
        id: uuidv4()
    },
    {
        label: "Github",
        href: '#github',
        icon: 'fab fa-github',
        id: uuidv4()
    },
])
const columSelected = ref(navigations.value[0]);

const selectedColums = (value) => {
    columSelected.value = {...value}
}

const handleColumsTypeChange = (value) => {
    if (value.title !== columSelected.value.type) {
        let indexDummy = DummyColums.findIndex((item) => item.type === value.value);
        let indexColums = navigations.value.findIndex((item) => item.id === columSelected.value.id);
        const data = { ...DummyColums[indexDummy], id: uuidv4() }
        navigations.value[indexColums] = data
        selectedColums(data)
    } else { cosnole.log('salah') }

}

const saveItemTitle = (value) => {
    const indexNavigation = navigations.value.findIndex((item) => item.id == value.colum.id)
    const data = { ...navigations.value[indexNavigation], title: value.value }
    navigations.value[indexNavigation] = data
    selectedColums(data)
}

const saveTextArea = (value) => {
    const indexNavigation = navigations.value.findIndex((item) => item.id == value.colum.id)
    const data = { ...navigations.value[indexNavigation], data: value.value }
    navigations.value[indexNavigation] = data
}

const saveLink = (value) => {
    console.log('fsf')
    const indexNavigation = navigations.value.findIndex((item) => item.id == value.parentId)
    const indexChildData = navigations.value[indexNavigation].data.findIndex((item) => item.id == value.colum.id)
    if (value.type !== 'delete') {
        let set = value.type == 'name' ? { name: value.value } : { href: value.value }
        const data = { ...navigations.value[indexNavigation].data[indexChildData], ...set }
        navigations.value[indexNavigation].data[indexChildData] = data
        selectedColums(data)
    } else if (value.type == 'delete') {
        navigations.value[indexNavigation].data.splice(indexChildData, 1)
    } 
}

const saveInfo = (value) => {
    const indexNavigation = navigations.value.findIndex((item) => item.id == value.parentId)
    const indexChildData = navigations.value[indexNavigation].data.findIndex((item) => item.id == value.colum.id)
    let set = value.type == 'value' ? { value: value.value } : { icon: value.value }
    const data = { ...navigations.value[indexNavigation].data[indexChildData], ...set }
    navigations.value[indexNavigation].data[indexChildData] = data
    selectedColums(navigations.value[indexNavigation].data[indexChildData])
}


const columItemLinkChange = (value) => {
    const data = navigations.value
    if (value.value == 'add') {
        const index = data.findIndex((item) => item.id == columSelected.value.id)
        if (columSelected.value.type == 'list') data[index].data.push({ name: 'dummy', href: '#' })
        else if (columSelected.value.type == 'info') data[index].data.push({
            title: 'location',
            value: 'new item',
            icon: 'far fa-dot-circle',
            id: uuidv4(),
        })
    }
    navigations.value = data  
}

const copyRightSave = (value) => {
    let set = value.type == 'name' ? { label: value.value } : { href: value.value }
    copyRight.value = { ...copyRight.value, ...set }
}

const saveSocialmedia = (value) => {
    const index = socials.value.findIndex((item) => item.id == value.colum.id)
    if (value.type == 'save') {
        const data = { ...socials.value[index], ...value.value }
        socials.value[index] = data
    } else if (value.type == 'delete') {
        const data = socials.value
        data.splice(index, 1)
    }

}
const addSocial = () => {
    socials.value.push(
        {
            label: "new",
            href: '#',
            icon: 'far fa-dot-circle',
            id: uuidv4()
        },
    )
}

const EditItemLinkInTools = (value, type) => {
  const data = navigations.value;
  const index = data.findIndex((item) => item.id === columSelected.value.id);
  
  // Check if the item was found in the navigations array
  if (index !== -1) {
    const indexData = data[index].data.findIndex((item) => item.id === value.id);
    
    // Check if the item was found in the data array of the selected navigation item
    if (indexData !== -1) {
      if (type === 'edit') {
        // Update the item with the new value
        data[index].data[indexData] = value;
      } else if (type === 'delete') {
        data[index].data.splice(indexData, 1);
      }
    }
  }
  navigations.value = data;
};




</script>

<template>
    <div class="bg-white">
        <div class="pb-16 pt-6 sm:pb-24">
            <div class="mt-8  px-4 sm:px-6 lg:px-8">
                <div class="flex">
                    <!-- tools -->
                    <div class="w-1/10 p-6  overflow-y-auto overflow-x-hidden" style="border : 1px solid #bfbfbf; height:46rem;">
                        <form>
                            <!-- Color picker -->
                            <div>
                                <h2 class="text-sm font-medium text-gray-900">Tools</h2>
                                <RadioGroup v-model="tool" class="mt-2">
                                    <RadioGroupLabel class="sr-only">Choose a tool</RadioGroupLabel>
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
                            <hr class="mt-5">
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
                            <hr class="mt-5">
                            <!-- theme -->
                            <div class="mt-8">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-sm font-medium text-gray-900">Colums Type</h2>
                                </div>
                                <RadioGroup v-model="columsTypeTheme" class="mt-2">
                                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-2">
                                        <RadioGroupOption as="template" v-for="option in Dummy.columsType"
                                            :key="option.value" :value="option" v-slot="{ active, checked }">
                                            <div :class="{
                                                'cursor-pointer': !columSelected.type == option.value,
                                                'cursor-not-allowed': columSelected.type == option.value,
                                                'bg-gray-300 text-gray-600': columSelected.type == option.value, // Apply different class when disabled
                                                'ring-2 ring-indigo-500 ring-offset-2': active,
                                                'border-transparent bg-indigo-600 text-white hover:bg-indigo-700': checked && !columSelected.type == option.value,
                                                'border-gray-200 bg-white text-gray-900 hover:bg-gray-50': !checked && !columSelected.type == option.value,
                                                'flex items-center justify-center rounded-md border py-3 px-3 text-sm font-medium uppercase sm:flex-1': true
                                            }" @click="handleColumsTypeChange(option)">
                                                <RadioGroupLabel as="span">{{ option.name }}</RadioGroupLabel>
                                            </div>
                                        </RadioGroupOption>
                                    </div>
                                </RadioGroup>
                            </div>
                            <!-- colum tools list -->
                            <div class="mt-8" v-if="get(columSelected,'type') == 'list'">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-sm font-medium text-gray-900">{{ `Colums tools ${columSelected.title}`
                                    }}</h2>
                                </div>
                                <div>
                                    <div class="flex gap-2 mt-2">
                                        <div style="width:87%;"
                                            class=" shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                            <input type="text" v-model="columSelected.title"
                                                class=" flex-1 border-0 bg-transparent text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                                                placeholder="title" />
                                        </div>
                                        <div>

                                            <button type="submit"  @click.prevent="columItemLinkChange({ name: 'Add Item', value: 'add' })"
                                                class="rounded-md cursor-pointer border ring-gray-300 px-3 py-2 text-sm font-semibold text-black shadow-sm ">+</button>
                                        </div>

                                    </div>

                                    <div  v-for="set in columSelected.data" :key="set.id">
                                        <HyperlinkTools :data="set" :save="EditItemLinkInTools" modelLabel="name" modelLink="href"/>
                                    </div>
                                </div>

                            </div>
                            <!-- colum tools info-->
                            <div class="mt-8" v-if="get(columSelected,'type')== 'info'">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-sm font-medium text-gray-900">{{ `Colums tools ${columSelected.title}`
                                    }}</h2>
                                </div>
                                <div>
                                    <div class="flex gap-2 mt-2">
                                        <div style="width:87%;"
                                            class=" shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                            <input type="text" v-model="columSelected.title"
                                                class=" flex-1 border-0 bg-transparent text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                                                placeholder="title" />
                                        </div>
                                        <div>

                                            <button type="submit"  @click.prevent="columItemLinkChange({ name: 'Add Item', value: 'add' })"
                                                class="rounded-md cursor-pointer border ring-gray-300 px-3 py-2 text-sm font-semibold text-black shadow-sm ">+</button>
                                        </div>

                                    </div>

                                    <div  v-for="set in columSelected.data" :key="set.id">
                                        <HyperInfoTools :data="set" :save="EditItemLinkInTools"/>
                                    </div>
                                </div>
                            </div>
                            <hr class="mt-5">
                            <!-- social Media -->
                            <div class="mt-8">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-sm font-medium text-gray-900">{{ `Social media setting` }}</h2>
                                </div>
                                <RadioGroup class="mt-2">
                                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-2">
                                        <RadioGroupOption as="template" v-for="option in socials" :key="option.value"
                                            :value="option" v-slot="{ active, checked }">
                                            <div :class="{
                                                'flex cursor-pointer items-center justify-center rounded-md border py-3 px-3 text-sm font-medium uppercase sm:flex-1': true
                                            }">
                                                <RadioGroupLabel as="span">
                                                	<SocialMediaPicker :modelValue="option.icon" cssClass="h-6 w-6" :data="option" :save="saveSocialmedia" />
                                                </RadioGroupLabel>
                                            </div>
                                        </RadioGroupOption>
                                        <RadioGroupOption as="template">
                                            <div :class="{
                                                'flex cursor-pointer items-center justify-center rounded-md border  border-dashed py-3 px-3 text-sm font-medium uppercase sm:flex-1': true
                                            }" @click="addSocial">
                                                <RadioGroupLabel as="span">
                                                    <FontAwesomeIcon :icon="['fas', 'plus']" />
                                                </RadioGroupLabel>
                                            </div>
                                        </RadioGroupOption>
                                    </div>
                                </RadioGroup>
                            </div>
                        </form>

                        <!-- Product details -->
                    </div>
                    <!-- Image gallery -->
                    <div
                        style="width: 90%; background: #f2f2f2; border : 1px solid #bfbfbf; align-items: center; justify-content: center; display: flex;">
                    <div style="transform: scale(0.8); width:100%;">
                        <!-- <vue-resizable> -->
                        <Footer class="lg:col-span-2 lg:row-span-2 rounded-lg " :columSelected="columSelected"
                            :saveSocialmedia="saveSocialmedia" :theme="selectedTheme.value" :social="socials"
                            :saveInfo="saveInfo" :copyRight="copyRight" :copyRightSave="copyRightSave"
                            :navigation="navigations" :selectedColums="selectedColums" :saveItemTitle="saveItemTitle"
                            :saveTextArea="saveTextArea" :tool="tool" :saveLink="saveLink" />
                            <!-- </vue-resizable> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div></template>