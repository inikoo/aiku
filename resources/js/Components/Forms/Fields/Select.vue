<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 10 May 2023 09:18:00 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup>
import Multiselect from '@vueform/multiselect'

const value = null
const options = [
    'Batman',
    'Robin',
    'Joker',
    'Superman',
    'Wonderwoman',
    'Spiderman',
    'Hulk',
    'Iron Man',
    'Dr. Strange',
]

const multiselectPlaceholder = "Select your option"

</script>

<template>
    <div>
        <Multiselect v-model="value" :options="options" :placeholder="multiselectPlaceholder" />
    </div>
</template>



<style src="@vueform/multiselect/themes/default.css"></style>
