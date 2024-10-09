<script setup lang="ts">
import { ref, watch } from 'vue'

import Button from '@/Components/Elements/Buttons/Button.vue';
import Editor from "@/Components/Forms/Fields/BubleTextEditor/Editor.vue"
import draggable from "vuedraggable";
import { v4 as uuidv4 } from 'uuid';
import ContextMenu from 'primevue/contextmenu';
/* import Accordion from 'primevue/accordion';
import AccordionPanel from 'primevue/accordionpanel';
import AccordionHeader from 'primevue/accordionheader';
import AccordionContent from 'primevue/accordioncontent'; */
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'

import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faShieldAlt, faPlus, faTrash, faAngleUp, faAngleDown } from "@fas"
import { faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn } from "@fortawesome/free-brands-svg-icons";
import { faBars } from '@fal'

library.add(faFacebookF, faInstagram, faTiktok, faPinterest, faYoutube, faLinkedinIn, faShieldAlt, faBars, faPlus, faTrash)

const props = defineProps<{
    modelValue: object,
    keyTemplate: String
    previewMode: Boolean
    colorThemed?: Object
}>();

const selectedColor = props.colorThemed?.color
const toogle = ['bold', 'italic', 'underline', 'link', 'undo', 'redo']
const editable = ref(!props.previewMode)
const selectedData = ref(null)
const selectedIndex = ref(null)
const selectedColumn = ref(null)
const editKey = ref(uuidv4())
const menu = ref();
const subMenu = ref();
const ColumnMenu = ref();
const Menuitems = ref([
    {
        label: 'Sub Menu',
        icon: 'fas fa-plus',
        command: () => {
            addSubmenu()
        }
    },
    {
        label: 'Delete',
        icon: 'fas fa-trash',
        command: () => {
            deleteMenu()
        }
    }
]);
const subMenuitems = ref([
    {
        label: 'Delete',
        icon: 'fas fa-trash',
        command: () => {
            deleteSubMenu()
        }
    }
]);

const onDrag = () => {
    editable.value = false
    editKey.value = uuidv4()
}

const onDrop = () => {
    editable.value = true
    editKey.value = uuidv4()

}

const addSubmenu = () => {
    if (selectedData.value.data) {
        selectedData.value.data.push(
            {
                name: "New Sub Menu",
                id: uuidv4(),
            },
        )
    } else {
        selectedData.value.data = [
            {
                name: "New Sub Menu",
                id: uuidv4(),
            }
        ]
    }

}

const deleteMenu = () => {
    selectedColumn.value.splice(selectedIndex.value, 1)
}

const deleteSubMenu = () => {
    selectedData.value.data.splice(selectedIndex.value, 1)
}

const onRightClickMenu = (event: Object, data: Object, column: Object, index: Number) => {
    selectedData.value = data;
    selectedIndex.value = index,
        selectedColumn.value = column
    menu.value.show(event);
};

const onRightClickSubMenu = (event: Object, data: Object, column: Object, index: Number) => {
    selectedData.value = data;
    selectedIndex.value = index,
        selectedColumn.value = column
    subMenu.value.show(event);
};

watch(() => props.previewMode, (newStatus, oldStatus) => {
    editable.value = !newStatus
});

</script>

<template>
    <div id="app" class="py-24 md:px-7" :style="{ backgroundColor: selectedColor[0], color: selectedColor[1] }">
        <div class="">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 md:gap-8">
                <div class="px-4 md:px-0 grid gap-y-2 md:gap-y-6 h-fit">
                    <draggable :list="modelValue.column['column_1']['data']" group="row" itemKey="id" :animation="200"
                        handle=".handle" @start="onDrag" @end="onDrop"
                        class="px-4 md:px-0 grid grid-cols-1 gap-y-2 md:gap-y-6 h-fit">
                        <template #item="{ element: item, index: index }">
                            <div>
                                <div
                                    class="hidden md:block grid grid-cols-1 md:cursor-default space-y-1 border-b pb-2 md:border-none">
                                    <div class="flex">
                                        <FontAwesomeIcon icon="fal fa-bars" v-if="!previewMode"
                                            class="handle text-xl text-white cursor-grab pr-3 mr-2" />
                                        <div class="w-full"
                                            @contextmenu="onRightClickMenu($event, item, modelValue.column['column_1']['data'], index)">
                                            <span class="text-xl font-semibold w-fit leading-6">
                                                <Editor v-model="item.name" :toogle="[]" :editable="editable"
                                                    :key="editKey" />
                                            </span>
                                        </div>
                                        <ContextMenu ref="menu" :model="Menuitems">
                                            <template #itemicon="item">
                                                <FontAwesomeIcon :icon="item.item.icon" />
                                            </template>
                                        </ContextMenu>
                                    </div>
                                    <draggable :list="item.data" group="sub-row" itemKey="id" :animation="200"
                                        handle=".handle-sub" @start="onDrag" @end="onDrop">
                                        <template #item="{ element: sub, index: subIndex }">
                                            <ul class="hidden md:block space-y-1">
                                                <li>
                                                    <div class="flex items-center">
                                                        <FontAwesomeIcon icon="fal fa-bars" v-if="!previewMode"
                                                            class="handle-sub text-sm text-white cursor-grab pr-3 mr-2" />
                                                        <div class="w-full"
                                                            @contextmenu="onRightClickSubMenu($event, item, modelValue.column['column_1']['data'], subIndex)">
                                                            <span class="text-sm block">
                                                                <Editor v-model="sub.name" :toogle="['link']"
                                                                    :editable="editable" :key="editKey" />
                                                            </span>
                                                        </div>
                                                        <ContextMenu ref="subMenu" :model="subMenuitems">
                                                            <template #itemicon="item">
                                                                <FontAwesomeIcon :icon="item.item.icon" />
                                                            </template>
                                                        </ContextMenu>
                                                    </div>
                                                </li>
                                            </ul>
                                        </template>
                                    </draggable>
                                </div>
                                <div class="block md:hidden">
                                    <Disclosure v-slot="{ open }">
                                        <DisclosureButton
                                            class="grid grid-cols-1 md:cursor-default space-y-1 border-b pb-2 md:border-none w-full">
                                            <div class="flex justify-between">
                                                <div class="flex ">
                                                    <FontAwesomeIcon icon="fal fa-bars" v-if="!previewMode"
                                                        class="handle text-xl text-white cursor-grab pr-3 mr-2" />
                                                    <div class="w-fit"
                                                        @contextmenu="onRightClickMenu($event, item, modelValue.column['column_1']['data'], index)">
                                                        <span class="text-xl font-semibold leading-6">
                                                            <Editor v-model="item.name" :toogle="[]"
                                                                :editable="editable" :key="editKey" />
                                                        </span>
                                                    </div>
                                                    <ContextMenu ref="menu" :model="Menuitems">
                                                        <template #default="{ item }">
                                                            <FontAwesomeIcon :icon="item.item.icon" />
                                                        </template>
                                                    </ContextMenu>
                                                </div>
                                                <div>
                                                    <FontAwesomeIcon :icon="open ? faAngleDown : faAngleUp"
                                                        class="w-3 h-3" />
                                                </div>
                                            </div>
                                        </DisclosureButton>

                                        <DisclosurePanel>
                                            <div>
                                                <draggable :list="item.data" group="sub-row" itemKey="id"
                                                    :animation="200" handle=".handle-sub" @start="onDrag" @end="onDrop">
                                                    <template #item="{ element: sub, index: subIndex }">
                                                        <ul class="block space-y-1">
                                                            <li>
                                                                <div class="flex items-center">
                                                                    <FontAwesomeIcon icon="fal fa-bars"
                                                                        v-if="!previewMode"
                                                                        class="handle-sub text-sm text-white cursor-grab pr-3 mr-2" />
                                                                    <div class="w-full"
                                                                        @contextmenu="onRightClickSubMenu($event, item, modelValue.column['column_1']['data'], subIndex)">
                                                                        <span class="text-sm block">
                                                                            <Editor v-model="sub.name"
                                                                                :toogle="['link']" :editable="editable"
                                                                                :key="editKey" />
                                                                        </span>
                                                                    </div>
                                                                    <ContextMenu ref="subMenu" :model="subMenuitems">
                                                                        <template #default="{ item }">
                                                                            <FontAwesomeIcon :icon="item.item.icon" />
                                                                        </template>
                                                                    </ContextMenu>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </template>
                                                </draggable>
                                            </div>
                                        </DisclosurePanel>
                                    </Disclosure>
                                </div>
                            </div>
                        </template>
                    </draggable>
                </div>


                <div class="px-4 md:px-0 grid gap-y-2 md:gap-y-6 h-fit">
                    <draggable :list="modelValue.column['column_2']['data']" group="row" itemKey="id" :animation="200"
                        handle=".handle" @start="onDrag" @end="onDrop"
                        class="px-4 md:px-0 grid grid-cols-1 gap-y-2 md:gap-y-6 h-fit">
                        <template #item="{ element: item, index: index }">
                            <div>
                                <div
                                    class="hidden md:block grid grid-cols-1 md:cursor-default space-y-1 border-b pb-2 md:border-none">
                                    <div class="flex">
                                        <FontAwesomeIcon icon="fal fa-bars" v-if="!previewMode"
                                            class="handle text-xl text-white cursor-grab pr-3 mr-2" />
                                        <div class="w-full"
                                            @contextmenu="onRightClickMenu($event, item, modelValue.column['column_2']['data'], index)">
                                            <span class="text-xl font-semibold w-fit leading-6">
                                                <Editor v-model="item.name" :toogle="[]" :editable="editable"
                                                    :key="editKey" />
                                            </span>
                                        </div>
                                        <ContextMenu ref="menu" :model="Menuitems">
                                            <template #itemicon="item">
                                                <FontAwesomeIcon :icon="item.item.icon" />
                                            </template>
                                        </ContextMenu>
                                    </div>
                                    <draggable :list="item.data" group="sub-row" itemKey="id" :animation="200"
                                        handle=".handle-sub" @start="onDrag" @end="onDrop">
                                        <template #item="{ element: sub, index: subIndex }">
                                            <ul class="hidden md:block space-y-1">
                                                <li>
                                                    <div class="flex items-center">
                                                        <FontAwesomeIcon icon="fal fa-bars" v-if="!previewMode"
                                                            class="handle-sub text-sm text-white cursor-grab pr-3 mr-2" />
                                                        <div class="w-full"
                                                            @contextmenu="onRightClickSubMenu($event, item, modelValue.column['column_2']['data'], subIndex)">
                                                            <span class="text-sm block">
                                                                <Editor v-model="sub.name" :toogle="['link']"
                                                                    :editable="editable" :key="editKey" />
                                                            </span>
                                                        </div>
                                                        <ContextMenu ref="subMenu" :model="subMenuitems">
                                                            <template #itemicon="item">
                                                                <FontAwesomeIcon :icon="item.item.icon" />
                                                            </template>
                                                        </ContextMenu>
                                                    </div>
                                                </li>
                                            </ul>
                                        </template>
                                    </draggable>
                                </div>

                                <div class="block md:hidden">
                                    <Disclosure v-slot="{ open }">
                                        <DisclosureButton
                                            class="grid grid-cols-1 md:cursor-default space-y-1 border-b pb-2 md:border-none w-full">
                                            <div class="flex justify-between">
                                                <div class="flex ">
                                                    <FontAwesomeIcon icon="fal fa-bars" v-if="!previewMode"
                                                        class="handle text-xl text-white cursor-grab pr-3 mr-2" />
                                                    <div class="w-fit"
                                                        @contextmenu="onRightClickMenu($event, item, modelValue.column['column_2']['data'], index)">
                                                        <span class="text-xl font-semibold leading-6">
                                                            <Editor v-model="item.name" :toogle="[]"
                                                                :editable="editable" :key="editKey" />
                                                        </span>
                                                    </div>
                                                    <ContextMenu ref="menu" :model="Menuitems">
                                                        <template #default="{ item }">
                                                            <FontAwesomeIcon :icon="item.item.icon" />
                                                        </template>
                                                    </ContextMenu>
                                                </div>
                                                <div>
                                                    <FontAwesomeIcon :icon="open ? faAngleDown : faAngleUp"
                                                        class="w-3 h-3" />
                                                </div>
                                            </div>
                                        </DisclosureButton>

                                        <DisclosurePanel>
                                            <div>
                                                <draggable :list="item.data" group="sub-row" itemKey="id"
                                                    :animation="200" handle=".handle-sub" @start="onDrag" @end="onDrop">
                                                    <template #item="{ element: sub, index: subIndex }">
                                                        <ul class="block space-y-1">
                                                            <li>
                                                                <div class="flex items-center">
                                                                    <FontAwesomeIcon icon="fal fa-bars"
                                                                        v-if="!previewMode"
                                                                        class="handle-sub text-sm text-white cursor-grab pr-3 mr-2" />
                                                                    <div class="w-full"
                                                                        @contextmenu="onRightClickSubMenu($event, item, modelValue.column['column_2']['data'], subIndex)">
                                                                        <span class="text-sm block">
                                                                            <Editor v-model="sub.name"
                                                                                :toogle="['link']" :editable="editable"
                                                                                :key="editKey" />
                                                                        </span>
                                                                    </div>
                                                                    <ContextMenu ref="subMenu" :model="subMenuitems">
                                                                        <template #default="{ item }">
                                                                            <FontAwesomeIcon :icon="item.item.icon" />
                                                                        </template>
                                                                    </ContextMenu>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </template>
                                                </draggable>
                                            </div>
                                        </DisclosurePanel>
                                    </Disclosure>
                                </div>
                            </div>
                        </template>
                    </draggable>
                </div>

                <div class="px-4 md:px-0 grid gap-y-2 md:gap-y-6 h-fit">
                    <draggable :list="modelValue.column['column_3']['data']" group="row" itemKey="id" :animation="200"
                        handle=".handle" @start="onDrag" @end="onDrop"
                        class="hidden md:block px-4 md:px-0 grid grid-cols-1 gap-y-2 md:gap-y-6 h-fit">
                        <template #item="{ element: item, index: index }">
                            <div>
                                <div class="grid grid-cols-1 md:cursor-default space-y-1 border-b pb-2 md:border-none">
                                    <div class="flex">
                                        <FontAwesomeIcon icon="fal fa-bars" v-if="!previewMode"
                                            class="handle text-xl text-white cursor-grab pr-3 mr-2" />
                                        <div class="w-full"
                                            @contextmenu="onRightClickMenu($event, item, modelValue.column['column_2']['data'], index)">
                                            <span class="text-xl font-semibold w-fit leading-6">
                                                <Editor v-model="item.name" :toogle="[]" :editable="editable"
                                                    :key="editKey" />
                                            </span>
                                        </div>
                                        <ContextMenu ref="menu" :model="Menuitems">
                                            <template #itemicon="item">
                                                <FontAwesomeIcon :icon="item.item.icon" />
                                            </template>
                                        </ContextMenu>
                                    </div>
                                    <draggable :list="item.data" group="sub-row" itemKey="id" :animation="200"
                                        handle=".handle-sub" @start="onDrag" @end="onDrop">
                                        <template #item="{ element: sub, index: subIndex }">
                                            <ul class="hidden md:block space-y-1">
                                                <li>
                                                    <div class="flex items-center">
                                                        <FontAwesomeIcon icon="fal fa-bars" v-if="!previewMode"
                                                            class="handle-sub text-sm text-white cursor-grab pr-3 mr-2" />
                                                        <div class="w-full"
                                                            @contextmenu="onRightClickSubMenu($event, item, modelValue.column['column_2']['data'], subIndex)">
                                                            <span class="text-sm block">
                                                                <Editor v-model="sub.name" :toogle="['link']"
                                                                    :editable="editable" :key="editKey" />
                                                            </span>
                                                        </div>
                                                        <ContextMenu ref="subMenu" :model="subMenuitems">
                                                            <template #itemicon="item">
                                                                <FontAwesomeIcon :icon="item.item.icon" />
                                                            </template>
                                                        </ContextMenu>
                                                    </div>
                                                </li>
                                            </ul>
                                        </template>
                                    </draggable>
                                </div>

                                <div class="block md:hidden">
                                    <Disclosure v-slot="{ open }">
                                        <DisclosureButton
                                            class="grid grid-cols-1 md:cursor-default space-y-1 border-b pb-2 md:border-none w-full">
                                            <div class="flex justify-between">
                                                <div class="flex ">
                                                    <FontAwesomeIcon icon="fal fa-bars" v-if="!previewMode"
                                                        class="handle text-xl text-white cursor-grab pr-3 mr-2" />
                                                    <div class="w-fit"
                                                        @contextmenu="onRightClickMenu($event, item, modelValue.column['column_2']['data'], index)">
                                                        <span class="text-xl font-semibold leading-6">
                                                            <Editor v-model="item.name" :toogle="[]"
                                                                :editable="editable" :key="editKey" />
                                                        </span>
                                                    </div>
                                                    <ContextMenu ref="menu" :model="Menuitems">
                                                        <template #default="{ item }">
                                                            <FontAwesomeIcon :icon="item.item.icon" />
                                                        </template>
                                                    </ContextMenu>
                                                </div>
                                                <div>
                                                    <FontAwesomeIcon :icon="open ? faAngleDown : faAngleUp"
                                                        class="w-3 h-3" />
                                                </div>
                                            </div>
                                        </DisclosureButton>

                                        <DisclosurePanel>
                                            <div>
                                                <draggable :list="item.data" group="sub-row" itemKey="id"
                                                    :animation="200" handle=".handle-sub" @start="onDrag" @end="onDrop">
                                                    <template #item="{ element: sub, index: subIndex }">
                                                        <ul class="block space-y-1">
                                                            <li>
                                                                <div class="flex items-center">
                                                                    <FontAwesomeIcon icon="fal fa-bars"
                                                                        v-if="!previewMode"
                                                                        class="handle-sub text-sm text-white cursor-grab pr-3 mr-2" />
                                                                    <div class="w-full"
                                                                        @contextmenu="onRightClickSubMenu($event, item, modelValue.column['column_2']['data'], subIndex)">
                                                                        <span class="text-sm block">
                                                                            <Editor v-model="sub.name"
                                                                                :toogle="['link']" :editable="editable"
                                                                                :key="editKey" />
                                                                        </span>
                                                                    </div>
                                                                    <ContextMenu ref="subMenu" :model="subMenuitems">
                                                                        <template #default="{ item }">
                                                                            <FontAwesomeIcon :icon="item.item.icon" />
                                                                        </template>
                                                                    </ContextMenu>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </template>
                                                </draggable>
                                            </div>
                                        </DisclosurePanel>
                                    </Disclosure>
                                </div>
                            </div>
                        </template>
                    </draggable>
                </div>

                <div
                    class="md:hidden mb-6 md:mb-5 bg-[#9c7c64] md:bg-transparent text-center md:text-left pt-4 pb-6 space-y-4 md:py-0 md:space-y-0">
                    <h2 class="text-xl tracking-wider font-semibold md:mt-8 md:mb-4">Get Social with Us!</h2>
                    <div class="flex md:space-x-6 md:mb-4 justify-around md:justify-start">
                        <a v-for="item of modelValue.socialData" target="_blank"  :key="item.icon" :href="item.link"><font-awesome-icon :icon="item.icon" class="text-2xl" /></a>
                    </div>
                </div>

                <div class="flex flex-col flex-col-reverse gap-y-6 md:block">
                    <div>
                        <div class="flex flex-wrap -mx-4">
                            <div v-for="payment in modelValue.PaymentData.data" :key="payment.key"
                                class="w-full md:w-1/3 px-4 mb-8">
                                <div class="flex items-center justify-center md:justify-start space-x-4">
                                    <img :src="payment.image" :alt="payment.name" class="px-1 h-4">
                                </div>
                            </div>
                        </div>


                        <address
                            class="mt-10 md:mt-0 not-italic mb-4 text-center md:text-left text-xs md:text-sm text-gray-300">
                            <Editor v-model="modelValue.column.column_4.data.textBox1"
                                :toogle="[...toogle, 'color', 'highlight']" :editable="editable" />
                        </address>

                        <div class="flex justify-center gap-x-8 text-gray-300 md:block">
                            <Editor v-model="modelValue.column.column_4.data.textBox2"
                                :toogle="[...toogle, 'color', 'highlight']" :editable="editable" />
                        </div>
                        <!-- Section: Get Social With Us -->
                        <div
                            class="hidden md:block mb-6 md:mb-5 bg-[#9c7c64] md:bg-transparent text-center md:text-left pt-4 pb-6 space-y-4 md:py-0 md:space-y-0">
                            <h2 class="text-xl tracking-wider font-semibold md:mt-8 md:mb-4">Get Social with Us!</h2>
                            <div class="flex md:space-x-6 md:mb-4 justify-around md:justify-start">
                                <a v-for="item of modelValue.socialData" :key="item.icon" target="_blank" 
                                    :href="item.link"><font-awesome-icon :icon="item.icon" class="text-2xl" /></a>
                            </div>
                        </div>
                    </div>

                    <div
                        class="border-b border-gray-500 md:border-none flex items-center space-x-2 px-5 pb-4 md:pb-0 md:px-0">
                        <i class="text-4xl md:text-3xl fab fa-whatsapp text-green-500"></i>
                        <span class="w-10/12 md:w-full md:text-sm">
                            <Editor v-model="modelValue.column.column_4.data.textBox3"
                                :toogle="[...toogle, 'color', 'highlight']" />
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-[#9c7c64] md:bg-transparent text-[10px] md:text-base border-t mt-8 pb-2 pt-2 md:pb-0 md:pt-4 text-center  md:text-[#d1d5db]"
                :style="{ borderColor: selectedColor[1], color: selectedColor[1] }">
                <Editor v-model="modelValue.copyRight" :toogle="[...toogle, 'color', 'highlight']" />
            </div>
        </div>
    </div>


</template>


<style scss></style>
