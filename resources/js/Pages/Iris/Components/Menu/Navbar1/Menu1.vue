<script setup lang="ts">
import { ref } from "vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import draggable from "vuedraggable"
import Hyperlink from '../../Fields/Hyperlink.vue'
import SubMenu from "./SubMenu.vue"
import { get } from 'lodash'
import { fas } from '@/../private/pro-solid-svg-icons';
import { fal } from '@/../private/pro-light-svg-icons';
import { far } from '@/../private/pro-regular-svg-icons';
import { fad } from '@/../private/pro-duotone-svg-icons';
import { fab } from "@fortawesome/free-brands-svg-icons"
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
library.add(fas, fal, far, fad, fab)

const props = defineProps<{
	navigation: Object
	saveNav: Function
	saveSubMenu : Function
	tool:Object
	selectedNav : Object
	changeNavActive : Function
}>()

const openNav  = ref(null)

const mobileMenuOpen = ref(false)
</script>

<template>
	<div class="bg-white">
		<!-- Mobile menu -->
		<TransitionRoot as="template" :show="mobileMenuOpen">
			<Dialog as="div" class="relative z-40 lg:hidden" @close="mobileMenuOpen = false">
				<TransitionChild
					as="template"
					enter="transition-opacity ease-linear duration-300"
					enter-from="opacity-0"
					enter-to="opacity-100"
					leave="transition-opacity ease-linear duration-300"
					leave-from="opacity-100"
					leave-to="opacity-0">
					<div class="fixed inset-0 bg-black bg-opacity-25" />
				</TransitionChild>

				<div class="fixed inset-0 z-40 flex">
					<TransitionChild
						as="template"
						enter="transition ease-in-out duration-300 transform"
						enter-from="-translate-x-full"
						enter-to="translate-x-0"
						leave="transition ease-in-out duration-300 transform"
						leave-from="translate-x-0"
						leave-to="-translate-x-full">
						<DialogPanel
							class="relative flex w-full max-w-xs flex-col overflow-y-auto bg-white pb-12 shadow-xl">
							<div class="flex px-4 pb-2 pt-5">
								<button
									type="button"
									class="-m-2 inline-flex items-center justify-center rounded-md p-2 text-gray-400"
									@click="mobileMenuOpen = false">
									<span class="sr-only">Close menu</span>
									saasd
								</button>
							</div>

							<!-- Links -->
							<TabGroup as="div" class="mt-2">
								<div class="border-b border-gray">
									<TabList
										class="-mb-px flex space-x-8 px-4 w-304 overflow-x-auto">
										<Tab
											as="template"
											v-for="category in navigation.categories"
											:key="category.name"
											v-slot="{ selected }">
											<button
												:class="[
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
									<TabPanel
										v-for="category in navigation.categories"
										:key="category.name"
										class="space-y-12 px-4 py-6">
										<div class="grid grid-cols-1 gap-x-4">
											<div
												v-for="item in category.featured"
												:key="item.name"
												class="group relative">
												<a
													:href="item.href"
													class="mt-6 block text-sm font-medium text-gray-900">
													<span
														class="absolute inset-0 z-10"
														aria-hidden="true" />
													{{ item.name }}
												</a>
											</div>
										</div>
									</TabPanel>
								</TabPanels>
							</TabGroup>

							<div class="space-y-6 border-t border-gray-200 px-4 py-6">
								<div
									v-for="page in navigation.pages"
									:key="page.name"
									class="flow-root">
									<a
										:href="page.href"
										class="-m-2 block p-2 font-medium text-gray-900"
										>{{ page.name }}</a
									>
								</div>
							</div>
						</DialogPanel>
					</TransitionChild>
				</div>
			</Dialog>
		</TransitionRoot>

		<!-- Desktop -->
		<header class="relative z-10">
			<nav aria-label="Top">
				<!-- Top navigation -->
				<div class="bg-gray-900">
					<div class="mx-auto flex h-10 px-4 sm:px-6 lg:px-8">
						<div class="w-2/4">
							<div class="hidden lg:flex lg:flex-1 lg:items-center">
								<a href="#">
									<div class="flex">
										<img
											class="h-8 w-auto"
											src="https://tailwindui.com/img/logos/mark.svg?color=white"
											alt="" /><span
											class="p-1 text-2xl font-semibold text-white"
											>AW GIFT</span
										>
									</div>
								</a>
							</div>
						</div>

						<div class="w-2/4 flex items-center space-x-6 justify-end">
							<div class="flex flex-1 items-center justify-end">
								<div class="flex items-center lg:ml-8">
									<div class="flex space-x-8">
										<div class="hidden lg:flex">
											<a
												href="#"
												class="-m-2 p-2 text-gray-400 hover:text-gray-500">
												<span class="sr-only">Search</span>
												<font-awesome-icon :icon="['fas', 'fa-search']" />
											</a>
										</div>

										<div class="flex">
											<a
												href="#"
												class="-m-2 p-2 text-gray-400 hover:text-gray-500">
												<span class="sr-only">Account</span>
												<font-awesome-icon :icon="['fas', 'user']" />
											</a>
										</div>
										<div class="flex">
											<a
												href="#"
												class="-m-2 p-2 text-gray-400 hover:text-gray-500">
												<span class="sr-only">Account</span>
												<font-awesome-icon :icon="['fass', 'heart']" />
											</a>
										</div>
									</div>

									<span
										class="mx-4 h-6 w-px bg-gray-200 lg:mx-6"
										aria-hidden="true" />

									<div class="flow-root">
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
						</div>
					</div>
				</div>

				<!-- Secondary navigation -->
				<div class="bg-white">
					<div class="border-b border-gray-200">
						<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
							<div class="flex h-16 items-center justify-center">
								<!-- Logo (lg+) -->
								<div class="hidden lg:flex">
									<!-- Mega menus -->
									<div class="ml-8">
										<draggable
											:list="navigation.categories"
											group="topMenu"
											key="id"
											:disabled="tool.name !== 'grab'"
											class="flex h-full justify-center space-x-8 align-middle">
											<template v-slot:item="{ element, index }">
												<div :class="[get(selectedNav,'id') == element.id ? 'border' : '']" >
													<div
															v-if="element.type === 'group'" 
															class="p-2.5" @click="() => { openNav = element.id, changeNavActive(element) }">
															<div :key="element.name" class="flex"  >
																<div class="relative flex">
																	<div
																		:class="[openNav == element.id ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-700 hover:text-gray-800', tool.name !== 'grab' ? 'cursor-pointer' : 'cursor-grab', 'relative z-10 -mb-px flex items-center border-b-2 pt-px text-sm font-medium transition-colors duration-200 ease-out']">
																		<Hyperlink
																:data="element"
																valueKeyLabel="name"
																valueKeyLink="link"
																:save="(e) => saveNav({ ...e, menuType: 'group' })"
																:useLink="false"
																cssClass="flex items-center text-sm font-medium text-gray-700 hover:text-gray-800" />
																	</div>
																</div>

																<div v-if="openNav == element.id">
																	<SubMenu :data="element" :saveSubMenu="saveSubMenu" :closePopover="() => { changeNavActive(null), openNav = null }" :tool="tool"/>
																</div>
															
															</div>
														</div>
													<div
													    @click="(e)=> {changeNavActive(element), openNav=null}"
														v-if="element.type === 'link'"
														class="p-2.5">
														<Hyperlink
															:data="element"
															valueKeyLabel="name"
															valueKeyLink="link"
															:save="(e)=>saveNav({...e,menuType:'link'})"
															cssClass="flex items-center text-sm font-medium text-gray-700 hover:text-gray-800" />
													</div>
												</div>
											</template>
										</draggable>
									</div>
								</div>

								<!-- Mobile menu and search (lg-) -->
								<div class="flex flex-1 items-center lg:hidden">
									<button
										type="button"
										class="-ml-2 rounded-md bg-white p-2 text-gray-400"
										@click="mobileMenuOpen = true">
										<span class="sr-only">Open menu</span>
										=
									</button>

									<!-- Search -->
									<a href="#" class="ml-2 p-2 text-gray-400 hover:text-gray-500">
										<span class="sr-only">Search</span>
										=
									</a>
								</div>

								<!-- Logo (lg-) -->
								<a href="#" class="lg:hidden">
									<span class="sr-only">Your Company</span>
									<img
										src="https://tailwindui.com/img/logos/mark.svg?color=indigo&shade=600"
										alt=""
										class="h-8 w-auto" />
								</a>
							</div>
						</div>
					</div>
				</div>
			</nav>
		</header>
	</div>
</template>
