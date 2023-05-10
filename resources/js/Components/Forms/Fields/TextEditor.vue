<template>
    <div>
      <PrimitiveEditor class="py-2 px-3 rounded border border-gray-500" v-model="content" />
  
      <div class="content my-2">
        <h3>Content</h3>
        <pre><code>{{ content }}</code></pre>
      </div>
    </div>
  </template>
  
  <script>
  import PrimitiveEditor from '@/Components/Elements/Fields/PrimitiveEditor.vue'

  
  export default {
    components: {
        PrimitiveEditor,
    },
  
    data() {
      return {
        content: '<p>Change this text. This component is not finished yet.</p>',
      }
    },
  }
  </script>
  
  