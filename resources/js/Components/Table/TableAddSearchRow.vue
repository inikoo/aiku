<script setup>
import ButtonWithDropdown from "./ButtonWithDropdown.vue";
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSearch } from "@/../private/pro-regular-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faSearch)
import { ref } from "vue";

const props = defineProps({
  searchInputs: {
    type: Object,
    required: true,
  },

  hasSearchInputsWithoutValue: {
    type: Boolean,
    required: true,
  },

  onAdd: {
    type: Function,
    required: true,
  },
});

const dropdown = ref(null);

function enableSearch(key) {
  props.onAdd(key);
  dropdown.value.hide();
}
</script>

<template>
  <ButtonWithDropdown ref="dropdown" dusk="add-search-row-dropdown" :disabled="!hasSearchInputsWithoutValue"
    class="w-auto">
    <template #button>
      <div class="h-5 w-5 flex justify-center items-center">
        <FontAwesomeIcon icon="far fa-search" class="h-4 w-4 text-gray-400" aria-hidden="true" />
      </div>
    </template>

    <!-- The popup -->
    <div role="menu" aria-orientation="horizontal" aria-labelledby="add-search-input-menu" class="min-w-max">
      <button v-for="(searchInput, key) in searchInputs" :key="key" :dusk="`add-search-row-${searchInput.key}`"
        class="text-left w-full px-4 py-2 text-sm text-gray-700 capitalize hover:bg-gray-100 hover:text-gray-900"
        role="menuitem" @click.prevent="enableSearch(searchInput.key)">
        {{ searchInput.label }}
      </button>
    </div>
  </ButtonWithDropdown>
</template>
