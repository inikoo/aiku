<script setup>
import ButtonWithDropdown from "./ButtonWithDropdown.vue"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faYinYang, faUserCircle, } from "@/../private/pro-light-svg-icons"
import { faEye } from "@/../private/pro-solid-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faYinYang, faUserCircle, faEye)

const props = defineProps({
  columns: {
    type: Object,
    required: true,
  },

  hasHiddenColumns: {
    type: Boolean,
    required: true,
  },

  onChange: {
    type: Function,
    required: true,
  },
});
</script>

<template>
  <ButtonWithDropdown placement="bottom-end" dusk="columns-dropdown" :active="hasHiddenColumns">
    <!-- Buttons beside of Search Table (Filter column, etc.) -->
    <template #button>
      <FontAwesomeIcon icon="fas fa-eye" aria-hidden="true" :class="[hasHiddenColumns ? 'text-green-400' : 'text-gray-400', 'h-5 w-5']" />
    </template>

    <!-- The popup -->
    <div role="menu" aria-orientation="horizontal" aria-labelledby="toggle-columns-menu" class="min-w-max">
      <div class="px-2">
        <ul class="divide-y divide-gray-200">
          <li v-for="(column, key) in props.columns" v-show="column.can_be_hidden" :key="key"
            class="py-2 flex items-center justify-between">
            <p class="text-sm text-gray-900 capitalize">
              {{ typeof column.label == 'string' ? column.label : '' }}
              <FontAwesomeIcon v-if="(typeof column.label != 'string')" class="text-gray-700" :icon="column.label" aria-hidden="true" />
            </p>

            <!-- Switch Toggle -->
            <button type="button"
              class="ml-4 relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500"
              :class="[column.hidden ? 'bg-gray-200' : 'bg-green-500']"
              :aria-pressed="!column.hidden" :aria-labelledby="`toggle-column-${column.key}`"
              :aria-describedby="`toggle-column-${column.key}`" :dusk="`toggle-column-${column.key}`"
              @click.prevent="onChange(column.key, column.hidden)">
              <span class="sr-only">Column status</span>
              <span aria-hidden="true" :class="{
                'translate-x-5': !column.hidden,
                'translate-x-0': column.hidden,
              }"
                class="inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200" />
            </button>
          </li>
        </ul>
      </div>
    </div>
  </ButtonWithDropdown>
</template>