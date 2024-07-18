<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 22 Aug 2023 19:44:06 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import draggable from "vuedraggable";
import Button from '@/Components/Elements/Buttons/Button.vue';
import { data as dataHeader, bluprintForm as bluprintFormHeader} from '@/Components/Websites/Header/HeaderTemplates/Header1/descriptor'
import { getRenderComponent  as getRenderComponentHeader} from "@/Components/Websites/Header/Content"
import { getComponent } from "@/Components/Websites/Menu/Content"


import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle } from '@fas';
import { faHeart } from '@far';

library.add(faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes, faPlusCircle, faBars, faUserCircle);


const props = withDefaults(defineProps<{
    navigations: Array<any>;
    headerData: object
    colorThemed: object
    template:string
}>(), {
});


const header = props.headerData ? props.headerData : { key : "header1" , data : dataHeader, bluprint : bluprintFormHeader }


</script>

<template>
 <div class="opacity-25 cursor-not-allowed">
        <component :is="getRenderComponentHeader(header.key)" :loginMode="true"  :data="header.data" :colorThemed="colorThemed" />
    </div>

    <div>
        <component :is="getComponent(template)" :navigations="navigations" :colorThemed="colorThemed"/>
    </div>
   
</template>
