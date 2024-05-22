<script setup lang='ts'>
import StackedComponent from "@/Layouts/Grp/StackedComponent.vue"
import Button from '@/Components/Elements/Buttons/Button.vue'
import { inject, ref } from "vue"
import Profile from '@/Pages/Grp/Profile.vue'
import { layoutStructure } from "@/Composables/useLayoutStructure"

const layout = inject('layout', layoutStructure)
// layout.stackedComponents.push(Button)
</script>

<template>
    <Transition name="stacked-component">
        <div v-if="layout.stackedComponents.length" class="fixed top-0 left-0 h-screen w-screen flex justify-end isolate z-[100]">
            <div class="flex gap-x-2 absolute bottom-24 left-1/2 z-[200]">
                <Button @click="() => layout.stackedComponents.push(Profile)" label="Add component" class="">
                </Button>
                <Button @click="() => layout.stackedComponents.pop()" label="Delete component" type="negative" class="">
                </Button>
            </div>
            
            <TransitionGroup name="stacked-component">
                <StackedComponent v-for="(component, idxComponent) in layout.stackedComponents"
                    :component="component"
                    :key="'stackedComponent' + idxComponent"
                    :idxComponent="idxComponent"
                    :listLength="layout.stackedComponents.length"
                >
                    <!-- <div class="bg-gray-700/50 absolute top-0 left-0 h-screen w-screen flex justify-end isolate z-[100]">
                    </div> -->
                    <!-- <StackedComponent :component /> -->
                </StackedComponent>
            </TransitionGroup>
        </div>
    </Transition>
</template>

<style>
.stacked-component-move, /* apply transition to moving elements */
.stacked-component-enter-active,
.stacked-component-leave-active {
  position: fixed;
  transition: all 0.5s ease;
}

.stacked-component-enter-from,
.stacked-component-leave-to {
  transform: translateX(100%);
}

</style>