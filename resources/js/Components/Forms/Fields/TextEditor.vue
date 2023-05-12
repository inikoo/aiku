<template>
  <div class="">
    <PrimitiveEditor class="" v-model="content" :showStats="showStats" :placeholder="placeholder"/>

    <!-- <div class="content my-2">
      <h3>Content</h3>
      <pre><code>{{ content }}</code></pre>
    </div> -->
  </div>
</template>
  
<script setup>
import PrimitiveEditor from '@/Components/Elements/Fields/PrimitiveTextEditor.vue'
import { ref } from 'vue'

const showStats = true
const placeholder = ref("This is dinamic placeholder.")

const content = ref('<p>Change this text. This this text. This component is not finished yet.</p>')

</script>
  
  