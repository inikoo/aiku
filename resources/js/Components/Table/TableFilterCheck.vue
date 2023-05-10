<script setup lang="ts">

const props = defineProps<{
    labels: Array<{
        key: string
      label: string
      checked: boolean
      value: string
      count: bigint

    }>,
}>()
const emit = defineEmits(['changeCheckboxValue']);
const handleClick = (checkedFilterCheckState: any[]) => {
    let filterCheck ="";
    checkedFilterCheckState.map(function(val) {
        if (val.checked) {
            filterCheck+= val.value + '|';
        }
    });
    
    emit('changeCheckboxValue', filterCheck);
};
</script>
<template>
    <div class="grid justify-items-center grid-flow-col auto-cols-auto divide-x-2 divide-gray-200 py-3">
        <div v-for="(label, index) of labels" :key="index" class="w-full" :class="{'bg-indigo-100': label.checked}" >
            <div class="grid justify-center grid-flow-col items-center focus:bg-indigo-100 hover:bg-indigo-100" >
                <label :for="(label.label + index)" class="py-2 select-none cursor-pointer inline pr-2">
                    {{ label.label }} ({{ label.count }})
                </label>
                <input
                    :id="(label.label + index)"
                    :name="(label.label + index)"
                    :value="label.value"
                    class="cursor-pointer focus:ring-0"
                    type="checkbox"
                    v-model="label.checked"
                    v-on:click="handleClick(labels)"
                />
            </div>
        </div>
    </div>
</template>
