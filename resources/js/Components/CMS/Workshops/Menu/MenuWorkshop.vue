<script setup lang="ts">
import { ref, onMounted } from 'vue'

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

  <div class="mx-auto max-w-7xl py-24 sm:px-6 sm:py-32 lg:px-8">
    <!-- Top Bar -->
    <div class="bg-gray-800 grid grid-cols-3 text-white flex justify-between items-center p-2 text-xs">
    <div></div>
    <div class="font-bold text-center">FAIRLY TRADING WHOLESALE GIFTS SINCE 1995</div>

    <!-- Section: Logout, Cart, profile -->
    <div class="place-self-end flex items-center space-x-4 mr-4">
      <a href="#" class="flex items-center">
        <i class="fas fa-sign-out-alt mr-1"></i> Log Out
      </a>
      <a href="#">
        <i class="far fa-heart"></i>
      </a>
      <a href="#" class="flex items-center gap-x-1">
        <i class="fas fa-shopping-cart relative mr-1">
          <div
            class="absolute -top-1 -right-1 bg-white border border-gray-800 h-2.5 aspect-square rounded-full text-gray-600 text-[6px] flex items-center justify-center">
            5
          </div>
        </i> £568.20
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
          <i class="fas fa-search absolute top-1/2 -translate-y-1/2 right-4 text-gray-400"></i>
        </div>
        <button
          class="justify-self-end w-fit bg-stone-500 hover:bg-stone-600 text-white text-sm py-1 px-4 rounded-md">Become
          a Gold Reward Member <i class="ml-1 fas fa-chevron-right text-xs"></i> </button>
      </div>

      <!-- Section: Navigation list horizontal -->
      <nav class="relative flex text-sm text-gray-600">
        <div v-for="(navigation, idxNavigation) in navigationsList" href="#" class="group w-full ">
          <div
            class="px-5 hover:bg-gray-200 hover:text-orange-500 flex items-center justify-center gap-x-1 h-full cursor-pointer">
            <div class="w-fit text-center">{{ navigation.label }}</div>
            <i class="fas fa-chevron-down text-[11px]"></i>
          </div>

          <!-- Section: Subnav hover -->
          <div v-if="navigation.subnavs"
            class="hidden group-hover:grid inline absolute left-0 top-full border border-gray-300 w-full grid-cols-4 gap-x-5 gap-y-8 px-6 pt-6 pb-14">
            <div v-for="subnav in navigation.subnavs" class="space-y-2">
              <div class="font-semibold">{{ subnav.title }}</div>

              <!-- Subnav links -->
              <div class="flex flex-col gap-y-2">
                <div v-for="link in subnav.links" class="flex items-center gap-x-2">
                  <i class="fas fa-chevron-right text-[10px] text-gray-400"></i>
                  <span class=" text-gray-500 hover:text-gray-600 hover:underline cursor-pointer">{{ link }}</span>
                </div>

                <div v-if="subnav.title != 'Fragrance'" class="font-semibold underline text-xs cursor-pointer">
                  See all
                </div>
                <div v-else class="mt-6">
                  <div class="font-bold underline">
                    Starters
                  </div>
                  <div class="mt-2">
                    Shop Beauty & Spa Starters
                  </div>
                  <div class="mt-6 underline font-semibold">
                    BLOG - AW Product Guide
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>
      </nav>
    </div>
  </div>
  
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
