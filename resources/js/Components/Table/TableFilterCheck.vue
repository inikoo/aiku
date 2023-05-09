<template>
    <div class="grid justify-items-center grid-flow-col auto-cols-auto divide-x-2 divide-gray-200 py-3">
        <div v-for="(labele, index) of labels" :key="index" class="w-full" :class="{'bg-indigo-100': labele.checked}" >
            <div class="grid justify-center grid-flow-col items-center focus:bg-gray-800" >
                <label :for="(labele.label + index)" class="py-2 select-none cursor-pointer inline pr-2">
                    {{ labele.label }} ({{ labele.total }})
                </label>
                <input 
                    :id="(labele.label + index)" 
                    :name="(labele.label + index)" 
                    class="cursor-pointer focus:ring-0" 
                    type="checkbox" 
                    v-model="labele.checked"
                    @input="onClick(labele.value)"
                />
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
    defineProps({
        labels: Object,
        onClick: {
            type: Function,
            required: true,
        },
    });
</script>