<script setup  lang="ts">
import { TransitionRoot, TransitionChild, Dialog, DialogPanel } from '@headlessui/vue'

const props = defineProps({
  width: {
    type: String,
    default: 'w-4/5'
  },
  isOpen: Boolean
});
const emits = defineEmits()

const closeModal = () => {
    emits('onClose') 
}

</script>


<template>
    <TransitionRoot appear :show="props.isOpen" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-[22]">
            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100"
                leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-black bg-opacity-25" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95">
                        <DialogPanel
                            :class="`${props.width} transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all`">
                            <slot />
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

