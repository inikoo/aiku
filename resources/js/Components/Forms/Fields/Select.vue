<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 10 May 2023 09:18:00 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script>
import Multiselect from '@vueform/multiselect'

export default {
    components: {
        Multiselect,
    },
    data() {
        return {
            value: null,
            options: [
                'Batman',
                'Robin',
                'Joker',
            ]
        }
    }
}
</script>

<template>
    <div>
        <Multiselect
            v-model="value"
            :options="options"
        />
    </div>
</template>



<style src="@vueform/multiselect/themes/default.css"></style>
