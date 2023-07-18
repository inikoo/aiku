<script setup>
import { ref } from "vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"


const props = defineProps({
  fontDecorator: {
    type: Object,
    default: {}
  },
  changeText: {
    type: Function,
    required: true
  }
})

const setText = (value) => {
  const updatedFontDecorator = { ...props.fontDecorator };

  if (value === 'bold') {
    if (updatedFontDecorator.fontWeight) {
      delete updatedFontDecorator.fontWeight;
    } else {
      updatedFontDecorator.fontWeight = 500;
    }
  }

  if (value === 'underline') {
    if (updatedFontDecorator.textDecoration) {
      delete updatedFontDecorator.textDecoration;
    } else {
      updatedFontDecorator.textDecoration = 'underline';
    }
  }

  if (value === 'strikeThrough') {
    if (updatedFontDecorator.textDecoration) {
      delete updatedFontDecorator.textDecoration;
    } else {
      updatedFontDecorator.textDecoration = 'line-through';
    }
  }

  if (value === 'italic') {
    if (updatedFontDecorator.fontStyle) {
      delete updatedFontDecorator.fontStyle;
    } else {
      updatedFontDecorator.fontStyle = 'italic';
    }
  }

  props.changeText(updatedFontDecorator);
};

</script>


<template>
	<div class="flex h-full gap-1 justify-center align-middle">
		
			<button
                @click="(e)=>{e.stopPropagation(),setText('bold')}"
				type="button"
				class=" p-1.5   hover:bg-indigo-500 hover:text-white focus:ring-4 focus:outline-none  font-medium rounded-lg text-sm p-1 text-center inline-flex items-center  dark:border-indigo-500 dark:text-blue-500 dark:hover:text-white  dark:hover:bg-indigo-500">
				<font-awesome-icon :icon="['fass', 'bold']" />
				<span class="sr-only">Bold</span>
			</button>

            <button
                @click="(e)=>{e.stopPropagation(),setText('underline')}"
				type="button"
				class=" p-1.5   hover:bg-blue-700 hover:text-white focus:ring-4 focus:outline-none  font-medium rounded-lg text-sm p-1 text-center inline-flex items-center  dark:border-indigo-500 dark:text-blue-500 dark:hover:text-white  dark:hover:bg-indigo-500">
				<font-awesome-icon :icon="['fas', 'underline']" />
				<span class="sr-only">underline</span>
			</button>

            <button
                @click="(e)=>{e.stopPropagation(),setText('strikeThrough')}"
				type="button"
				class=" p-1.5   hover:bg-indigo-500 hover:text-white focus:ring-4 focus:outline-none  font-medium rounded-lg text-sm p-1 text-center inline-flex items-center  dark:border-indigo-500 dark:text-blue-500 dark:hover:text-white  dark:hover:bg-indigo-500">
				<font-awesome-icon :icon="['fas', 'strikethrough']" />
				<span class="sr-only">strike through</span>
			</button>

            <button
                @click="(e)=>{e.stopPropagation(),setText('italic')}"
				type="button"
				class=" p-1.5   hover:bg-indigo-500 hover:text-white focus:ring-4 focus:outline-none  font-medium rounded-lg text-sm p-1 text-center inline-flex items-center  dark:border-indigo-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-indigo-500">
				<font-awesome-icon :icon="['fas', 'italic']" />
				<span class="sr-only">Italic</span>
			</button>
		
	</div>
</template>
