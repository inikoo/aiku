<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, IframeHTMLAttributes } from "vue"
import { Head, router } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import { capitalize } from "@/Composables/capitalize"
import axios from "axios"
import Publish from "@/Components/Publish.vue"
import { notify } from "@kyvg/vue3-notification"
import ScreenView from "@/Components/ScreenView.vue"
import WebpageSideEditor from "@/Components/Workshop/WebpageSideEditor.vue"
import Drawer from "primevue/drawer"
/* import { socketWeblock } from "@/Composables/SocketWebBlock" */
import Toggle from "@/Components/Pure/Toggle.vue"
import { setIframeView } from "@/Composables/Workshop"
import ProgressSpinner from 'primevue/progressspinner';
import Button from "@/Components/Elements/Buttons/Button.vue"

import { Root, Daum } from "@/types/webBlockTypes"
import { Root as RootWebpage } from "@/types/webpageTypes"
import { PageHeading as PageHeadingTypes } from "@/types/PageHeading"

import { faBrowser, faDraftingCompass, faRectangleWide, faStars, faBars, faExternalLink, } from "@fal"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { trans } from "laravel-vue-i18n"
import LoadingIcon from "@/Components/Utils/LoadingIcon.vue"
import ButtonPreviewLogin from "@/Components/Workshop/Tools/ButtonPreviewLogin.vue";
import ButtonPreviewEdit from "@/Components/Workshop/Tools/ButtonPreviewEdit.vue";

library.add(faBrowser, faDraftingCompass, faRectangleWide, faStars, faBars)

const props = defineProps<{
	title: string
	pageHead: PageHeadingTypes
	webpage: RootWebpage
	webBlockTypes: Root
}>()


const comment = ref("")
const isLoading = ref<string | boolean>(false)
const openDrawer = ref<string | boolean>(false)
const isPreviewMode = ref<boolean>(false)
const iframeSrc = 
	route("grp.websites.preview", [
		route().params["website"],
		route().params["webpage"],
		{
			organisation: route().params["organisation"],
			shop: route().params["shop"],
		},
	]
)
const data = ref(props.webpage)
const iframeClass = ref("w-full h-full")
const isIframeLoading = ref(true)
const _WebpageSideEditor = ref(null)
const isPreviewLoggedIn = ref(false)
const isModalBlockList = ref(false)
/* const socketConnectionWebpage = props.webpage ? socketWeblock(props.webpage.slug) : null */
const _iframe = ref<IframeHTMLAttributes | null>(null)
const isLoadingblock = ref<string | null>(null)
const isSavingBlock = ref(false)
const isAddBlockLoading = ref<string | null>(null)
const addBlockCancelToken = ref<Function | null>(null)
const orderBlockCancelToken = ref<Function | null>(null)
const deleteBlockCancelToken = ref<Function | null>(null)

const addNewBlock = async (block: Daum) => {
	if (addBlockCancelToken.value) addBlockCancelToken.value()
	router.post(
		route(props.webpage.add_web_block_route.name, props.webpage.add_web_block_route.parameters),
		{ web_block_type_id: block.id },
		{
			onStart: () => (isAddBlockLoading.value = "addBlock" + block.id),
			onFinish: () => {
				addBlockCancelToken.value = null
				isAddBlockLoading.value = null
			},
			onCancelToken: (cancelToken) => {
                addBlockCancelToken.value = cancelToken.cancel
            },
			onSuccess:(e) => { 
				data.value = e.props.webpage 
				sendToIframe({ key: 'reload', value: {} })
			},
			onError: (error) => {
				notify({
					title: trans("Something went wrong"),
					text: error.message,
					type: "error",
				})
			},
		}
	)
}

const sendBlockUpdate = async (block: Daum) => {
	router.patch(
		route(props.webpage.update_model_has_web_blocks_route.name, {
			modelHasWebBlocks: block.id,
		}),
		{
			layout: block.web_block.layout,
			show_logged_in: block.visibility.in,
			show_logged_out: block.visibility.out,
			show: block.show,
		},
		{
			onStart: () => {
				isLoadingblock.value = "deleteBlock" + block.id,
				isSavingBlock.value = true
			},
			onFinish: () => {
				isLoadingblock.value = null
				isSavingBlock.value = false
			},
			onSuccess:(e) => { 
				data.value = e.props.webpage 
				sendToIframe({ key: 'reload', value: {} })
			},
			onError: (error) => {
				notify({
					title: trans("Something went wrong"),
					text: error.message,
					type: "error",
				})
			},
			preserveScroll: true,
		}
	)
}

const sendOrderBlock = async (block: Object) => {
	if (orderBlockCancelToken.value) orderBlockCancelToken.value()
	router.post(
		route(props.webpage.reorder_web_blocks_route.name,props.webpage.reorder_web_blocks_route.parameters),
		{ positions: block },
        {
            onStart: () => {},
            onFinish: () => {
				isLoadingblock.value = null
				orderBlockCancelToken.value = null
			},
			onCancelToken: (cancelToken) => {
                orderBlockCancelToken.value = cancelToken.cancel
            },
			onSuccess:(e) => { 
				data.value = e.props.webpage 
				sendToIframe({ key: 'reload', value: {} })
			},
            onError: (error) => {
                notify({
                    title: trans('Something went wrong'),
                    text: error.message,
                    type: 'error',
                })
            }
        }
    )
}

const sendDeleteBlock = async (block: Daum) => {
	if (deleteBlockCancelToken.value) deleteBlockCancelToken.value()
	router.delete(
        route(props.webpage.delete_model_has_web_blocks_route.name, { modelHasWebBlocks: block.id }),
        {
            onStart: () => isLoadingblock.value = 'deleteBlock' + block.id,
            onFinish: () => {
				isLoadingblock.value = null
				orderBlockCancelToken.value = null
			},
			onCancelToken: (cancelToken) => {
                deleteBlockCancelToken.value = cancelToken.cancel
            },
			onSuccess:(e) => { 
				data.value = e.props.webpage 
				sendToIframe({ key: 'reload', value: {} })
			},
            onError: (error) => {
                notify({
                    title: trans('Something went wrong'),
                    text: error.message,
                    type: 'error',
                })
            }
        }
    )
}

const onPublish = async (action: {}, popover: {}) => {
	try {
		// Ensure action is defined and has necessary properties
		if (!action || !action.method || !action.name || !action.parameters) {
			throw new Error("Invalid action parameters")
		}

		isLoading.value = true

		// Make sure route and axios are defined and used correctly
		const response = await axios[action.method](route(action.name, action.parameters), {
			comment: comment.value,
			publishLayout: { blocks: data.value.layout },
		})
		popover.close()
	} catch (error) {
		// Ensure the error is logged properly
		console.error("Error:", error)
		const errorMessage =
			error.response?.data?.message || error.message || "Unknown error occurred"
		notify({
			title: "Something went wrong.",
			text: errorMessage,
			type: "error",
		})
	} finally {
		isLoading.value = false
	}
}

const handleIframeError = () => {
	console.error("Failed to load iframe content.")
}

const openFullScreenPreview = () => {
	window.open(iframeSrc + '&isInWorkshop=true', "_blank")
}

const setHideBlock = (block : Daum) => {
	block.show = !block.show 
	isLoadingblock.value = "deleteBlock" + block.id,
	sendBlockUpdate(block)
}

const sendToIframe = (data: any) => {
	_iframe.value?.contentWindow.postMessage(data, "*")
}


// watch(isPreviewMode, (newVal) => {
//     sendToIframe({ key: 'isPreviewMode', value: newVal })
// }, { deep: true })

onMounted(() => {
/* 	if (socketConnectionWebpage)
		socketConnectionWebpage.actions.subscribe((value: Root) => {
			data.value = { ...data.value, ...value }
		}) */
	window.addEventListener("message", (event) => {
		if (event.origin !== window.location.origin) return;
		const { data } = event;
		if (event.data === "openModalBlockList") {
			isModalBlockList.value = true
		} else if (data.key === 'autosave') {
			sendBlockUpdate(data.value)
		}
	})
})

onUnmounted(() => {
/* 	if (socketConnectionWebpage) socketConnectionWebpage.actions.unsubscribe() */
})

</script>

<template>
	<Head :title="capitalize(title)" />
	<PageHeading :data="pageHead">
		<template #button-publish="{ action }">
			<Publish
				:isLoading="isLoading"
				:is_dirty="data.is_dirty"
				v-model="comment"
				@onPublish="(popover) => onPublish(action.route, popover)" />
		</template>

		<template #afterTitle v-if="isSavingBlock">
			<LoadingIcon v-tooltip="trans('Saving..')" />
		</template>
	</PageHeading>

	<div class="grid grid-cols-5 h-[84vh]">
		<!-- Section: Side editor -->
		<div class="col-span-1 lg:block hidden h-full border-2 bg-gray-200 px-3 py-1">
			<WebpageSideEditor
				v-model="isModalBlockList"
				:isLoadingblock
				:isAddBlockLoading
				:webpage="data"
				:webBlockTypes="webBlockTypes"
				@update="sendBlockUpdate"
				@delete="sendDeleteBlock"
				@add="addNewBlock"
				@order="sendOrderBlock" 
				@setVisible="setHideBlock"
			/>
		</div>

		<!-- Section: Preview -->
		<div v-if="true" class="lg:col-span-4 col-span-5 h-full flex flex-col bg-gray-200">
			<div class="flex justify-between">
				<div
					class="py-1 px-2 cursor-pointer lg:hidden block"
					title="Desktop view"
					v-tooltip="'Navigation'">
					<FontAwesomeIcon
						:icon="faBars"
						aria-hidden="true"
						@click="() => (openDrawer = true)" />
					<Drawer v-model:visible="openDrawer" :header="''" :dismissable="true">
						<WebpageSideEditor
							v-model="isModalBlockList"
							:isAddBlockLoading
							:isLoadingblock
							ref="_WebpageSideEditor"
							:webpage="data"
							:webBlockTypes="webBlockTypes"
							@update="sendBlockUpdate"
							@delete="sendDeleteBlock"
							@add="addNewBlock"
							@order="sendOrderBlock"
							@openBlockList=" () => {
									;(openDrawer = false), (isModalBlockList = true)
								}
							" />
					</Drawer>
				</div>

				<!-- Section: Screenview -->
				<div class="flex">
					<ScreenView @screenView="(e)=>iframeClass = setIframeView(e)" />
					<div
						class="py-1 px-2 cursor-pointer"
						title="Desktop view"
						v-tooltip="'Preview'"
						@click="openFullScreenPreview">
						<FontAwesomeIcon :icon="faExternalLink" aria-hidden="true" />
					</div>
				</div>

				<!-- Tools: login-logout, edit-preview -->
				<div class="flex gap-3 items-center px-4">
					<ButtonPreviewLogin
						:modelValue="isPreviewLoggedIn"
						@update:modelValue="(newVal) => sendToIframe({ key: 'isPreviewLoggedIn', value: newVal }) "
					/>

					<div class="h-6 w-px bg-gray-400 mx-2"></div>

					<ButtonPreviewEdit
						:modelValue="isPreviewMode"
						@update:modelValue="(newVal) => sendToIframe({ key: 'isPreviewMode', value: newVal }) "
					/>
				</div>
			</div>

			<div class="border-2 h-full w-full">
			<!-- 	<div
					v-if="isIframeLoading"
					class="flex justify-center items-center w-full h-64 p-12 bg-white">
					<FontAwesomeIcon
						icon="fad fa-spinner-third"
						class="animate-spin w-6"
						aria-hidden="true" />
				</div> -->
				<div v-if="isIframeLoading" class="loading-overlay">
					<ProgressSpinner />
				</div>

				<div v-show="!isIframeLoading" class="h-full w-full bg-white overflow-auto">
					<iframe
						ref="_iframe"
						:src="iframeSrc"
						:title="props.title"
						:class="[iframeClass, isIframeLoading ? 'hidden' : '']"
						@error="handleIframeError"
						@load="isIframeLoading = false" />
				</div>
			</div>
		</div>
	</div>
</template>

<style scoped>
iframe {
	height: 100%;
	transition: width 0.3s ease;
}

:deep(.loading-overlay) {
    position: block;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.8);
    z-index: 1000;
}

:deep(.spinner) {
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top: 4px solid #3498db;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>
