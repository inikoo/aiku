<script setup lang="ts">
import TableRow from "@/Components/Tables/Grp/Org/Web/TableRow.vue"

// Define the TreeNode interface.
interface TreeNode {
	id: number | string
	name: string
	value?: string
	status?: string
	children?: TreeNode[]
}

// Accept nodes as props.
const props = defineProps<{
	nodes: TreeNode[]
}>()
</script>

<template>
	<div class="overflow-x-auto bg-white shadow-md rounded-lg border border-gray-200">
		<table class="min-w-full divide-y divide-gray-300">
			<!-- Table Header -->
			<thead class="bg-gray-100">
				<tr>
					<th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
						Name
					</th>
				
					<th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
						Actions
					</th>
					<th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
						Expand
					</th>
				</tr>
			</thead>
			<!-- Table Body -->
			<tbody class="bg-white divide-y divide-gray-200">
				<TableRow v-for="node in nodes" :key="node.id" :node="node" :level="0" />
			</tbody>
		</table>
	</div>
</template>