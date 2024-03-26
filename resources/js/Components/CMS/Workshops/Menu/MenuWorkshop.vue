<script setup lang="ts">
import grapesjs from 'grapesjs'
import 'grapesjs/dist/css/grapes.min.css'
import { ref, onMounted } from 'vue'
import presetWebpage from 'grapesjs-preset-webpage'
import pluginBlocksBasic from 'grapesjs-blocks-basic'  // Import all basic Blocks
import { DescriptorBlocks } from '@/Components/CMS/Workshops/Menu/Descriptor.ts'
import { HeroBlock1 } from '@/Components/CMS/Menu/HeroComponentList.ts'
import { FeatureBlock1 } from '@/Components/CMS/Menu/FeatureComponentList.ts'
import { PricingBlock1 } from '@/Components/CMS/Menu/PricingComponentList.ts'
import { TestimonialBlock1 } from '@/Components/CMS/Menu/TestimonialComponentList.ts'

const canvas = ref(null)
let editor

function myPlugin(editor) {
  // Use the API: https://grapesjs.com/docs/api/
  editor.Blocks.add('my-first-block', {
    label: 'Simple block',
    content: '<div class="my-block">This is a simple block</div>',
  });
}

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
        // blockManager: {
        //     appendTo: '#panels',
        //     custom: true,
        //     blocks: DescriptorBlocks,
        // },
    })

    editor.Blocks.add(HeroBlock1.id, HeroBlock1)
    editor.Blocks.add(FeatureBlock1.id, FeatureBlock1)
    editor.Blocks.add(PricingBlock1.id, PricingBlock1)
    editor.Blocks.add(TestimonialBlock1.id, TestimonialBlock1)

    editor.addComponents(`<div>
    <img src="https://path/image" />
    <span title="foo">Hello world!!!</span>
    </div>`);
})
</script>

<template>
    <div ref="canvas" id="gjs">
    
    </div>
        <div id="panels">
    </div>
</template>

