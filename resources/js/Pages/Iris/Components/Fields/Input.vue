<script setup lang="ts">
import { ref, onMounted } from 'vue'
const props = defineProps<{
  data: Object,
  save : Function
  keyValue:String
  cssClass:any
}>()
const editMode = ref(false)
const inputValue = ref(props.data[props.keyValue])
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
  props.save({column : {...props.data}, value :inputValue.value })
}

onMounted(() => {
  if (editMode.value && inputRef.value) {
    inputRef.value.focus()
  }
})
</script>

<template>
  <div>
    <template v-if="!editMode">
      <div @click="changeEditMode"> <h3 :class="cssClass">{{ data[keyValue] }}</h3></div>
    </template>
    <template v-else>
      <input
        ref="inputRef"
        v-model="inputValue"
        @blur="handleInputBlur"
        class="w-full border"
      />
    </template>
  </div>
</template>


