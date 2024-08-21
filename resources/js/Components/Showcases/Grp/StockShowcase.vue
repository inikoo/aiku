<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 04 Apr 2023 11:19:33 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">


const props = defineProps<{
    data: object
}>();
const product = props.data.data;

// Inventory/OrgStock/UI/ShowOrgStock.php

</script>


<template>
    <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-2  xl:grid-cols-3 gap-4 ">
        <pre>{{ props.data }}</pre>
        <div class="m-auto">
            <!-- <img :src="route('grp.media.show',product.image_id)" alt="product main image"
                 class=" sm:max-w-xs  md:max-w lg:h-64 "
            /> -->
        </div>
        <table class="lg:mr-6 lg:ml-0 md:ml-28 md:mr-28">
            <tr>
                <td class="px-1 border border-gray-200">All sales since: Thu 30 Mar 2023</td>
            </tr>
            <tr>
                <td class="px-1 border border-gray-200">€0.00</td>
                <td class="px-1 border border-gray-200">0</td>
                <td class="px-1 border border-gray-200">0 (NA)</td>
            </tr>
            <tr>
                <td class="px-1 border border-gray-200">2023</td>
                <td class="px-1 border border-gray-200">2022</td>
                <td class="px-1 border border-gray-200">2021</td>
                <td class="px-1 border border-gray-200">2020</td>
                <td class="px-1 border border-gray-200">2019</td>
            </tr>
            <tr>
                <td class="px-1 border border-gray-200">€0</td>
                <td class="px-1 border border-gray-200">€0</td>
                <td class="px-1 border border-gray-200">€0</td>
                <td class="px-1 border border-gray-200">€0</td>
                <td class="px-1 border border-gray-200">€0</td>
            </tr>
            <tr>
                <td class="px-1 border border-gray-200">0</td>
                <td class="px-1 border border-gray-200">0</td>
                <td class="px-1 border border-gray-200">0</td>
                <td class="px-1 border border-gray-200">0</td>
                <td class="px-1 border border-gray-200">0</td>
            </tr>
            <tr>
                <td class="px-1 border border-gray-200">2Q 23</td>
                <td class="px-1 border border-gray-200">1Q 23</td>
                <td class="px-1 border border-gray-200">4Q 22</td>
                <td class="px-1 border border-gray-200">3Q 22</td>
                <td class="px-1 border border-gray-200">2Q 22</td>
            </tr>
            <tr>
                <td class="px-1 border border-gray-200">€0</td>
                <td class="px-1 border border-gray-200">€0</td>
                <td class="px-1 border border-gray-200">€0</td>
                <td class="px-1 border border-gray-200">€0</td>
                <td class="px-1 border border-gray-200">€0</td>
            </tr>
            <tr>
                <td class="px-1 border border-gray-200">0</td>
                <td class="px-1 border border-gray-200">0</td>
                <td class="px-1 border border-gray-200">0</td>
                <td class="px-1 border border-gray-200">0</td>
                <td class="px-1 border border-gray-200">0</td>
            </tr>
        </table>

        <table class="table-auto lg:ml-10 lg:mr-6 md:ml-28 md:mr-28">
            <tbody>
            <tr>
                <td class="px-1 border-b border-t border-gray-200">Active</td>
                <td class="px-1 border-b border-t border-gray-200">Online</td>
            </tr>
            <tr>
                <td class="px-1 border-b border-gray-200">Stock available:</td>
                <td class="px-1 border-b border-gray-200">50</td>
            </tr>
            <tr>
                <td class="px-1 border-b border-gray-200">Cost</td>
                <td class="px-1 border-b border-gray-200">€10.05 (€1.01/bag)</td>
            </tr>
            <tr>
                <td class="px-1 border-b border-gray-200">Price</td>
                <td class="px-1 border-b border-gray-200">€30.50 (€3.05/bag) margin 67.0%</td>
            </tr>
            <tr>
                <td class="px-1 border-b border-gray-200">RRP</td>
                <td class="px-1 border-b border-gray-200">€7.35/bag margin 58.5%</td>
            </tr>
            <tr>
                <td class="px-1 border-b border-gray-200">Webpage</td>
                <td class="px-1 border-b border-gray-200">jsb-03</td>
            </tr>
            </tbody>
        </table>

    </div>
<!--
    <table class="table-auto">
        <tbody>
        <tr>
            <td class="px-1">Primera fila</td>
        </tr>
        <tr>
            <td class="px-1">Segunda fila</td>
        </tr>
        <tr>
            <td class="px-1">Tercera fila</td>
        </tr>
        <tr>
            <td class="px-1">Cuarta fila</td>
        </tr>
        </tbody>
    </table>
-->
</template>
