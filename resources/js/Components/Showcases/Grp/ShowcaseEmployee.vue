<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 14:00:48 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
-->

<script setup lang="ts">
import AppLogin from "@/Components/Forms/Fields/AppLogin.vue";
import { ref } from 'vue'
import Tag from 'primevue/tag';
import Image from '@/Components/Image.vue'
import { useFormatTime } from '@/Composables/useFormatTime'
import Button from '@/Components/Elements/Buttons/Button.vue';
import { faEye, faEyeSlash } from "@fal";

const props = defineProps<{
  data: {
    employee : any,
    pin : any
  };

}>();
console.log(props)


const showPins = ref(false);

function toggleShowPins() {
  showPins.value = !showPins.value;
}
</script>

<template>
<!--   <div class="h-fit grid grid-cols-3 gap-4 p-6">
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

    <div class="col-span-2">
      <div class="h-fit w-fit grid col-span-3 ring-1 ring-gray-300 shadow rounded-2xl p-6 gap-y-6">
        <div class="flex flex-col items-center gap-y-4">
          <div class="flex flex-wrap justify-center gap-2">
            <div
              v-for="(value, index) in Array.from(data.pin)"
              :key="index"
              class="w-12 h-12 flex items-center justify-center text-lg font-semibold border border-gray-300 rounded-md shadow-sm bg-gray-50"
            >
            
              {{ showPins ? value : 'X' }}
            </div>
          </div>
        </div>

        <div class=" flex flex-col items-center gap-y-4">
          <div class="text-gray-400 italic">Show Clocking Machine Pin</div>

          <Button  @click="toggleShowPins" :label="showPins ? 'Hide Pins' : 'Show Pins'" :icon="showPins ? faEyeSlash : faEye" />
        </div>
      </div>
    </div>
  </div> -->

  <div class="px-6 py-6 grid grid-cols-9 gap-x-8">
        <div class="col-span-6 ring-1 ring-gray-300 shadow rounded-2xl py-6 grid grid-cols-2 gap-y-4">
            <div class="flex flex-col gap-y-4 px-8">
                <div class="mx-auto w-fit aspect-square rounded-full overflow-hidden md:h-56" :src="'person.imageUrl'" alt="">
                    <Image :src="data?.employee?.data.avatar" />
                </div>
                
                <div class="col-span-4">
                    <div class="flex items-end gap-x-2">
                        <div class="font-semibold text-2xl">{{ data?.employee?.data.contact_name }}</div>
                        <div class="text-gray-400">
                            #{{ data?.employee?.data.id }} {{ data?.employee?.data.username }}
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="font-medium">
                            Description
                        </div>
                        <div v-if="data?.employee?.data.about" class="text-gray-500">
                            {{ data?.employee?.data.about }}
                        </div>
                        <div v-else class="text-gray-400 italic">
                            {{ 'No description yet' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section: Contact Information -->
            <div class="mt-6 border-l border-gray-300 pl-6 space-y-3">
                <div class="font-semibold">Contact Information</div>

                <div class="space-y-2">
                    <div class="grid grid-cols-3 gap-x-5 text-sm">
                        <div class="text-gray-400">
                            Contact Name
                        </div>
                        <div class="col-span-2 font-medium capitalize">
                            {{ data?.employee?.data.contact_name }}
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-x-5 text-sm">
                        <div class="text-gray-400">
                          Emergency Contact
                        </div>
                        <div class="col-span-2 font-medium capitalize">
                            {{ data?.employee?.data.emergency_contact || '-' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-x-5 text-sm">
                        <div class="text-gray-400">
                            Start Working
                        </div>
                        <div class="col-span-2 font-medium">
                            {{ useFormatTime(data?.employee?.data.employment_start_at) }}
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-x-5 text-sm">
                        <div class="text-gray-400">
                          State
                        </div>
                        <div class="col-span-2 font-medium">
                          <Tag  :value="data?.employee?.data.state"></Tag>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-x-5 text-sm">
                        <div class="text-gray-400">
                            Job Position
                        </div>
                        <div class="col-span-2 font-medium">
                            {{ data?.employee?.data.job_positions?.length }} 
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="h-fit w-fit grid col-span-3 ring-1 ring-gray-300 shadow rounded-2xl p-6 gap-y-6">
        <div class="flex flex-col items-center gap-y-4">
          <div class="flex flex-wrap justify-center gap-2">
            <div
              v-for="(value, index) in Array.from(data.pin)"
              :key="index"
              class="w-12 h-12 flex items-center justify-center text-lg font-semibold border border-gray-300 rounded-md shadow-sm bg-gray-50"
            >
            
              {{ showPins ? value : 'X' }}
            </div>
          </div>
        </div>

        <div class=" flex flex-col items-center gap-y-4">
          <div class="text-gray-400 italic">Show Clocking Machine Pin</div>

          <Button  @click="toggleShowPins" :label="showPins ? 'Hide Pins' : 'Show Pins'" :icon="showPins ? faEyeSlash : faEye" />
        </div>
      </div>


    </div>
</template>
