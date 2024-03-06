<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
-->

<script setup lang="ts">
import Button from "@/Components/Elements/Buttons/Button.vue";
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Utils/Modal.vue";
import StoredItemMovementForm from './StoredItemMovementForm.vue'
import { routeType } from '@/types/route';


const props = defineProps<{
	locationRoute: {
		index : routeType
	}
	palletRoute: {
		index: routeType
	}
  pallet : {}
}>()

const isModalOpen = ref(false);
const form = useForm({  quantity: 1, pallet: null, location: null, type: 'pallet'});
</script>

<template>
  <div class="flex">
    <Button :icon="'far fa-person-dolly'" @click="()=>isModalOpen = true" type="secondary" />
  </div>

  <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false" width="w-1/2">
    <Button class="sr-only" />
    <div class="space-y-4">
     <StoredItemMovementForm :form="form" :palletRoute="palletRoute" :locationRoute="locationRoute" :pallet="pallet"/>
    </div>
  </Modal>
</template>
