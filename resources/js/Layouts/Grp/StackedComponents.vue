<script setup lang='ts'>
import StackedComponent from "@/Layouts/Grp/StackedComponent.vue"
import Button from '@/Components/Elements/Buttons/Button.vue'
import { ref } from "vue"
import Profile from '@/Pages/Grp/Profile.vue'
import axios from "axios"

const componentList = ref([
    Button,
    Profile
])



</script>

<template>
    <Transition name="fghdsa">
        <div v-if="componentList.length" class="fixed top-0 left-0 h-screen w-screen flex justify-end isolate z-[100]">
            <div @click="() => componentList.push(Button)" class="cursor-pointer absolute text-red-500 bottom-5 left-1/2 z-[200]">
                Add component
            </div>
            <div @click="() => componentList.pop()" class="cursor-pointer absolute text-red-500 bottom-14 left-1/2 z-[200]">
                Delete component
            </div>
            <TransitionGroup name="fghdsa">
                <StackedComponent v-for="(component, idxComponent) in componentList"
                    :component="component"
                    :key="'stackedComponent' + idxComponent"
                    :idxComponent="idxComponent"
                    :listLength="componentList.length"
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
.fghdsa-move, /* apply transition to moving elements */
.fghdsa-enter-active,
.fghdsa-leave-active {
  position: fixed;
  transition: all 0.5s ease;
}

.fghdsa-enter-from,
.fghdsa-leave-to {
  transform: translateX(100%);
}

</style>