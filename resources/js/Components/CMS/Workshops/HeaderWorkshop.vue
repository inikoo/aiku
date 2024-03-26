<script setup lang="ts">
import grapesjs from 'grapesjs'
import 'grapesjs/dist/css/grapes.min.css'
import { ref, onMounted } from 'vue'
import presetWebpage from 'grapesjs-preset-webpage'
import pluginBlocksBasic from 'grapesjs-blocks-basic'  // Import all basic Blocks
import { DescriptorBlocks } from '@/Components/CMS/Workshops/Menu/Descriptor.ts'
import { HeaderBlock1 } from '@/Components/CMS/Header/HeaderComponentList.ts'

const canvas = ref(null)
let editor

onMounted(() => {
    editor = grapesjs.init({
        container: '#gjs',
        fromElement: true,
        storageManager: {
            type: 'local',
            autosave: true,
            autoload: true,
        },
        canvas: {
            styles: [],
            scripts: [],
        },
        plugins: [presetWebpage, pluginBlocksBasic],
        pluginsOpts: {
            [presetWebpage]: {
                blocksBasicOpts: {
                    blocks: ['column1', 'column2', 'column3', 'column3-7', 'text', 'link', 'image', 'video'],
                    flexGrid: 1,
                },
                blocks: ['link-block', 'quote', 'text-basic'],
            },
        },
    })

    editor.Blocks.add(HeaderBlock1.id, HeaderBlock1)
})

</script>

<template>
    <div ref="canvas" id="gjs">

    </div>
    <div id="panels">
    </div>
</template>
