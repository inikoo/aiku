<script setup lang="ts">
import { ref, onMounted } from 'vue'
const props = defineProps<{
  data: Object,
  save : Function
  cssClass : {
    type : string,
		default: ''
  }
}>()

const editMode = ref(false)
const inputValue = ref(props.data.data)
const inputRef = ref<HTMLInputElement | null>(null)

const changeEditMode = () => {
  editMode.value = true
  setTimeout(() => {
    if (inputRef.value) {
      inputRef.value.focus()
    }
  }, 0)
}

const handleInputBlur = () => {
  editMode.value = false
  props.save({colum : {...props.data}, value :inputValue.value })
}

onMounted(() => {
  if (editMode.value && inputRef.value) {
    inputRef.value.focus()
  }
})
</script>

<template>
  <div>
    <template v-if="!editMode" style="white-space : pre-warp">
        <div class="parent">
    <div @click="changeEditMode" :class="cssClass">{{ data.data }}</div>
  
</div>
    </template>
    <template v-else>
        <textarea ref="inputRef"
        v-model="inputValue"
        @blur="handleInputBlur"
        class="w-full border h-full"
        :maxlength="500"
        style="min-height: 218px; max-height: 300px;"
        />
    </template>
  </div>
</template>

