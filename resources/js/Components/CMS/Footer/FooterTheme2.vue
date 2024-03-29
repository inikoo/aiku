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
import BubleTextEditor from "@/Components/Forms/Fields/BubleTextEditor/BubleTextEditor.vue"
import { ref, watch, defineEmits } from "vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { menuItem } from '@/Components/CMS/Workshops/Footer/Descriptor.ts'

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
	activeColumn: string
}>()

const emits = defineEmits()

const handleChangeActiveColumn = (data: string) => {
	props.tool.columnType = data.type
	emits('changeActiveColumn', data.column_id)
}

const addMenu = (data, index) => {
	data.push(menuItem())
}

</script>

<template>
	<footer class="bg-gray-50 px-6">
		<div class="mx-auto w-[1300px] px-6 pb-8 pt-20 sm:pt-24 lg:px-8 lg:pt-12">
			<div class="xl:grid xl:gap-x-24 xl:px-6">
				<draggable :list="footerDataLayout.initialColumns" group="column" itemKey="column_id"
					:disabled="tool.hand !== 'grab'"
					:class="['flex gap-8 xl:col-span-2', tool.hand !== 'grab' ? 'cursor-pointer' : 'cursor-grab']">
					<template #item="{ element, index }">
						<div :class="['space-y-3 w-1/3', activeColumn == element.column_id ? 'outline-dashed' : null]"
							@click="() => handleChangeActiveColumn(element)">
							<BubleTextEditor v-if="tool.hand !== 'grab'" :form="element" field-name="title" />
							<div v-else v-html="element.title" class="m-2" ></div>
							<div v-if="element.type == 'list'">
								<draggable :list="element.data" group="menu" itemKey="menu_id"
									:disabled="tool.hand !== 'grab'">
									<template #item="{ element: child, index: childIndex }">
										<ul role="list">
											<li :key="child.column_id">
												<BubleTextEditor v-if="tool.hand !== 'grab'" :form="child" field-name="content" />
												<div v-else v-html="child.content" class="m-2"></div>
											</li>
										</ul>
									</template>
								</draggable>
								<Button size="xxs" :icon="'fal fa-plus'" type="dashed" class="m-2"
									@click="() => addMenu(element.data, childIndex)"></Button>
							</div>

							<div v-if="element.type == 'description'">
								<BubleTextEditor v-if="tool.hand !== 'grab'" :form="element" field-name="content" />
								<div v-else v-html="element.content" class="m-2"></div>
							</div>
						</div>
					</template>
				</draggable>
			</div>

			<!-- Social Media -->
			<div
				class="border-t border-gray-900/10 pt-8 sm:mt-10 flex flex-col md:flex-row items-center justify-between mt-16 lg:mt-18 xl:px-3">
				<div class="flex">
					<div class="mt-4 text-xs flex gap-1 leading-6 text-gray-500 md:order-1 md:mt-0">
						<BubleTextEditor :form="footerDataLayout" field-name="copyRight" />
					</div>
				</div>
			</div>
		</div>
	</footer>
</template>
