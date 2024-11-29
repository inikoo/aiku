<script setup lang="js">
import { EmailEditor } from 'vue-email-editor';
import { ref } from 'vue';
import beeJson from './beePluginJsonExample.js'
import unlayerJson from './UnlayerJsonExample.json'

const emailEditor = ref();

const editorLoaded = () => {
   /*  emailEditor.value.loadDesign({
        html: "<div>hello</div>",
        classic: true
    }); */
}
const saveDesign = () => {
    console.log(emailEditor.value)
    emailEditor.value?.saveDesign((design) => {
        console.log('saveDesign', design);
    });
};

const exportHtml = () => {
    console.log(emailEditor.value)
    emailEditor.value.exportHtml(function (data) {
        console.log('exportHtml', data);

    })
};
</script>
<template>
    <div id="app">
        <button v-on:click="saveDesign">Save Design</button>

        <button v-on:click="exportHtml" class="m-3">export</button>
        <EmailEditor 
            ref="emailEditor" 
            class="unlayer" 
            @load="editorLoaded" 
            :options="{
            confirmOnDelete: false
            }" 
            :appearance="{
                panels: {
                    tools: {
                        dock: 'left',
                    },
                },
            }" 
        />
    </div>
</template>
<style lang="scss">
.unlayer {
    height: calc(100vh - 177px);
    width: 100%;
}

.bmJUHV .blockbuilder-branding {
    display: none !important
}
</style>
