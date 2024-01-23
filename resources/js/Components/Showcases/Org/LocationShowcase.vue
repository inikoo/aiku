<script setup lang='ts'>
import { ref, onMounted } from 'vue'
import JsBarcode from 'jsbarcode'

const buf = ref('weqewq')


onMounted(() => {
    JsBarcode('#locationBarcode', "123456789012", {
        lineColor: "rgb(41 37 36)",
        width: 1.6,
        height: 40,
        displayValue: true
    });
})


</script>

<template>
    <div class="w-full grid grid-cols-2">
        <div>
            
        </div>
        <div class="place-self-end pr-2 pt-2">
            <svg id="locationBarcode" />
        </div>
    </div>
</template>