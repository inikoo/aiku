<script setup lang="ts">
import {
	faFacebook,
	faInstagram,
	faTwitter,
	faGithub,
	faYoutube,
} from "@fortawesome/free-brands-svg-icons"
import {
	faMapMarkerAlt,
	faEnvelope,
	faBalanceScale,
	faBuilding,
	faPhone,
	faMap,
} from "@fortawesome/free-solid-svg-icons"
import { library } from "@fortawesome/fontawesome-svg-core"
import draggable from "vuedraggable"
import Input from "../Fields/Input.vue"
import TextArea from "../Fields/TextArea.vue"
import HyperLink from "../Fields/Hyperlink.vue"
import IconPicker from "../Fields/IconPicker/IconPicker.vue"
import SocialMediaPicker from "../Fields/SocialMediaTools.vue"
import TextEditor from "@/Components/Forms/Fields/TextEditor.vue"
import BubleTextEditor from "@/Components/Forms/Fields/BubleTextEditor/BubleTextEditor.vue"

library.add(
	faFacebook,
	faInstagram,
	faTwitter,
	faGithub,
	faYoutube,
	faMapMarkerAlt,
	faEnvelope,
	faBalanceScale,
	faBuilding,
	faPhone,
	faMap
)

const props = defineProps<{
	tool: Object
	footerDataLayout: object
}>()

console.log("theme1", props)
</script>

<template>
	<footer class="bg-gray-50 px-6">
		<div class="mx-auto max-w-7xl px-6 pb-8 pt-20 sm:pt-24 lg:px-8 lg:pt-12">
			<div class="xl:grid xl:gap-x-24 xl:px-6">
				<draggable
					:list="footerDataLayout.initialColumns"
					group="navigation"
					itemKey="id"
					:disabled="tool.hand !== 'grab'"
					:class="[
						'flex',
						'gap-8',
						'xl:col-span-2',
						tool.hand !== 'grab' ? 'cursor-pointer' : 'cursor-grab',
					]">
					<template #item="{ element, index }">
						<div :class="['space-y-3 w-1/4']">
							<Input
								:data="element"
								:save="props.saveItemTitle"
								keyValue="title"
								cssClass="text-sm font-bold leading-6 text-gray-700 capitalize" />
							<div v-if="element.type == 'list'">
								<draggable
									:list="element.data"
									group="list"
									itemKey="name"
									:disabled="tool.hand !== 'grab'">
									<template #item="{ element: child, index: childIndex }">
										<ul role="list">
											<li :key="child.name">
												<HyperLink
													valueKeyLabel="name"
													valueKeyLink="href"
													:useDelete="true"
													:data="child"
													cssClass="space-y-3 text-sm leading-6 text-gray-600 hover:text-indigo-500" />
											</li>
										</ul>
									</template>
								</draggable>
							</div>

							<div v-if="element.type == 'description'">
								<TextArea
									:data="element"
									:save="props.saveTextArea"
									:cssClass="'space-y-3 text-sm leading-6 text-gray-600 hover:text-indigo-500'" />
							</div>

							<div v-if="element.type == 'info'">
								<div class="flex flex-col gap-y-5">
									<draggable
										:list="element.data"
										group="info"
										@change="childLog"
										itemKey="name"
										:disabled="tool.hand.name !== 'grab'">
										<template #item="{ element: child, index: childIndex }">
											<div
												class="grid grid-cols-[auto,1fr] gap-4 items-center justify-start gap-y-3 mb-2.5">
												<div
													class="w-5 flex items-center justify-center text-gray-400">
													<!-- <FontAwesomeIcon :icon="child.icon" :title="child.title"
                                                aria-hidden="true" /> -->
													<IconPicker
														:modelValue="child.icon"
														:data="child"
														:save="
															(value) =>
																props.saveInfo({
																	parentId: element.id,
																	type: 'icon',
																	...value,
																})
														" />
												</div>
												<Input
													:data="child"
													:save="
														(value) =>
															props.saveInfo({
																parentId: element.id,
																type: 'value',
																...value,
															})
													"
													keyValue="value"
													cssClass="leading-5 text-gray-600" />
											</div>
										</template>
									</draggable>
								</div>
							</div>
						</div>
					</template>
				</draggable>
			</div>

			<!-- Social Media -->
			<div
				class="border-t border-gray-900/10 pt-8 sm:mt-10 flex flex-col md:flex-row items-center justify-between mt-16 lg:mt-18 xl:px-3">    
				<div class="md:order-2">
					<draggable
						:list="footerDataLayout.socials"
						group="socialMedia"
						itemKey="id"
						:class="[
							tool.hand.name === 'grab' ? 'cursor-grab' : 'cursor-pointer',
							'text-gray-400 hover:text-gray-500 flex space-x-6',
						]"
						@change="childLog"
						:disabled="tool.hand.name !== 'grab'">
						<template #item="{ element: child, index: childIndex }">
							<div>
								<span class="sr-only">{{ child.label }}</span>
								<SocialMediaPicker
									:modelValue="child.icon"
									cssClass="h-6 w-6"
									:data="child"
									:save="saveSocialmedia" />
							</div>
						</template>
					</draggable>
				</div>

				<div class="flex">
					<div class="mt-4 text-xs flex gap-1 leading-6 text-gray-500 md:order-1 md:mt-0">
						&copy; 2023
						<span class="font-bold">
							<HyperLink
								:useDelete="false"
								:data="footerDataLayout.copyRight"
								:save="copyRightSave"
								valueKeyLabel="label"
								valueKeyLink="href" /> </span
						>, Inc. All rights reserved.
					</div>
				</div>
			</div>
		</div>
	</footer>
</template>
