<script setup lang="ts">
import Drawer from 'primevue/drawer';
import { ref, inject } from 'vue';
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';
import Image from "@/Components/Image.vue";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faBars, faSignIn, faSignOut, faTimesCircle } from '@fas';
import { library } from '@fortawesome/fontawesome-svg-core'
import { faChevronCircleDown } from '@fal';
// Add icons to library
library.add(faBars, faSignIn, faSignOut, faTimesCircle);

const props = defineProps<{
    header: { logo?: { source: string } },
    menu: { data: Array<{ type: string, label: string, subnavs?: Array<{ title: string, link: { href: string, target: string } }> }> }
}>();

const visible = ref(false);
const isLoggedIn = inject('isPreviewLoggedIn', false)
const onLogout = inject('onLogout')
</script>

<template>
    <div>
        <button @click="visible = true">
            <FontAwesomeIcon :icon="faBars" class="text-xl" />
        </button>
        <Drawer v-model:visible="visible" :header="''">
            <template #closeicon>
                <FontAwesomeIcon :icon="faTimesCircle" @click="visible = false" />
            </template>
            <template #header>
                <img :src="header?.logo?.image?.source?.original" :alt="header?.logo?.alt" class="h-12" />
            </template>

            <div class="menu-container">
                <div class="menu-content">
                    <div v-for="(item, index) in props.menu" :key="index">
                        <Disclosure v-if="item.type === 'multiple'">
                            <DisclosureButton class="w-full text-left p-4 font-semibold text-gray-500 border-b-2">
                                <div class="w-full flex justify-between items-center">
                                    <div>{{ item.label }}</div>
                                    <div>
                                        <FontAwesomeIcon :icon="faChevronCircleDown" />
                                    </div>
                                </div>
                            </DisclosureButton>

                            <DisclosurePanel>
                                <div v-for="(submenu, indexSub) in item.subnavs" :key="indexSub" class="first:mt-2">
                                    <span v-if="submenu.title" :href="submenu?.link?.href" :target="submenu?.link?.target"
                                        class="p-4 text-sm font-semibold text-gray-500 block">{{ submenu.title }}</span>

                                    <div v-for="(menu, indexMenu) in submenu.links" :key="indexSub">

                                        <a :href="menu?.link?.href" :target="menu?.link?.target"
                                            class="p-2 text-sm font-semibold text-gray-600 block">- {{ menu.label }}</a>
                                    </div>
                                </div>
                            </DisclosurePanel>
                        </Disclosure>

                        <!-- Single link items -->
                        <div v-else class="py-4 px-5 border-b-2">
                            <a :href="item?.link?.href" :target="item?.link?.target" class="font-bold text-gray-500">
                                {{ item.label }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Login section -->
                <div class="login-section">
                    <a v-if="!isLoggedIn" href="/app" class="font-bold text-gray-500">
                        <FontAwesomeIcon :icon="faSignIn" class="mr-3"></FontAwesomeIcon>Login
                    </a>
                    <div v-else @click="onLogout()" class="font-bold text-red-500">
                        <FontAwesomeIcon :icon="faSignOut" class="mr-3"></FontAwesomeIcon>LogOut
                    </div>
                </div>
            </div>
        </Drawer>
    </div>
</template>

<style scoped>
.menu-container {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.menu-content {
    flex: 0 0 80%;
    overflow-y: auto;
}

.login-section {
    flex: 0 0 20%;
    display: flex;
    align-items: center;
    justify-content: start;
    border-top: 1px solid #e5e5e5;
}

.disclosure-button {
    padding: 1rem;
    font-weight: bold;
    color: #4B5563;
}

.disclosure-panel {
    padding: 0.5rem 1rem;
    background-color: #F9FAFB;
}
</style>
