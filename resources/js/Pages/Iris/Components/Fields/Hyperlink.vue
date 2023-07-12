<script setup lang="ts">
import { ref, onMounted } from "vue"
import { Popover, PopoverButton, PopoverPanel } from "@headlessui/vue"
import { faLink, faEdit, faTrash } from "@/../private/pro-solid-svg-icons"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
library.add(faLink, faEdit, faTrash)
const props = defineProps({
  data: {
    type: Object,
    default: () => ({})
  },
  save: {
    type: Function,
    default: () => {}
  },
  valueKeyLabel: {
    type: String,
    default: ''
  },
  valueKeyLink: {
    type: String,
    default: ''
  },
  useDelete: {
    type: Boolean,
    default: true
  },
  useLink: {
    type: Boolean,
    default: true
  },
  cssClass: {
    type: String,
    default: ''
  }
});

const editMode = ref({ edit : false, type : null})
const inputValueName = ref(props.data[props.valueKeyLabel])
const inputValueLink = ref(props.data[props.valueKeyLink])
const inputRef = ref<HTMLInputElement | null>(null)
const propsInput = ref({ model: inputValueName, class: 'w-full border', placeholder : 'label' });

const changeEditMode = (name) => {

	if (name == 'delete') {
		props.save({colum : {...props.data}, value : '' , type : 'delete' })
	} else {
		editMode.value = { edit: true, type: name };

		propsInput.value = {
			...propsInput.value,
			model: name == 'name' ? inputValueName : inputValueLink,
			placeholder: name == 'name' ? 'label' : 'http//:'
		};


		setTimeout(() => {
			if (inputRef.value) {
				inputRef.value.focus();
			}
		}, 0);
	}
};


const handleInputBlur = () => {
  props.save({colum : {...props.data}, value :editMode.value.type == 'name' ? inputValueName.value : inputValueLink.value   , type : editMode.value.type })
  editMode.value = { edit : false, type : null}
}

onMounted(() => {
  if (editMode.value && inputRef.value) {
    inputRef.value.focus()
  }
})


</script>
<template>
	<template v-if="!editMode.edit">
		<div>
			<Popover v-slot="{ open }" class="relative">
				<PopoverButton>
					<span :class="cssClass">{{
						data[valueKeyLabel]
					}}</span>
				</PopoverButton>

				<transition name="fade">
					<PopoverPanel
						:ref="popoverRef"
						v-show="open"
						class="bg-white z-10 mt-3 transform px-4 sm:px-0 absolute"
						style="margin-top: 0.75rem; top: -4.8rem">
						<div
							class="absolute bg-white overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5">
							<div class="relative gridbg-white lg:grid-cols-2">
								<div
									class="flex items-center space-x-2 p-2 transition duration-150 ease-in-out hover:bg-gray-100 focus:outline-none focus-visible:ring focus-visible:ring-orange-500 focus-visible:ring-opacity-50">
									<div v-if="useLink"
                                    @click="changeEditMode('link')"
										class="flex-shrink-0 h-10 w-10 sm:h-12 sm:w-12 bg-gray-200 border border-gray-300 rounded-md flex items-center justify-center" >
										<FontAwesomeIcon icon="fa-link" />
									</div>
									<div 
										@click="changeEditMode('name')"
										class="flex-shrink-0 h-10 w-10 sm:h-12 sm:w-12 bg-gray-200 border border-gray-300 rounded-md flex items-center justify-center" style="color: black;">
										<FontAwesomeIcon :icon="['fas', 'edit']" />
									</div>
									<div v-if="useDelete"
										@click="changeEditMode('delete')"
										class="flex-shrink-0 h-10 w-10 sm:h-12 sm:w-12 bg-gray-200 border border-gray-300 text-red rounded-md flex items-center justify-center" style="color: black;">
										<font-awesome-icon :icon="['fass', 'trash']" />
									</div>
								</div>
							</div>
						</div>
					</PopoverPanel>
				</transition>
			</Popover>
		</div>
	</template>
	<template v-else>
		<input ref="inputRef" @blur="handleInputBlur" class="w-full border" v-model="propsInput.model" :placeholder="propsInput.placeholder" style="color: black;"/>
	</template>
</template>

<style>
.fade-enter-active {
	transition: opacity 0.3s;
}
</style>
