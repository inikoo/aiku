<script setup>
import { ref } from 'vue';
import {
  Dialog,
  DialogPanel,
  Popover,
  PopoverButton,
  PopoverGroup,
  PopoverPanel,
  Tab,
  TabGroup,
  TabList,
  TabPanel,
  TabPanels,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'
import { router } from '@inertiajs/vue3'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faBars, faMagnifyingGlass } from "@fortawesome/free-solid-svg-icons";
import { library } from "@fortawesome/fontawesome-svg-core";
import { trans } from 'laravel-vue-i18n';
import { Link, } from '@inertiajs/vue3';
import { usePage } from "@inertiajs/vue3";

library.add(faBars, faMagnifyingGlass);
const navigation = {
  categories: [
    {
      name: 'Home',
      featured: [
        {
          name: 'About us',
          href: '#',
        },
        {
          name: 'Contact',
          href: '#',
        },
        {
          name: 'ShowRoom',
          href: '#',
        },
        {
          name: 'Trems & Conditions',
          href: '#',
        },
        {
          name: 'Delivery',
          href: '#',
        },
        {
          name: 'Operation Hours',
          href: '#',
        },
        {
          name: 'Freedom Fund',
          href: '#',
        },
        {
          name: 'Business Ethics',
          href: '#',
        },
        {
          name: 'Catalogue',
          href: '#',
        },
        {
          name: 'Retruns Policy',
          href: '#',
        },
        {
          name: 'Dropshiping Sevices',
          href: '#',
        },
        {
          name: 'Working with local businesses',
          href: '#',
        },
        {
          name: 'sustainable palm oil',
          href: '#',
        },
        {
          name: 'Privacy Policy',
          href: '#',
        },
        {
          name: 'Cookies Policy',
          href: '#',
        },
        {
          name: 'Travel Blog',
          href: '#',
        },
      ],
    },
    {
      name: 'Departements',
      featured: [
        {
          name: 'New Arrivals',
          href: '#',
        },
        {
          name: 'Basic Tees',
          href: '#',
        },
        {
          name: 'Accessories',
          href: '#',
        },
        {
          name: 'Carry',
          href: '#',
        },
      ],
    },
    {
      name: 'Incentives & Inspiration',
      featured: [
        {
          name: 'New Arrivals',
          href: '#',
        },
        {
          name: 'Basic Tees',
          href: '#',
        },
        {
          name: 'Accessories',
          href: '#',
        },
        {
          name: 'Carry',
          href: '#',
        },
      ],
    },
    {
      name: 'Delivery',
      featured: [
        {
          name: 'New Arrivals',
          href: '#',
        },
        {
          name: 'Basic Tees',
          href: '#',
        },
        {
          name: 'Accessories',
          href: '#',
        },
        {
          name: 'Carry',
          href: '#',
        },
      ],
    },
    {
      name: 'New & Notetable',
      featured: [
        {
          name: 'New Arrivals',
          href: '#',
        },
        {
          name: 'Basic Tees',
          href: '#',
        },
        {
          name: 'Accessories',
          href: '#',
        },
        {
          name: 'Carry',
          href: '#',
        },
      ],
    },
  ],
}


const mobileMenuOpen = ref(false)

const user = ref(usePage().props.auth.user);

router.on('success', (event) => {
  user.value = usePage().props.auth.user;
})

console.log('dddd', usePage().props.auth.user)


</script>
<template>
  <!-- Mobile -->
  <TransitionRoot as="template" :show="mobileMenuOpen">
    <Dialog as="div" class="relative z-40 lg:hidden" @close="mobileMenuOpen = false">
      <TransitionChild as="template" enter="transition-opacity ease-linear duration-300" enter-from="opacity-0"
        enter-to="opacity-100" leave="transition-opacity ease-linear duration-300" leave-from="opacity-100"
        leave-to="opacity-0">
        <div class="fixed inset-0 bg-black bg-opacity-25" />
      </TransitionChild>

      <div class="fixed inset-0 z-40 flex">
        <TransitionChild as="template" enter="transition ease-in-out duration-300 transform"
          enter-from="-translate-x-full" enter-to="translate-x-0" leave="transition ease-in-out duration-300 transform"
          leave-from="translate-x-0" leave-to="-translate-x-full">
          <DialogPanel class="relative flex w-full max-w-xs flex-col overflow-y-auto bg-white pb-12 shadow-xl">
            <div class="flex px-4 pb-2 pt-5">
              <button type="button" class="-m-2 inline-flex items-center justify-center rounded-md p-2 text-gray-400"
                @click="mobileMenuOpen = false">
                <span class="sr-only">Close menu</span>
                <XMarkIcon class="h-6 w-6" aria-hidden="true" />
              </button>
            </div>
            <TabGroup as="div" class="mt-2">
              <div class="border-b border-gray">
                <TabList class="-mb-px flex space-x-8 px-4 w-304 overflow-x-auto">
                  <Tab as="template" v-for="category in navigation.categories" :key="category.name" v-slot="{ selected }">
                    <button
                      :class="[selected ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-900', 'flex-1 whitespace-nowrap border-b-2 px-1 py-4 text-base font-medium']">{{
                        category.name }}</button>
                  </Tab>
                </TabList>
              </div>
              <TabPanels as="template">
                <TabPanel v-for="category in navigation.categories" :key="category.name" class="space-y-12 px-4 py-6">
                  <div class="grid grid-cols-1 gap-x-4">
                    <div v-for="item in category.featured" :key="item.name" class="group relative">

                      <a :href="item.href" class="mt-6 block text-sm font-medium text-gray-900">
                        <span class="absolute inset-0 z-10" aria-hidden="true" />
                        {{ item.name }}
                      </a>
                    </div>
                  </div>
                </TabPanel>
              </TabPanels>
            </TabGroup>


            <div class="space-y-6 border-t border-gray-200 px-4 py-6">
              <div class="flow-root" v-if="user == null">
                <Link :href="route('login')" class="-m-2 block p-2 font-medium text-gray-900">{{ trans('Login') }}</Link>
              </div>
              <div class="flow-root" v-if="user == null">
                <Link :href="route('register')" class="-m-2 block p-2 font-medium text-gray-900">{{ trans('Register') }}
                </Link>
              </div>
              <div class="flow-root" v-if="user">
                <Link method="post" :href="route('logout')" class="-m-2 block p-2 font-medium text-gray-900">
                {{ trans('Logout') }}</Link>
              </div>
            </div>


          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>

  <!-- Desktop -->
  <div class="relative bg-gray-900">
    <div aria-hidden="true" class="absolute inset-0 bg-gray-900 opacity-50" />

    <header class="relative z-10">
      <nav aria-label="Top">
        <div class="bg-gray-900">
          <div class="mx-auto flex h-10  px-4 sm:px-6 lg:px-8">

            <div class="w-2/4">
              <div class="hidden lg:flex lg:flex-1 lg:items-center justify-end ">
                <a href="#">
                  <div class="flex"><img class="h-8 w-auto" src="https://tailwindui.com/img/logos/mark.svg?color=white"
                      alt="" /><span class="p-1 text-2xl font-semibold text-white">AW GIFT</span></div>
                </a>
              </div>
            </div>

            <div class="w-2/4  flex items-center space-x-6 justify-end ">

              <Link v-if="user == null" :href="route('login')" class="text-sm font-medium text-white hover:text-gray-100">
              {{ trans('Login') }}</Link>
              <Link v-if="user == null" :href="route('register')"
                class="text-sm font-medium text-white hover:text-gray-100">{{ trans('Register') }}</Link>
              <Link v-if="user" method="post" :href="route('logout')"
                class="text-sm font-medium text-white hover:text-gray-100">{{ trans('Logout') }}</Link>
            </div>

          </div>
        </div>

        <!-- Secondary navigation -->
        <div class="bg-gray-600 bg-opacity-10 backdrop-blur-md backdrop-filter">
          <div class="mx-auto  px-4 sm:px-6 lg:px-4">
            <div>
              <div class="flex h-16 items-center justify-between">

                <div class="hidden h-full lg:flex">
                  <!-- Flyout menus -->
                  <PopoverGroup class="inset-x-0 bottom-0 px-4">
                    <div class="flex h-full justify-center space-x-8">
                      <Popover v-for="category in navigation.categories" :key="category.name" class="flex"
                        v-slot="{ open }">
                        <div class="relative flex">
                          <PopoverButton
                            class="relative z-10 flex items-center justify-center text-sm font-medium text-white transition-colors duration-200 ease-out">
                            {{ category.name }}
                            <span
                              :class="[open ? 'bg-white' : '', 'absolute inset-x-0 -bottom-px h-0.5 transition duration-200 ease-out']"
                              aria-hidden="true" />
                          </PopoverButton>
                        </div>

                        <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
                          enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150"
                          leave-from-class="opacity-100" leave-to-class="opacity-0">
                          <PopoverPanel class="absolute inset-x-0 top-full text-sm text-gray-500">
                            <!-- Presentational element used to render the bottom shadow, if we put the shadow on the actual panel it pokes out the top, so we use this shorter element to hide the top of the shadow -->
                            <div class="absolute inset-0 top-1/2 bg-white shadow" aria-hidden="true" />

                            <div class="relative bg-white">
                              <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                                <div class="grid grid-cols-3 gap-x-8 gap-y-4 py-4">
                                  <div v-for="item in category.featured" :key="item.name" class="group relative">
                                    <a :href="item.href" class="mt-4 block font-medium text-gray-900">
                                      <span class="absolute inset-0 z-10" aria-hidden="true" />
                                      {{ item.name }}
                                    </a>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </PopoverPanel>
                        </transition>
                      </Popover>

                      <a v-for="page in navigation.pages" :key="page.name" :href="page.href"
                        class="flex items-center text-sm font-medium text-white">{{ page.name }}</a>
                    </div>
                  </PopoverGroup>
                </div>

                <!-- Mobile menu and search (lg-) -->
                <div class="flex flex-1 items-center lg:hidden">
                  <button type="button" class="-ml-2 p-2 text-white" @click="mobileMenuOpen = true">
                    <span class="sr-only">{{ trans('Open menu') }}</span>
                    <FontAwesomeIcon icon="fa-solid fa-bars" aria-hidden="true" />
                  </button>

                  <!-- Search -->
                  <div class="flex flex-1 items-center justify-end w-full">
                    <button type="button"
                      class="w-full bg-white lg:flex items-center text-sm leading-6 text-slate-400 rounded-md ring-1 ring-slate-900/10 shadow-sm py-1.5 pl-2 pr-3 hover:ring-slate-300 dark:bg-slate-800 dark:highlight-white/5 dark:hover:bg-slate-700">
                      {{ trans('Quick search...') }}
                    </button>
                  </div>

                </div>

                <div class="flex flex-1 items-center justify-end">
                  <!-- <a href="#" class="hidden text-sm font-medium text-white lg:block">Search</a> -->
                <button type="button"
                  class="hidden w-1/5 bg-white lg:flex items-center text-sm leading-6 text-slate-400 rounded-md ring-1 ring-slate-900/10 shadow-sm py-1.5 pl-2 pr-3 hover:ring-slate-300 dark:bg-slate-800 dark:highlight-white/5 dark:hover:bg-slate-700">
                  {{trans('Quick search...')}}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>
  </header>
</div></template>
 

<style>:focus-visible {
  outline: -webkit-focus-ring-color auto 0px;
}</style>