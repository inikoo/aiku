<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 30 Oct 2024 16:00:09 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import draggable from "vuedraggable";
import Button from '@/Components/Elements/Buttons/Button.vue';
/* import { data as dataHeader, bluprintForm as bluprintFormHeader} from '@/Components/Websites/Header/HeaderTemplates/Header1/descriptor'
import { getRenderComponent  as getRenderComponentHeader} from "@/Components/CMS/Website/Headers/Content" */
import { getComponent } from "@/Components/CMS/Website/Menus/Content"


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


const header = props.headerData ? props.headerData : { key : "header1" , data : dataHeader, blueprint : [] }


</script>

<template>
 <div class="opacity-25 cursor-not-allowed">
        <component :is="getRenderComponentHeader(header.key)" :loginMode="true"  :data="header.data" :colorThemed="colorThemed" />
    </div>

    <div>
        <component :is="getComponent(template)" :navigations="navigations" :colorThemed="colorThemed"/>
    </div>
   
</template>
