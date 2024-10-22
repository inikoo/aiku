      <!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

  <script setup lang="ts">
import { faCube, faLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref } from "vue"
import Button from '@/Components/Elements/Buttons/Button.vue';
import Editor from "@/Components/Forms/Fields/BubleTextEditor/EditorV2.vue"
import { getStyles } from "@/Composables/styles";

library.add(faCube, faLink)

const props = defineProps<{
    modelValue: any
    isEditable?: boolean
}>()


const emits = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
    (e: 'autoSave'): void
}>()

console.log('cta_aurora', props.modelValue)
</script>
  
  <template>
    <div class="bg-white" :style="getStyles(modelValue.container.properties)">
        <div class="w-full">
            <div class="relative isolate overflow-hidden bg-gray-900 px-6 py-24 text-center shadow-2xl sm:px-16">
                <Editor :editable="isEditable" v-model="modelValue.title"
                    @update:modelValue="() => emits('autoSave')" />
                <Editor :editable="isEditable" v-model="modelValue.text" @update:modelValue="() => emits('autoSave')" />

                <div class="flex justify-center">
                    <div typeof="button" :style="getStyles(modelValue.button.container.properties)"
                        class="mt-10 flex items-center justify-center w-64 mx-auto gap-x-6">
                        <Editor :editable="isEditable" v-model="modelValue.button.text"
                            @update:modelValue="() => emits('autoSave')" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>