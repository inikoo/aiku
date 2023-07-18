<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 03 Jul 2023 16:55:06 Malaysia Time, Pantai Lembeng, Bali, Id
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import { Link } from "@inertiajs/vue3";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
// import { library } from "@fortawesome/fontawesome-svg-core";
// import {
//     faPeopleArrows, faPersonDolly, faClipboardList, faTerminal, faUserAlien, faCog, faNetworkWired, faCalendar, faStopwatch, faChessClock,
//     faBuilding, faBox, faBoxesAlt, faTachometer, faChartNetwork, faNarwhal, faBusinessTime, faUserTie
// } from "@/../private/pro-light-svg-icons";
import { useLayoutStore } from "@/Stores/layout";
import { capitalize } from "@/Composables/capitalize";

// library.add(faPeopleArrows, faPersonDolly, faClipboardList, faTerminal, faUserAlien, faCog, faNetworkWired, faCalendar, faStopwatch, faChessClock,
//     faBuilding, faBox, faBoxesAlt, faTachometer, faChartNetwork, faNarwhal, faBusinessTime, faUserTie
// );

const layout = useLayoutStore();

</script>

<template>
    <Link
        v-for="menu in layout.navigation?.[layout.currentModule]?.topMenu.subSections" :href="route(menu.route.name)"
        class="group text-sm flex justify-end items-center cursor-pointer py-1 space-x-1 px-4 md:px-4 lg:px-4"
        :class="[route(layout.currentRoute, route().v().params).includes(route(menu.route.name)) ? 'text-indigo-600' : 'text-gray-600']"
        :title="capitalize(menu.tooltip??menu.label??'')">
        <FontAwesomeIcon :icon="menu.icon"
            class="h-5 lg:h-3.5 w-auto group-hover:opacity-100 opacity-70 transition duration-100 ease-in-out"
            aria-hidden="true" />
        <span v-if="menu.label" class="hidden lg:inline capitalize whitespace-nowrap">{{ menu.label }}</span>
    </Link>
</template>

