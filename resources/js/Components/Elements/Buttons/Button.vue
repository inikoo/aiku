<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Sun, 30 Oct 2022 15:27:23 Greenwich Mean Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import {library} from '@fortawesome/fontawesome-svg-core';
import {faPlus} from '@/../private/pro-solid-svg-icons';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";

library.add(faPlus);

const props = defineProps(
    {
        'type': {
            type: String,
            default: 'primary',
        },
        'size': {
            type: String,
            default: 'm',
        },
        'leftIcon': {
            type: Object,
        },
        'rightIcon': {
            type: Object,
        },
        'action': {
            type: String,
        },
    });

let typeClass = '';
let iconClass = '';
let sizeClass = '';
switch (props.type) {
    case 'white':
        typeClass = 'border-gray-300 bg-white text-gray-700  hover:bg-gray-50 ';
        break;
    case 'secondary':
        typeClass = 'border-transparent bg-indigo-100 text-indigo-700 hover:bg-indigo-200';
        break;
    case 'primary':
        typeClass = 'border-transparent bg-indigo-600 text-white hover:bg-indigo-700';
        break;
}


switch (props.size) {
    case 'xs':
        sizeClass = 'rounded px-2.5 py-1.5 text-xs';
        iconClass= '-ml-0.5 mr-2 h-3 w-3 ';
        break;
    case 's':
        sizeClass = 'rounded-md px-3 py-2 text-sm';
        iconClass= '-ml-0.5 mr-2 h-4 w-4';
        break;
    case 'm':
        sizeClass = 'rounded-md x-4 py-2 text-sm';
        iconClass= '-ml-1 mr-2 ';
        break;
    case 'l':
        sizeClass = 'rounded-md px-4 py-2 text-base';
        iconClass= '-ml-1 mr-3 h-5 w-5';
        break;
    case 'xl':
        sizeClass = 'rounded-md px-6 py-3 text-base';
        iconClass= 'ml-1 mr-3 h-5 w-5';
        break;
}




</script>

<template>

    <button
        type="button"
        :class="[
        'px-5 border inline-flex items-center font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
        typeClass,sizeClass]"
    >


        <FontAwesomeIcon
            v-if="action==='create'"
            aria-hidden="true"
            icon="fas fa-plus"
            size="sm"
            :class="[iconClass]"/>

        <FontAwesomeIcon
            v-if="leftIcon"
            :title="leftIcon['tooltip']"
            aria-hidden="true"
            :icon="leftIcon['icon']"
            size="lg"
            :class="[iconClass]"/>
        <slot/>
    </button>


</template>
