<template>
	<div>
		<div class="flex justify-between items-center">
			<Link
				:class="'ml-8 hidden lg:block xl:ml-0 mr-4'"
				:title="trans('Shop')"
				:href=" layout.currentShopSlug ? route('shops.show', layout.currentShopSlug) : route('shops.index')
            ">
				<font-awesome-icon
					aria-hidden="true"
					:icon="layout.currentShopSlug ? 'fal fa-store-alt' : 'fal fa-list'" />
			</Link>

			<!-- Dropdown -->
			<div class="grid">
				<AppShopNavigationDropDown class="place-self-center" />
			</div>

			<!-- Icon Shops -->
			<div
				class="flex flex-wrap py-3 my-2 md:py-0 md:my-0 md:mt-0 md:inline-flex justify-start items-center border-b border-gray-200 md:border-0 bg-gray-100/50 md:bg-inherit md:space-y-0 md:space-x-0">
				<Link
					v-for="(shopsFeature, index) in shopsFeatures"
					:key="index"
					class="group relative grid grid-flow-col grid-cols-7 justify-center items-center w-full py-1.5 px-4 space-x-0 group md:grid-cols-1 md:justify-end md:w-auto md:px-2 lg:px-4"
					:title="trans(shopsFeature.title)"
					:href="
						layout.currentShopSlug
							? route(shopsFeature.link1, layout.currentShopSlug)
							: route(shopsFeature.link2)
					">
					<div
						class="col-span-2 flex justify-center items-center text-gray-600 w-7 h-auto aspect-square">
						<!-- Helper: Border bottom to indicate current active link -->
						<div
							class="absolute w-9/12 bottom-0 group-hover:border-b group-hover:border-indigo-300"
							:class="{
								'border-b border-indigo-700 group-hover:border-indigo-700':
									route(shopsFeature.link2) == urlPage ||
									(layout.currentShopSlug
										? route(shopsFeature.link1, layout.currentShopSlug)
										: null) == urlPage,
							}" />
						<font-awesome-icon
							class="text-xs group-hover:text-indigo-300"
							aria-hidden="true"
							:icon="shopsFeature.icon"
							:class="{
								'text-indigo-600 group-hover:text-indigo-600':
									route(shopsFeature.link2) == urlPage ||
									(layout.currentShopSlug
										? route(shopsFeature.link1, layout.currentShopSlug)
										: null) == urlPage,
							}" />
					</div>
					<div class="md:hidden col-span-5">
						<span class="text-xs inline text-gray-700 group-hover:text-indigo-500">{{
							trans(shopsFeature.title)
						}}</span>
					</div>
				</Link>
			</div>
		</div>
	</div>
</template>

<script setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { Link, router } from "@inertiajs/vue3"
import { ref } from 'vue'
import AppShopNavigationDropDown from "@/Layouts/AppShopNavigationDropDown.vue"
import { trans } from "laravel-vue-i18n"
import { faList, faFolderTree, faMailBulk } from "@/../private/pro-light-svg-icons"
import { library } from "@fortawesome/fontawesome-svg-core"
library.add(faList, faFolderTree, faMailBulk)

import { useLayoutStore } from "@/Stores/layout"
const layout = useLayoutStore()
const shopsFeatures = [
    {
        title: trans("Catalogue"),
        link1: "shops.show.catalogue.hub",
        link2: "catalogue.hub",
        icon : "fal fa-folder-tree"
    },
    {
        title: trans("Website"),
        link1: "shops.show.website",
        link2: "websites.index",
        icon : "fal fa-globe"
    },

    {
        title: trans("Customers"),
        link1: "shops.show.customers.index",
        link2: "customers.index",
        icon : "fal fa-user"
    },
    {
        title: trans("Orders"),
        link1: "shops.show.orders.index",
        link2: "orders.index",
        icon : "fal fa-shopping-cart"
    },
    {
        title: trans("Mailroom"),
        link1: "shops.show.mail.hub",
        link2: "mail.hub",
        icon : "fal fa-mail-bulk"
    },
    {
        title: trans("Accounting"),
        link1: "shops.show.accounting.dashboard",
        link2: "accounting.dashboard",
        icon : "fal fa-abacus"
    },
    {
        title: trans("Dispatch"),
        link1: "shops.show.dispatch.hub",
        link2: "dispatch.hub",
        icon : "fal fa-conveyor-belt-alt"
    }
]

const urlPage = ref(location.href)
</script>

<style lang="scss" scoped></style>
