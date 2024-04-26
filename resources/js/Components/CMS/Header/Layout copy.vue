<script setup lang="ts">
import VueResizable from "vue-resizable"
import { ref } from "vue"
import { set, get } from "lodash"
import Search from "@/Components/CMS/Utils/Search.vue";


import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core';
import { faHandPointer, faHandRock, faPlus, faText, faSearch, faImage } from '@fas';
import Input from "../Fields/Input.vue"
library.add(faHandPointer, faText, faSearch, faImage)
const props = defineProps<{
	data: Object
	layout: Object
	setActive: Function
	layerActive?: Object
}>()


function setdragElement(ref, value) {
	value.ref = ref
	dragElement(ref, value)
}

function dragElement(elmnt, dataSet) {
	let pos3 = 0, pos4 = 0

	const dragMouseDown = (e) => {
		e = e || window.event
		e.preventDefault()

		pos3 = e.clientX
		pos4 = e.clientY

		document.onmouseup = closeDragElement
		document.onmousemove = elementDrag
	}

	const elementDrag = (e) => {
		e = e || window.event
		e.preventDefault()

		let pos1 = pos3 - e.clientX
		let pos2 = pos4 - e.clientY
		pos3 = e.clientX
		pos4 = e.clientY

		const containerLeft = containerSize.value.left
		const containerTop = containerSize.value.top
		const containerRight = containerSize.value.right
		const containerBottom = containerSize.value.bottom

		// Calculate the new position of the dragged element
		let newTop = elmnt.offsetTop - pos2
		let newLeft = elmnt.offsetLeft - pos1

		// Check if the new position is within the container boundaries
		if (
			newTop >= containerTop &&
			newLeft >= containerLeft &&
			newLeft + elmnt.offsetWidth <= containerRight &&
			newTop + elmnt.offsetHeight <= containerBottom
		) {
			elmnt.style.top = newTop
			elmnt.style.left = newLeft
		}
		set(dataSet, 'style', { ...dataSet.style, top: newTop, left: newLeft })


	}

	const closeDragElement = () => {
		document.onmouseup = null
		document.onmousemove = null
	}

	if (elmnt) elmnt.querySelector(".draggable-handle").addEventListener("mousedown", dragMouseDown)
}

const containerSize = ref(null)
const eHandler = (data) => {
	containerSize.value = { ...data, right: data.left + data.width, bottom: data.top + data.height }
}

const generateThumbnail = (file) => {
	if (file.file && file.file instanceof File) {
		let fileSrc = URL.createObjectURL(file.file)
		setTimeout(() => {
			URL.revokeObjectURL(fileSrc)
		}, 1000)
		return fileSrc
	} else {
		return file.imageSrc
	}
}
</script>

<template>
	<div>
		<vue-resizable class="container bg-white overflow-hidden" :minHeight="200" :maxWidth="1500" :minWidth="1500" :height="layout.height" :maxHeight="200"
		:left="layout.left" :top="layout.top" @mount="eHandler" @resize:move="eHandler" @resize:start="eHandler"
		@resize:end="eHandler" @drag:move="eHandler" @drag:start="eHandler" @drag:end="eHandler">
			<div v-for="(item, index) in props.data.slice().reverse()" :key="item.id" :fit-parent="true">
				<div v-if="item.type == 'text'" :ref="(refValue) => setdragElement(refValue, item)"
					class="col-sm-10 draggable-component"
					:style="{
							top: item.style?.top + 'px',
							left: item.style?.left + 'px',
							fontSize: item.style?.fontSize + 'px',
							color: item.style?.color || '#374151'
							}">
					<div :class="['draggable-handle', { border: get(data[layerActive], 'id') === item.id }]"
						@click="(e) => { e.stopPropagation(); props.setActive(item.id) }">
						<Input :data="item" keyValue="name" :styleCss="item.style" />
					</div>
				</div>

				<div v-if="item.type == 'search'" :ref="(refValue) => setdragElement(refValue, item)"
					class="col-sm-10 draggable-component"
					:style="{
							top: item.style?.top + 'px',
							left: item.style?.left + 'px',
							fontSize: item.style?.fontSize + 'px',
							color: item.style?.color || '#374151'
							}">
					<div :class="['draggable-handle', { border: get(data[layerActive], 'id') === item.id }]"
						@click="(e) => { e.stopPropagation(); props.setActive(item.id) }">
						<Search />
					</div>
				</div>


				<div v-if="item.type == 'image'" :ref="(refValue) => setdragElement(refValue, item)"
					class="col-sm-10 draggable-component" :style="{ ...item.style }">
					<div :class="['draggable-handle', { border: get(data[layerActive], 'id') === item.id }]"
						@click="(e) => { e.stopPropagation(); props.setActive(item.id) }">
						<img :class="['draggable-handle', { border: get(data[layerActive], 'id') === item.id }]"
							class="preview-img" :src="generateThumbnail(item)" :style="`width: ${item.style.width}px; height: ${item.style.height}px;`" />
					</div>
				</div>
			</div>

			<!-- <vue-resizable
          :key="item.id"
          :maxWidth="layout.height"
          :maxHeight="1233"
          v-if="item.type == 'image'"
          :ref="(refValue) => setdragElement(refValue.$el, item)"
          class="col-sm-10 draggable-component"
          :style="{ ...item.style }"
        >

            <img :class="['draggable-handle', { border: get(data[layerActive], 'id') === item.id }]"  @click="(e) => { e.stopPropagation(); props.setActive(index) }" class="preview-img" :src="generateThumbnail(item)" style="width: 100%; height: 100%;"/>

        </vue-resizable> -->


		</vue-resizable>
	</div>
</template>


<style>
.draggable-component {
	position: absolute;
	z-index: 9;
	text-align: center;
	cursor: move;
}

.draggable-handle {
	padding: 10px;
	z-index: 10;
}
</style>
