<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 22 Aug 2023 19:44:06 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import Footer from "@/Components/CMS/Footer/index.vue"
import {
	menuTypeList,
	menuTypeDescription,
	menuTypeInfo,
	footerDataLayout,
	footerDataTools,
} from "./Descriptor"
import Tools from "./Tools.vue"
import { useForm } from "@inertiajs/vue3"
import { ref, watch } from "vue"

//InitialData
const ToolsBluprint = footerDataTools
const toolsValue = useForm({
	theme: "light-theme",
	hand: "click",
	columnType: null,
})
const footerDataLayoutForm = useForm({ ...footerDataLayout })
const activeColumn = ref(null)

const changeColumnType = (e) => {
	const index = footerDataLayoutForm.initialColumns.findIndex((item) => item.column_id == activeColumn.value)
	if (index != -1) {
		if (e == "description") footerDataLayoutForm.initialColumns.splice(index, 1, menuTypeDescription())
		if (e == "list") footerDataLayoutForm.initialColumns.splice(index, 1, menuTypeList())
		if (e == "info") footerDataLayoutForm.initialColumns.splice(index, 1, menuTypeInfo())
	}
}

</script>

<template>
	<div class="bg-white">
		<div class="p-3 border border-gray-300 overflow-y-auto overflow-x-hidden">
			<Tools
				:toolsBluprint="ToolsBluprint"
				v-model="toolsValue"
				@changeColumnType="changeColumnType" />
		</div>
		<div
			class="flex justify-center items-center bg-gray-200 border border-gray-300 transform scale-80 w-full h-screen">
			<Footer
				:tools="toolsValue"
				:activeColumn="activeColumn"
				:footerDataLayout="footerDataLayoutForm"
				@changeActiveColumn="(e : string )=> activeColumn = e" />
		</div>
	</div>
  <div @click="()=>console.log(footerDataLayoutForm)">set data to seee</div>
</template>
