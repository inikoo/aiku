<script setup lang="ts">
import { watch, ref } from 'vue';

const props = withDefaults(defineProps<{
  items: {
      type: Array<any>,
      required: true,
    },

    command: {
      type: Function,
      required: true,
    },
}>(), {})

const selectedIndex =  ref(0)

  const onKeyDown = ({ event }) => {
      if (event.key === 'ArrowUp') {
        upHandler()
        return true
      }

      if (event.key === 'ArrowDown') {
        downHandler()
        return true
      }

      if (event.key === 'Enter') {
        enterHandler()
        return true
      }

      return false
    }

    const upHandler = () => {
      selectedIndex.value  = ((selectedIndex.value  + props.items.length) - 1) % props.items.length
    }

    const downHandler = () => {
      selectedIndex.value  = (selectedIndex.value  + 1) % props.items.length
    }

    const enterHandler = () => {
      selectItem(selectedIndex.value)
    }

    const selectItem = (index) => {
      const item = props.items[index]

      if (item) {
        props.command({ id: item })
      }
    }

    watch(()=>props.items , (newValue)=>{
      selectedIndex.value = 0
    })

    defineExpose({
      onKeyDown,
      upHandler,
      downHandler,
      selectItem,
      enterHandler
});

</script>

<template>
  <div class="dropdown-menu">
    <template v-if="items.length">
      <button
        :class="{ 'is-selected': index === selectedIndex }"
        v-for="(item, index) in items"
        :key="index"
        @click="selectItem(index)"
      >
        {{ item }}
      </button>
    </template>
    <div class="item" v-else>
      No result
    </div>
  </div>
</template>



<style lang="scss">
/* Dropdown menu */
.dropdown-menu {
  background: #fff;
  border: 1px solid var(--gray-1);
  border-radius: 0.7rem;
  box-shadow: var(--shadow);
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
  overflow: auto;
  padding: 0.5rem 0.5rem;
  position: relative;

  button {
    align-items: center;
    background-color: transparent;
    display: flex;
    gap: 0.25rem;
    text-align: left;
    width: 100%;
    padding: 0.1rem 0.3rem;

    &:hover,
    &:hover.is-selected {
      background-color: #F6F2FF;
      color: #6A00F5;
      padding: 0.1rem 0.3rem;
    }

    &.is-selected {
      background-color: #F6F2FF;
      color: #6A00F5;
      padding: 0.1rem 0.3rem;
    }
  }
}
</style>