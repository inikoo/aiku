<script setup lang="ts">
import Image from "@/Components/Image.vue"

const props = defineProps<{
    data: Object
    layout : Object
}>()


const emits = defineEmits();

const changeLogo = async (element) => {
    emits('changeLogo',element)
};

</script>

<template>
 <div :class="`border: 2px solid ${layout?.color}; border-radius: 8px;`">
        <header class="">
            <nav class="mx-auto flex  items-center justify-between p-6 lg:px-8" aria-label="Global">
                <div class="flex lg:flex-1">
                    <label for="faviconUpload" class="flex justify-center items-center  cursor-pointer">
                        <input type="file" id="faviconUpload" accept="image/*" style="display: none" @change="changeLogo" />
                        <Image :src="data.logoSrc" class="h-8 w-auto"/>
                    </label>
                </div>
                <div class="flex gap-3">
                    <div  :class="`text-red-500 lg:flex lg:flex-1 lg:justify-end`" >
                    appointment
                    </div>
                    <div :class="`text-red-500`">|</div>
                    <div class="lg:flex lg:flex-1 lg:justify-end">
                        <a href="#" :class="`text-red-500 text-sm font-semibold leading-6`">
                        sign in
                        </a>
                    </div>
                </div>
            </nav>
        </header>
    </div>
</template>