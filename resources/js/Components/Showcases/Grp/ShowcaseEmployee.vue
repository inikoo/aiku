<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 14:00:48 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
-->

<script setup lang="ts">
import AppLogin from "@/Components/Forms/Fields/AppLogin.vue";
import { ref } from 'vue'
import Button from '@/Components/Elements/Buttons/Button.vue';
import { faEye, faEyeSlash } from "@fal";

const props = defineProps<{
  data: object;
}>();


const pin = [1, 3, 4, 56, 56, 778, 45];
const showPins = ref(false);

function toggleShowPins() {
  showPins.value = !showPins.value;
}
</script>

<template>
  <div class="h-fit grid grid-cols-3 gap-4 p-6">
    <!-- Grid Item 1 -->
    <div class="col-span-1">
      <div class="h-fit grid col-span-3 ring-1 ring-gray-300 shadow rounded-2xl p-6 gap-y-6">
        <AppLogin :route="{ name: 'grp.models.profile.app-login-qrcode' }" />
        <div class="mt-8 flex flex-col items-center gap-y-1">
          <div class="text-gray-400 italic">Don't have the app?</div>
          <a
            href=""
            target="_blank"
            class="text-blue-700 hover:underline flex items-center gap-x-2"
          >
            <FontAwesomeIcon
              icon="fab fa-android"
              class=""
              size="xl"
              fixed-width
              aria-hidden="true"
            />
            <div class="text-lg font-semibold leading-5">
              Download Android App
            </div>
          </a>
        </div>
      </div>
    </div>

    <!-- Grid Item 2 -->
    <div class="col-span-1">
      <div class="h-fit grid col-span-3 ring-1 ring-gray-300 shadow rounded-2xl p-6 gap-y-6">
        <div class="flex flex-col items-center gap-y-4">
          <!-- Pin Grid -->
          <div class="flex flex-wrap justify-center gap-2">
            <div
              v-for="(value, index) in pin"
              :key="index"
              class="w-12 h-12 flex items-center justify-center text-lg font-semibold border border-gray-300 rounded-md shadow-sm bg-gray-50"
            >
              {{ showPins ? value : 'X' }}
            </div>
          </div>
        </div>

        <!-- Additional Info -->
        <div class=" flex flex-col items-center gap-y-4">
          <div class="text-gray-400 italic">Show Clocking Machine Pin</div>

          <Button  @click="toggleShowPins" :label="showPins ? 'Hide Pins' : 'Show Pins'" :icon="showPins ? faEyeSlash : faEye" />
        </div>
      </div>
    </div>

    <!-- Grid Item 3 -->
    <div class="col-span-1 flex flex-col items-center justify-center">
      <!-- Placeholder for Future Content -->
    </div>
  </div>
</template>
