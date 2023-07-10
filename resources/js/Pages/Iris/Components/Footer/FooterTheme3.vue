<script setup lang="ts">
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import {
    faFacebook,
    faInstagram,
    faTwitter,
    faGithub,
    faYoutube,
} from "@fortawesome/free-brands-svg-icons"
import { library } from "@fortawesome/fontawesome-svg-core"
import HyperLink from "../Fields/Hyperlink.vue"
import draggable from "vuedraggable"
import SocialMediaPicker from "../Fields/SocialMediaTools.vue"
import { ref,onMounted } from "vue"
import { get } from 'lodash'
library.add(faFacebook, faInstagram, faTwitter, faGithub, faYoutube)
const props = defineProps<{
    social: Object
    navigation: Object
    selectedColums: Function
    columSelected: Object
    saveItemTitle: Function
    saveTextArea: Function
    tool: Object
    saveLink: Function
    saveInfo: Function
    copyRight: Object
    copyRightSave: Function
    saveSocialmedia: Function
}>()

const listData = ref(props.navigation.filter((item)=> item.type == 'list')[0])
onMounted(() => {
  props.selectedColums(props.navigation.filter((item)=> item.type == 'list')[0]);
});

</script>

<template>
    <footer class="bg-slate-800">
        <div class="mx-auto max-w-7xl overflow-hidden px-6 py-20 sm:py-24 lg:px-8">
            <!-- Navigations -->
            <nav class="grid grid-cols-2 sm:flex sm:justify-center sm:space-x-12" aria-label="Footer">
                <draggable :list="get(listData,'data',[])" group="link" itemKey="id" @change="childLog" :class="[
                    tool.name === 'grab' ? 'cursor-grab' : 'cursor-pointer',
                    'text-gray-400 hover:text-gray-500 flex space-x-6',
                ]" :disabled="tool.name !== 'grab'">
                    <template #item="{ element: child, index: childIndex }">
                        <div>
                            <div v-if="tool.name !== 'grab'">
                                <HyperLink valueKeyLabel="name" valueKeyLink="href" :useDelete="true" :data="child"
                                    :save="(value) => props.saveLink({ parentId: navigation[0].id, ...value })"
                                    cssClass="px-4 sm:px-0 text-sm leading-6 text-gray-100 hover:text-gray-400" />
                            </div>
                            <div v-if="tool.name == 'grab'">
                                <span class="px-4 sm:px-0 text-sm leading-6 text-gray-100 hover:text-gray-400">{{ child.name
                                }}</span>
                            </div>
                        </div>

                    </template>
                </draggable>
            </nav>

            <!-- Social Media -->
            <div class="mt-10 flex justify-center space-x-10">
                <draggable :list="social" group="socialMedia" itemKey="id" :class="[
						tool.name === 'grab' ? 'cursor-grab' : 'cursor-pointer',
						'text-gray-400  flex space-x-6',
					]" :disabled="tool.name !== 'grab'">
						<template #item="{ element: child, index: childIndex }">
							<div>
								<span class="sr-only">{{ child.label }}</span>
								<SocialMediaPicker :modelValue="child.icon" cssClass="h-6 w-6" :data="child"
									:save="saveSocialmedia" />
							</div>
						</template>
					</draggable>
            </div>


            <div class="mt-10 flex justify-center text-center text-xs leading-5 text-gray-500">
                &copy; 2023 <span class="font-extrabold mx-1">
                    <HyperLink :useDelete="false" :data="copyRight" :save="copyRightSave" valueKeyLabel="label"
                        valueKeyLink="href" />
                </span>, Inc. All rights
                reserved.
            </div>
        </div>
    </footer>
</template>
