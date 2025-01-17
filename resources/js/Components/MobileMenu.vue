<script setup lang="ts">
import Drawer from 'primevue/drawer';
import Accordion from 'primevue/accordion';
import AccordionPanel from 'primevue/accordionpanel';
import AccordionHeader from 'primevue/accordionheader';
import AccordionContent from 'primevue/accordioncontent';
import { ref } from 'vue';
import Image from "@/Components/Image.vue";

import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faBars } from '@fortawesome/free-solid-svg-icons'; // Ensure correct icon import

library.add(faBars);

const props = defineProps<{
   header: { logo?: { source: string } },
   menu: { data: Array<{ type: string, label: string }> }
}>();

const visible = ref(false);

</script>

<template>
    <div>
        <button @click="visible = true">
            <FontAwesomeIcon :icon="faBars" class="text-xl" />
        </button>
        <Drawer v-model:visible="visible" :header="''">
            <template #header>
                <img v-if="!props.header.logo"
                    src="https://d19ayerf5ehaab.cloudfront.net/assets/store-18687/18687-logo-1642004490.png"
                    alt="Ancient Wisdom Logo" class="h-12">
                <Image v-else :src="props.header.logo.source" class="h-12"></Image>
            </template>

            <Accordion>
                <template v-for="(item, index) in props.menu.data" :key="index">
                    <AccordionPanel v-if="item.type === 'multiple'" :value="index">
                        <AccordionHeader ><span class="font-bold text-gray-500">{{ item.label }}</span></AccordionHeader>
                        <AccordionContent>
                            <div v-for="(submenu, indexSub) in item.subnavs">
                                <div class="p-4 text-sm font-semibold text-gray-500">{{ submenu.title }}</div>
                            </div>
                        </AccordionContent>
                    </AccordionPanel>

                    <div v-else class='py-4 px-5 border-b-2'>
                        <div class="font-bold text-gray-500">{{item.label}}</div>
                    </div>
                </template>
            </Accordion>
        </Drawer>
    </div>
</template>
