<script setup lang="ts">

const props = defineProps<{
    labels: Array<{
        key: string
      label: string
      show: boolean
      count: bigint
    }>,
}>()

let data= {};
for (let i in props.labels) {
    data[props.labels[i].key]=props.labels[i].show;
}


const emit = defineEmits(['changeCheckboxValue']);
const handleClick = (e) => {
    data[e.target.id]=e.target.checked
    emit('changeCheckboxValue', data);
};
</script>
<template>
    {{labels}}
    <div class="grid justify-items-center grid-flow-col auto-cols-auto divide-x-2 divide-gray-200 py-3">
        <div v-for="(label, index) of labels" :key="index" class="w-full" >
            <div class="grid justify-center grid-flow-col items-center" >
                <label :for="(label.key)" class="py-2 select-none cursor-pointer inline pr-2">
                    {{ label.label }} ({{ label.count }})
                </label>
                <input
                    :id="(label.key)"
                    :name="(label.key)"
                    class="cursor-pointer focus:ring-0"
                    type="checkbox"

                    v-on:click="handleClick"
                />
            </div>
        </div>
    </div>
</template>
