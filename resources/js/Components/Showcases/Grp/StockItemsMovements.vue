
 <script setup lang="ts">
  import { Link } from '@inertiajs/vue3';
  import Table from '@/Components/Table/Table.vue';
  import { ref } from 'vue';
  import Button from '@/Components/Elements/Buttons/Button.vue';
  import Icon from "@/Components/Icon.vue"
  import Tag from "@/Components/Tag.vue"
  import { trans } from 'laravel-vue-i18n'
  
  const props = defineProps<{
      data?: {}
      tab?: string
      state:any
      key:any 
      tableKey?: string
  }>()

  </script>
  
  <template>
      <Table :resource="data" :name="tab" class="mt-5">
          
  
      </Table>
  </template>
  