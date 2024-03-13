<script setup lang="ts">
import { ref } from "vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import draggable from "vuedraggable"
import Hyperlink from "../../Fields/Hyperlink.vue"
import SubMenu from "./SubMenu.vue"
import { get } from "lodash"
import BubleTextEditor from "@/Components/Forms/Fields/BubleTextEditor/BubleTextEditor.vue"

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

const props = defineProps<{
	tool: Object
	menuDataLayout: object
	activeColumn: string
}>()
</script>

<template>
	<div class="bg-white">
		<header class="relative z-10">
			<nav aria-label="Top">
				<!-- Secondary navigation -->
				<div class="bg-white">
					<div class="border-b border-gray-200">
						<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
							<div class="flex h-16 items-center justify-center">
								<div class="hidden lg:flex">
									<div class="ml-8">
										<draggable :list="menuDataLayout.initialColumns" group="topMenu" key="column_id"
											:disabled="tool.hand !== 'grab'"
											class="flex h-full justify-center space-x-8 align-middle">
											<template v-slot:item="{ element, index }">
												<div>
													<div v-if="element.type === 'group'" class="p-2.5">
														<div :key="element.column_id" class="flex">
															<div class="relative flex p-2.5">
																<div :class="[
																		tool.hand !== 'grab'
																			? 'cursor-pointer'
																			: 'cursor-grab',
																	]">
																	<BubleTextEditor v-if="tool.hand !== 'grab'"
																		:form="element" field-name="content" />
																	<div v-else v-html="element.content" class="m-2">
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div v-if="element.type === 'single'" class="py-5 px-2.5">
														<BubleTextEditor v-if="tool.hand !== 'grab'" :form="element"
															field-name="content" />
														<div v-else v-html="element.content" class="m-2"></div>
													</div>
												</div>
											</template>
										</draggable>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</nav>
		</header>
	</div>
</template>
