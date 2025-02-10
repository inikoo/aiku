<script setup lang="ts">
import { ref } from "vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import ModalWebpage from "@/Components/Utils/ModalWebpage.vue";
import axios from "axios"
// Define component name
defineOptions({
	name: "TableRow",
})

// Define the TreeNode interface.
interface TreeNode {
	id: number | string
	name: string
	value?: string
	status?: string
	children?: TreeNode[]
}

const props = defineProps<{
	node: TreeNode
	level?: number
}>()

const { node, level = 0 } = props

// Local states
const isExpanded = ref(false)
const isLoading = ref(false)
const childrenData = ref<TreeNode[]>(node.children || [])

const dummyChildrenData: Record<string, TreeNode[]> = {
	"1": [
		{ id: "11", name: "Child A1", value: "50", status: "Active" },
		{ id: "12", name: "Child A2", value: "30", status: "Inactive" },
	],
	"11": [
		{ id: "111", name: "Child of A1 1", value: "10", status: "Pending" },
		{ id: "112", name: "Child of A1 2", value: "20", status: "Active" },
	],
	"12": [
		{ id: "121", name: "Child of A2 1", value: "15", status: "Active" },
		{ id: "122", name: "Child of A2 2", value: "25", status: "Inactive" },
	],
	"2": [
		{ id: "21", name: "Child B1", value: "40", status: "Active" },
	],
	"21": [
		{ id: "211", name: "Child of B1 1", value: "5", status: "Pending" },
		{ id: "212", name: "Child of B1 2", value: "10", status: "Active" },
	],
}

const fetchChildrenData = async () => {
	isLoading.value = true 

	setTimeout(() => {
		childrenData.value = dummyChildrenData[node.id] || []
		isLoading.value = false 
	}, 1000)
}

const toggle = () => {
	if (!isExpanded.value && childrenData.value.length === 0) {
		fetchChildrenData() // Load dummy data when expanded
	}
	isExpanded.value = !isExpanded.value
}

const showModal = ref(false)

const fetchChildrenData2 = async () => {
    if (childrenData.value.length === 0) {
        isLoading.value = true;
        try {
            const response = await axios.get(`/api/tree-nodes/${node.id}/children`);
            childrenData.value = response.data;
        } catch (error) {
            console.error("Error fetching child nodes:", error);
        }
        isLoading.value = false;
    }
    isExpanded.value = !isExpanded.value;
}

const openModal = () => { showModal.value = true }
const closeModal = () => { showModal.value = false }
</script>

<template>
	<tr>
		<td class="px-4 py-2 whitespace-nowrap">
			<div class="flex items-center">

				<div v-if="level > 0" :style="{ paddingLeft: `${level * 1.5}rem` }" class="h-full flex items-center">
					<FontAwesomeIcon icon="fal fa-grip-lines" class="cursor-move text-gray-500 hover:text-gray-700 mr-1" />
				</div>

				<!-- Drag Icon for Root-Level Rows -->
				<div v-else>
					<FontAwesomeIcon icon="fal fa-grip-lines" class="cursor-move text-gray-500 hover:text-gray-700 mr-2" />
				</div>

				<span class="font-medium text-gray-800">{{ node.name }}</span>
			</div>
		</td>
		
		<td class="px-4 py-2 text-center">
			<button @click="openModal">
				<FontAwesomeIcon icon="fal fa-plus" size="sm" />
			</button>
		</td>

		<td class="px-4 py-2 text-center">
			<button
				@click="toggle"
				class="text-gray-700 hover:text-gray-900 transition-all">
                <FontAwesomeIcon v-if="!isExpanded" icon="fal fa-chevron-right" size="sm" />
                <FontAwesomeIcon v-else icon="fal fa-chevron-down" size="sm" />
			</button>
		</td>
	</tr>

	<template v-if="isExpanded">
		<tr v-if="isLoading">
			<td colspan="5" class="text-center py-2 text-blue-500 font-semibold">
				Loading...
			</td>
		</tr>
		<TableRow v-for="child in childrenData" :key="child.id" :node="child" :level="level + 1" />
	</template>

    <ModalWebpage
		:isOpen="showModal" 
		:title="{ label: 'Webpage', information: 'Add new webpage' }"
		@close="closeModal"/>
</template>