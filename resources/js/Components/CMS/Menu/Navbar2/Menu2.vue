<script setup>
import { ref } from "vue"
import {
  Dialog,
  DialogPanel,
  Tab,
  TabGroup,
  TabList,
  TabPanel,
  TabPanels,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue"
import draggable from "vuedraggable"
import Hyperlink from "../../Fields/Hyperlink.vue"
import SubMenu from "./Submenu.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { trans } from "laravel-vue-i18n"
import { Link } from "@inertiajs/vue3"
import { get } from 'lodash'


const props = defineProps({
  navigation: {
    type: Object,
    required: true,
  },
  saveNav: {
    type: Function,
    required: true,
  },
  saveSubMenu: {
    type: Function,
    required: true,
  },
  tool: {
    type: Object,
    required: true,
  },
  selectedNav: {
    type: Object,
    required: true,
  },
  changeNavActive: {
    type: Function,
    required: true,
  },
})
const openNav = ref(null)
const mobileMenuOpen = ref(false)
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
                    <button :class="[
                      selected
                        ? 'border-indigo-600 text-indigo-600'
                        : 'border-transparent text-gray-900',
                      'flex-1 whitespace-nowrap border-b-2 px-1 py-4 text-base font-medium',
                    ]">
                      {{ category.name }}
                    </button>
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
                <Link :href="route('grp.login.show')" class="-m-2 block p-2 font-medium text-gray-900">{{ trans("Login") }}</Link>
              </div>
              <div class="flow-root" v-if="user == null">
                <Link :href="route('grp.register')" class="-m-2 block p-2 font-medium text-gray-900">{{ trans("Register") }}
                </Link>
              </div>
              <div class="flow-root" v-if="user">
                <Link method="post" :href="route('grp.logout')" class="-m-2 block p-2 font-medium text-gray-900">
                {{ trans("Logout") }}</Link>
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
          <div class="mx-auto flex h-10 px-4 sm:px-6 lg:px-8">
            <div class="w-1/3 flex items-center space-x-6 justify-start">
                  <a
  												href="#"
  												class="-m-2 p-2 text-gray-400 hover:text-gray-500">
  												<span class="sr-only">Account</span>
                          <font-awesome-icon :icon="['fasr', 'fa-arrow-alt-circle-right']" rotation=180 />
  											</a>

              </div>

            <div class="w-1/3 ">
              <div class="hidden lg:flex lg:flex-1 lg:items-center justify-center">
                <a href="#">
                  <div class="flex">
                    <img class="h-8 w-auto" src="https://tailwindui.com/img/logos/mark.svg?color=white" alt="" /><span
                      class="p-1 text-2xl font-semibold text-white">AW GIFT</span>
                  </div>
                </a>
              </div>
            </div>

            <div class="w-1/3 flex items-center space-x-6 justify-end">
                  <a
  												href="#"
  												class="-m-2 p-2 text-gray-400 hover:text-gray-500">
  												<span class="sr-only">Account</span>
  												<font-awesome-icon :icon="['fas', 'user']" />
  											</a>
                        <a
  												href="#"
  												class="-m-2 p-2 text-gray-400 hover:text-gray-500">
  												<span class="sr-only">Account</span>
  												<font-awesome-icon :icon="['fass', 'heart']" />
  											</a>
                        <a href="#" class="group -m-2 flex items-center p-2 -m-2 p-2 text-gray-400 hover:text-gray-500">
  											<font-awesome-icon :icon="['fas', 'shopping-cart']" />
  											<span
  												class="ml-2 text-sm font-medium text-gray-700 group-hover:text-gray-800"
  												>0</span
  											>
  											<span class="sr-only">items in cart, view bag</span>
  										</a>
              </div>
            </div>
        </div>

        <!-- Secondary navigation -->
        <div class="bg-gray-600 bg-opacity-10">
          <div class="mx-auto px-4 sm:px-6 lg:px-4">
            <div>
              <div class="flex h-16 items-center justify-between">
                <div class="hidden h-full lg:flex">
                  <!-- Flyout menus -->
                  <div>
                    <div>
                      <draggable v-model="navigation.categories" group="topMenu" options="id"
                        :disabled="tool.name !== 'grab'" class="flex justify-center space-x-8 h-fit">
                        <template v-slot:item="{ element: category, index }">
                          <div :class="[get(selectedNav,'id') == category.id ? 'border' : '', tool.name !== 'grab' ? 'cursor-pointer' : 'cursor-grab']">
                            <div v-if="category.type == 'group'" :key="category.id" class=" flex">

                                <div @click="() => { openNav = category.id, changeNavActive(category) }"
                                  class="py-5 px-2.5 relative z-10 items-center justify-center text-sm font-medium text-white transition-colors duration-200 ease-out">
                                  <Hyperlink :data="category" valueKeyLabel="name" valueKeyLink="link"
                                    :save="(e) => saveNav({ ...e, menuType: 'group' })" :useLink="false" />
                                  <span :class="[
                                    openNav == category.id ? 'bg-white' : '',
                                    'absolute inset-x-0 -bottom-px h-0.5 transition duration-200 ease-out ',
                                  ]" aria-hidden="true" />
                                </div>

                                <div v-if="openNav == category.id">
                                  <SubMenu :data="category" :saveSubMenu="saveSubMenu"
                                    :closePopover="() => { changeNavActive(null), openNav = null }"
                                    :tool="tool" />
                                </div>

                            </div>
                            <div v-if="category.type == 'link'" class="py-5 px-2.5 leading-4"  @click="(e)=> {changeNavActive(category), openNav=null}">

                              <Hyperlink :data="category" valueKeyLabel="name" valueKeyLink="link"
                                :save="(e) => saveNav({ ...e, menuType: 'link' })"
                                cssClass="items-center text-sm font-medium text-white" />

                            </div>
                          </div>
                        </template>
                      </draggable>
                    </div>
                  </div>
                </div>

                <!-- Mobile menu and search (lg-) -->
                <div class="flex flex-1 items-center lg:hidden">
                  <button type="button" class="-ml-2 p-2 text-white" @click="mobileMenuOpen = true">
                    <span class="sr-only">{{ trans("Open menu") }}</span>
                    <FontAwesomeIcon icon="fa-solid fa-bars" aria-hidden="true" />
                  </button>

                  <!-- Search -->
                  <div class="flex flex-1 items-center justify-end w-full">
                    <button type="button"
                      class="w-full bg-white lg:flex items-center text-sm leading-6 text-slate-400 rounded-md ring-1 ring-slate-900/10 shadow-sm py-1.5 pl-2 pr-3 hover:ring-slate-300 dark:bg-slate-800 dark:highlight-white/5 dark:hover:bg-slate-700">
                      {{ trans("Quick search...") }}
                    </button>
                  </div>
                </div>

                <div class="flex flex-1 items-center justify-end">
                  <!-- <a href="#" class="hidden text-sm font-medium text-white lg:block">Search</a> -->
                  <button type="button"
                    class="hidden w-2/5 bg-white lg:flex items-center text-sm leading-6 text-slate-400 rounded-md ring-1 ring-slate-900/10 shadow-sm py-1.5 pl-2 pr-3 hover:ring-slate-300 dark:bg-slate-800 dark:highlight-white/5 dark:hover:bg-slate-700">
                    {{ trans("Quick search...") }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </nav>
    </header>
  </div>
</template>

<style>
:focus-visible {
  outline: -webkit-focus-ring-color auto 0px;
}
</style>
