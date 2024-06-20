<script setup lang="ts">
import { ref, onMounted, inject } from 'vue'
import { layoutStructure } from '@/Composables/useLayoutStructure'
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faChevronRight, faSignOutAlt, faShoppingCart, faSearch, faChevronDown, faTimes } from '@fas';
import { faHeart } from '@far';

library.add(faChevronRight, faSignOutAlt, faShoppingCart, faHeart, faSearch, faChevronDown, faTimes);
const layout = inject('layout', layoutStructure)

const navigationsList = [
  {
    label: 'New In & Trending'
  },
  {
    label: 'Offers'
  },
  {
    label: 'Aromatherapy'
  },
  {
    label: 'Beauty & Spa',
    subnavs: [
      {
        title: "Beauty Products",
        links: [
          "Aromatherapy Hand & Body Lotion",
          "Fragrance Hand & Body Lotions",
          "Organic Body Oil",
          "Aromatherapy Shea Body Butters",
          "Pure Body Butters",
          "Scented Butters",
          "Fragranced Sugar Body Scrubs",
          "Organic Hair Serum",
        ]
      },
      {
        title: "Bath Bombs",
        links: [
          "Crystal Jewellery Bath Bomb",
          "Chakra Bath Fizzers Sets",
          "Valentines Bath Bombs",
          "Christmas Bath Bomb Gift Pack",
          "Bath Dust",
          "Cocktail Scented Bath Bombs",
          "Chill Pills Gift Packs",
          "Bath Bomb Kit",
        ]
      },
      {
        title: "Soaps",
        links: [
          "Artisan Olive Oil Soap",
          "Wild & Natural Soap Loaf",
          "Sliced Wild & Natural Soap Loaf",
          "Cool Waves Soap Loaves",
          "Essential Oil Soap Loaf",
          "Designer Soap Loaf",
          "Loofah - Star Soap Loaf",
          "Loofah - Round Soap Loaf",
        ]
      },
      {
        title: "Shop By Products",
        links: [
          "Gift Sets / Hampers",
          "Bath Bombs",
          "Soaps",
          "Soap Flowers",
          "Products with Gemstones",
          "Shampoos",
          "Body Lotions & Butters",
        ]
      },
      {
        title: "Beauty Accessories",
        links: [
          "Gemstone Face Rollers",
          "Teardrop Konjac Sponge",
          "Natural Japan Style Konjac Sponge",
          "Egyptian Luxury Loofah",
          "Glove, Mitts & Wraps",
          "Fun Fruit Shape Sponges",
          "Natural Soap Bags and Scrunches",
        ]
      },
      {
        title: "Bath Salts & Florals",
        links: [
          "Bath Salts in Vials",
          "Wild Hare Salt & Flowers Sets",
          "Himalayan Bath Salt Blends",
          "Aromatherapy Bath Potion in Kraft Bags",
          "Floral Bath Soak & Facial Steam Blend",
          "Aromatherapy Bath Potion - 7 kg",
          "Himalayan Bath Salt - 25 kg",
        ]
      },
      {
        title: "Soap Flowers",
        links: [
          "Soap Flowers Gift Boxes",
          "Gift Soap Flower Bouquet",
          "Ready to Retail Soap Flowers",
          "Petite Soap Flower Bouquets",
          "Luxury Soap Flowers",
          "Soap Flower Bouquets",
          "Craft Soap Flowers",
        ]
      },
      {
        title: "Fragrance",
        links: [
          "Fine Fragrance Perfume Oils"
        ]
      }
    ]
  },
  {
    label: 'Accessories'
  },
  {
    label: 'Artisan Tea'
  },
  {
    label: 'Home Fragrance'
  },
  {
    label: 'Home & Garden'
  },
  {
    label: 'Gemstones & Esoteric Gifts'
  },
  {
    label: 'Incense'
  },
  {
    label: 'Displays & Packaging'
  },
]


</script>

<template>

  <div class="mx-auto max-w-9xl sm:px-6 sm:py-10 lg:px-8 border-2">
    <!-- Top Bar -->
    <div class="bg-gray-800 grid grid-cols-3 text-white flex justify-between items-center p-2 text-xs">
      <div></div>
      <div class="font-bold text-center">FAIRLY TRADING WHOLESALE GIFTS SINCE 1995</div>

      <div class="place-self-end flex items-center space-x-4 mr-4">
        <a href="#" class="flex items-center">
          <FontAwesomeIcon icon="fas fa-sign-out-alt" class=" mr-1"></FontAwesomeIcon> Log Out
        </a>
        <a href="#">
          <FontAwesomeIcon icon="far fa-heart"></FontAwesomeIcon>
        </a>
        <a href="#" class="flex items-center gap-x-1">
          <FontAwesomeIcon icon="fas fa-shopping-cart" class="relative mr-1">
            <div
              class="absolute -top-1 -right-1 bg-white border border-gray-800 h-2.5 aspect-square rounded-full text-gray-600 text-[6px] flex items-center justify-center">
              5
            </div>
          </FontAwesomeIcon> £568.20
        </a>
        <a href="#" class="flex items-center"><i class="fas fa-user-circle mr-1"></i> Hello Sandra</a>
      </div>
    </div>


    <!-- Main Nav -->
    <div class="bg-white shadow-md border-b-2 border-gray-700">
      <div class="container mx-auto flex flex-col justify-between items-center">
        <div class="w-full grid grid-cols-3 items-center justify-between space-x-4">
          <img src="https://d19ayerf5ehaab.cloudfront.net/assets/store-18687/18687-logo-1642004490.png"
            alt="Ancient Wisdom Logo" class="h-24">
          <div class="relative w-fit justify-self-center">
            <input type="text" placeholder="Search Products" class="border border-gray-400 py-1 px-4 text-sm w-80">
            <FontAwesomeIcon icon="fas fa-search" class=" absolute top-1/2 -translate-y-1/2 right-4 text-gray-400">
            </FontAwesomeIcon>
          </div>
          <button
            class="justify-self-end w-fit bg-stone-500 hover:bg-stone-600 text-white text-sm py-1 px-4 rounded-md">Become
            a Gold Reward Member <FontAwesomeIcon icon="fas fa-chevron-right" class="ml-1  text-xs"></FontAwesomeIcon>
          </button>
        </div>

        <!-- Section: Navigation list horizontal -->
        <nav class="relative flex text-sm text-gray-600">
          <div v-for="(navigation, idxNavigation) in navigationsList" href="#" class="group w-full ">
            <div
              class="px-5 hover:bg-gray-200 hover:text-orange-500 flex items-center justify-center gap-x-1 h-full cursor-pointer">
              <div class="w-fit text-center">{{ navigation.label }}</div>
              <FontAwesomeIcon icon="fas fa-chevron-down" class=" text-[11px]"></FontAwesomeIcon>
            </div>
          </div>
        </nav>
      </div>
    </div>

  </div>

  <div class="w-7xl sm:px-6 lg:px-8 mt-3 overflow-hidden">
    <TabGroup>
      <TabList class="flex space-x-8 border-b-2 w-full overflow-x-auto scrollbar-hide">
        <Tab v-for="tab in navigationsList" as="template" :key="tab.label" v-slot="{ selected }">
          <button :style="selected ? { color: layout.app.theme[0] } : {}" :class="[
            'whitespace-nowrap py-1.5 px-1 text-sm font-medium focus:ring-0 focus:outline-none mb-2',
            selected
              ? `text-[${layout.app.theme[0]}] border-2 border-b-0 rounded-t-lg px-2`
              : `border-transparent text-[${layout.app.theme[0]}] hover:border-[${layout.app.theme[0]}]`,
          ]">
            <span> {{ tab.label }} <font-awesome-icon v-if="selected" :icon="['fas', 'times']" class="ml-1" /></span>
          </button>
        </Tab>
      </TabList>

      <TabPanels class="mt-2">
        <TabPanel v-for="(tab, idx) in navigationsList" :key="idx" :class="[
          'rounded-xl bg-white p-3 shadow-lg',
          'ring-white/60 ring-offset-2 ring-offset-blue-400 focus:outline-none focus:ring-2',
        ]">
          <div class="card-content">
            <div v-if="tab.subnavs" class="grid grid-cols-4 gap-x-5 gap-y-8 px-6 pt-6 pb-14">
              <div v-for="subnav in tab.subnavs" class="space-y-2">
                <div class="font-semibold">{{ subnav.title }}</div>

                <!-- Subnav links -->
                <div class="flex flex-col gap-y-2">
                  <div v-for="link in subnav.links" class="flex items-center gap-x-2">
                    <font-awesome-icon icon="fas fa-chevron-right"
                      class="text-[10px] text-gray-400"></font-awesome-icon>
                    <span class="text-gray-500 hover:text-gray-600 hover:underline cursor-pointer">{{ link }}</span>
                  </div>

                  <div v-if="subnav.title != 'Fragrance'" class="font-semibold underline text-xs cursor-pointer">
                    See all
                  </div>
                  <div v-else class="mt-6">
                    <div class="font-bold underline">Starters</div>
                    <div class="mt-2">Shop Beauty & Spa Starters</div>
                    <div class="mt-6 underline font-semibold">BLOG - AW Product Guide</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </TabPanel>
      </TabPanels>
    </TabGroup>
  </div>

</template>


<style scss>
body {
  font-family: 'Inter', sans-serif;
}

.qwezxc {
  border: 1px solid red;
}

.zxcqwe {
  border: 1px solid green;
}

.asdzxc {
  box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.7), 0 0 0 4px rgb(239, 68, 68), 0 0 0 1px rgba(0, 0, 0, 0.2);
}
</style>
