<script setup lang='ts'>
import { ref, onMounted } from 'vue'
import JsBarcode from 'jsbarcode'

const buf = ref('weqewq')


onMounted(() => {
    JsBarcode('#palletBarcode', route().v().params.pallet.padStart(4, 0), {
        lineColor: "rgb(41 37 36)",
        width: 2,
        height: 70,
        displayValue: true
    });
})


</script>

<template>
    <div class="w-full grid grid-cols-2">
        <div>

        </div>
        <div class="place-self-end pr-2 pt-2">
            <svg id="palletBarcode" />
        </div>
    </div>
</template>
