<script setup lang="ts">
import { EmailEditor } from 'vue-email-editor';
import { ref } from 'vue';
import { routeType } from "@/types/route";

// Define props without defaults since they're all required
const props = defineProps<{
    imagesUploadRoute : routeType
    updateRoute : routeType
    publishRoute : routeType
    snapshot : routeType
}>()

const emailEditor = ref(null);

const editorLoaded = () => {
    emailEditor.value?.editor.loadDesign({});
};

// Uncomment these functions when needed
/*
const saveDesign = () => {
    emailEditor.value?.saveDesign((design) => {
        console.log('saveDesign', design);
    });
};

const exportHtml = () => {
    emailEditor.value?.exportHtml((data) => {
        console.log('exportHtml', data);
    });
};
*/

</script>

<template>
    <div id="app">
        <EmailEditor 
            ref="emailEditor" 
            class="unlayer" 
            @load="editorLoaded" 
            :options="{ confirmOnDelete: false }" 
            :appearance="{
                panels: {
                    tools: { dock: 'left' },
                },
            }" 
        />
    </div>
</template>

<style lang="scss" scoped>
.unlayer {
    height: calc(100vh - 177px);
    width: 100%;
}

.bmJUHV .blockbuilder-branding {
    display: none !important;
}
</style>
