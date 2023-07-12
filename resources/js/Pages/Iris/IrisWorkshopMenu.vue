<script setup>
import { ref , watch } from 'vue'
import { library } from '@fortawesome/fontawesome-svg-core';
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue'
import Menu from './Components/Menu/index.vue'
import { faHandPointer, faHandRock, faPlus } from '@/../private/pro-solid-svg-icons';
import { fab } from "@fortawesome/free-brands-svg-icons"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { v4 as uuidv4 } from 'uuid';
import HyperlinkTools from './Components/Fields/Hyperlinktools.vue'
import { get } from 'lodash'
import HyperInfoTools from './Components/Fields/InfoFieldTools.vue'
import VueResizable from 'vue-resizable'
import SocialMediaPicker from "./Components/Fields//SocialMediaTools.vue"
library.add(faHandPointer, faHandRock, fab, faPlus)

const Dummy = {
    tools: [
        { name: 'edit', icon: ['fas', 'fa-hand-pointer'] },
        { name: 'grab', icon: ['fas', 'hand-rock'] },
        // { name: 'Heather Grey', icon: ['fas', 'fa-hand-pointer']},
    ],
    theme: [
        { name: 'One', value: '2' },
        { name: 'Two', value: '1' },
    ],
    menuType: [
        { name: 'Group', value: 'group' },
        { name: 'Link', value: 'link' },
    ],
    modeType: [
        { name: 'User', value: 'user' },
        { name: 'Guest', value: 'guest' },
    ],
}
const navigation = ref({
    categories: [
        {
            name: 'Home',
            id: uuidv4(),
            type: 'group',
            featured: [
                {
                    name: 'About us',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Contact',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'ShowRoom',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Trems & Conditions',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Delivery',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Operation Hours',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Freedom Fund',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Business Ethics',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Catalogue',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Retruns Policy',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Dropshiping Sevices',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Working with local businesses',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'sustainable palm oil',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Privacy Policy',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Cookies Policy',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Travel Blog',
                    id: uuidv4(),
                    link: '#',
                },
            ],
        },
        {
            name: 'Departements',
            id: uuidv4(),
            type: 'group',
            featured: [
                {
                    name: 'New Arrivals',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Basic Tees',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Accessories',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Carry',
                    id: uuidv4(),
                    link: '#',
                },
            ],
        },
        {
            name: 'Incentives & Inspiration',
            id: uuidv4(),
            type: 'group',
            featured: [
                {
                    name: 'New Arrivals',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Basic Tees',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Accessories',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Carry',
                    id: uuidv4(),
                    link: '#',
                },
            ],
        },
        {
            name: 'Delivery',
            id: uuidv4(),
            type: 'group',
            featured: [
                {
                    name: 'New Arrivals',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Basic Tees',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Accessories',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Carry',
                    id: uuidv4(),
                    link: '#',
                },
            ],
        },
        {
            name: 'New & Notetable',
            id: uuidv4(),
            type: 'group',
            featured: [
                {
                    name: 'New Arrivals',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Basic Tees',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Accessories',
                    id: uuidv4(),
                    link: '#',
                },
                {
                    name: 'Carry',
                    id: uuidv4(),
                    link: '#',
                },
            ],
        },
        {
            name: 'Test', id: uuidv4(),
            type: 'link', link: '#',
        },
    ],
})

const selectedTheme = ref(Dummy.theme[0])
const columsTypeTheme = ref(null)
const handtools = ref(Dummy.tools[0])
const selectedNav = ref(null)

watch(selectedNav, (newValue) => {
    console.log(newValue)
    selectedNav.value = {...newValue}
})

const saveNav = (value) => {
        const index = navigation.value.categories.findIndex((item) => item.id == value.colum.id)
        if (value.type == 'name') navigation.value.categories[index] = { ...navigation.value.categories[index], name: value.value }
        if (value.type == 'link') navigation.value.categories[index] = { ...navigation.value.categories[index], link: value.value }
        if (value.type == 'delete') navigation.value.categories.splice(index,1)
}

const saveSubMenu = (value) => {
    const index = navigation.value.categories.findIndex((item) => item.id == value.parentId)
    const indexSubMenu = navigation.value.categories[index].featured.findIndex((item) => item.id == value.colum.id)
    if (value.type == 'name') navigation.value.categories[index].featured[indexSubMenu] = { ...navigation.value.categories[index].featured[indexSubMenu], name: value.value }
    if (value.type == 'link') navigation.value.categories[index].featured[indexSubMenu] = { ...navigation.value.categories[index].featured[indexSubMenu], link: value.value }
    if (value.type == 'delete') navigation.value.categories[index].featured.splice(indexSubMenu, 1)
}

const changeMenuType = (value) => {
    const index = navigation.value.categories.findIndex(
        (item) => item.id === selectedNav.value.id
    );

    if (value.value === 'link' && selectedNav.value.type !== 'link') {
        navigation.value.categories[index] = {
            ...navigation.value.categories[index],
            type: 'link',
            link: '',
        };
    }

    if (value.value === 'group' && selectedNav.value.type !== 'group') {
        navigation.value.categories[index] = {
            ...navigation.value.categories[index],
            type: 'group',
            featured: [
                {
                    name: 'New item',
                    id: uuidv4(),
                    link: '#',
                },
            ],
        };
    }
};


const changeNavActive = (value) => {
    selectedNav.value = value
}

const columItemLinkChange = () => {
  const index = navigation.value.categories.findIndex((item) => item.id === selectedNav.value.id);
  navigation.value.categories[index].featured.push({ name: 'New', id: uuidv4(), link: '' });
};

const EditItemLinkInTools = (value,type) => {
    const index = navigation.value.categories.findIndex((item) => item.id === selectedNav.value.id);
    const indexSub =  navigation.value.categories[index].featured.findIndex((item) => item.id === value.id);
    if(type == 'edit') navigation.value.categories[index].featured[indexSub] = value
    if(type == 'delete') navigation.value.categories[index].featured.splice(indexSub,1)
}


</script>

<template>
    <div class="bg-white">
        <div class="pb-16 pt-6 sm:pb-24">
            <div class="mt-8 px-4 sm:px-6 lg:px-8">
                <div class="flex">
                    <!-- tools -->
                    <div class="w-1/4 p-6 overflow-y-auto overflow-x-hidden"
                        style="border: 1px solid #bfbfbf; height: 46rem">
                        <form>
                            <!-- Color picker -->
                            <div>
                                <h2 class="text-sm font-medium text-gray-900">Tools</h2>
                                <RadioGroup v-model="handtools" class="mt-2">
                                    <RadioGroupLabel class="sr-only">Choose a tool</RadioGroupLabel>
                                    <div class="flex items-center space-x-3">
                                        <RadioGroupOption as="template" v-for="color in Dummy.tools" :key="color.name"
                                            :value="color" v-slot="{ active, checked }">
                                            <div :class="[
                                                color.tools,
                                                active && checked ? 'ring ring-offset-1' : '',
                                                !active && checked ? 'ring-2' : '',
                                                'relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none',
                                            ]">
                                                <RadioGroupLabel as="span" class="sr-only">{{ color.name }}
                                                </RadioGroupLabel>
                                                <span aria-hidden="true" class="flex items-center justify-center">
                                                    <span
                                                        class="h-8 w-8 rounded-full border border-black border-opacity-10 flex items-center justify-center">
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
                            <hr class="mt-5" />
                            <!-- Size picker -->
                            <div class="mt-8">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-sm font-medium text-gray-900">Theme</h2>
                                </div>

                                <RadioGroup v-model="selectedTheme" class="mt-2">
                                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-2">
                                        <RadioGroupOption as="template" v-for="theme in Dummy.theme" :key="theme.name"
                                            :value="theme" v-slot="{ active, checked }">
                                            <div :class="[
                                                'cursor-pointer focus:outline-none',
                                                active
                                                    ? 'ring-2 ring-indigo-500 ring-offset-2'
                                                    : '',
                                                checked
                                                    ? 'border-transparent bg-indigo-600 text-white hover:bg-indigo-700'
                                                    : 'border-gray-200 bg-white text-gray-900 hover:bg-gray-50',
                                                'flex items-center justify-center rounded-md border py-3 px-3 text-sm font-medium uppercase sm:flex-1',
                                            ]">
                                                <RadioGroupLabel as="span">{{
                                                    theme.name
                                                }}</RadioGroupLabel>
                                            </div>
                                        </RadioGroupOption>
                                    </div>
                                </RadioGroup>
                            </div>
                            <hr class="mt-5" />
                            <!-- theme -->

                            <div class="mt-8" v-if="selectedNav !== null">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-sm font-medium text-gray-900">Menu Type</h2>
                                </div>
                                <RadioGroup class="mt-2">
                                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-2">
                                        <RadioGroupOption as="template" v-for="option in Dummy.menuType" :key="option.value"
                                            :value="option" v-slot="{ active, checked }">
                                            <div @click="changeMenuType(option)" :class="{
                                                'cursor-pointer': get(selectedNav, 'type') === option.value && selectedNav !== null,
                                                'cursor-not-allowed': get(selectedNav, 'type') !== option.value && selectedNav !== null,
                                                'bg-gray-300 text-gray-600': get(selectedNav, 'type') === option.value && selectedNav !== null, // Apply different class when disabled
                                                'ring-2 ring-indigo-500 ring-offset-2': active,
                                                'border-transparent bg-indigo-600 text-white hover:bg-indigo-700': checked && get(selectedNav, 'type') !== option.value && selectedNav !== null,
                                                'border-gray-200 bg-white text-gray-900 hover:bg-gray-50': !checked && get(selectedNav, 'type') !== option.value && selectedNav !== null,
                                                'flex items-center justify-center rounded-md border py-3 px-3 text-sm font-medium uppercase sm:flex-1': true
                                            }">
                                                <RadioGroupLabel as="span">{{ option.name }}</RadioGroupLabel>
                                            </div>
                                        </RadioGroupOption>

                                    </div>
                                </RadioGroup>
                            </div>
                            <!-- Mode -->
                            <div class="mt-8" v-if="get(selectedNav,'type') == 'group'">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-sm font-medium text-gray-900">{{ `Colums tools ${selectedNav.name}`
                                    }}</h2>
                                </div>
                                <div>
                                    <div class="flex gap-2 mt-2">
                                        <div style="width:87%;"
                                            class=" shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                            <input type="text" v-model="selectedNav.name"
                                                class=" flex-1 border-0 bg-transparent text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                                                placeholder="title" />
                                        </div>
                                        <div>

                                            <button type="submit"  @click.prevent="columItemLinkChange()"
                                                class="rounded-md cursor-pointer border ring-gray-300 px-3 py-2 text-sm font-semibold text-black shadow-sm ">+</button>
                                        </div>

                                    </div>

                                    <div  v-for="set in selectedNav.featured" :key="set.id">
                                        <HyperlinkTools :data="set" :save="EditItemLinkInTools" modelLabel="name" modelLink="link"/>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                    <!-- Image gallery -->
                    <div style="
							width: 90%;
							background: #f2f2f2;
							border: 1px solid #bfbfbf;
							
						">
                        <div style="transform: scale(0.8); width: 100%">
                            <Menu :theme="selectedTheme.value" :navigation="navigation" :saveNav="saveNav"
                                :saveSubMenu="saveSubMenu" :tool="handtools" :selectedNav="selectedNav"
                                :changeNavActive="changeNavActive" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
