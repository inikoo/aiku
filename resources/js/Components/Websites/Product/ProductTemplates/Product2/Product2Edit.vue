<script setup lang='ts'>

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCircle, faHandPointer, faStar, faStarHalfAlt } from '@fas'
import { faHeart, faLongArrowAltDown } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import PureInputNumber from '@/Components/Pure/PureInputNumber.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import SwiperGalery from '@/Components/SwiperGalery.vue'
library.add(faCircle, faHandPointer, faStar, faStarHalfAlt, faHeart, faLongArrowAltDown)

const props = defineProps<{
    mode: Object;
    colorThemed?: Object;
    data: object;
}>();

const tableData = [
    { label: 'Origin', value: 'Spain' },
    { label: 'Net weight', value: '0.04Kg /piece' },
    { label: 'Shipping weight', value: '0.04Kg' },
    { label: 'Dimensions', value: '0x0 (cm)' },
    { label: 'Barcode', value: '5055796528424' },
    { label: 'MSDS', value: 'CLP SDS', url: 'https://www.ancientwisdom.biz/attachment.php?id=10787' },
    { label: 'MSDS', value: 'IFRA Certificate', url: 'https://www.ancientwisdom.biz/attachment.php?id=10788' },
    { label: 'MSDS', value: 'Declaration', url: 'https://www.ancientwisdom.biz/attachment.php?id=10789' },
]

const images = [
    'https://swiperjs.com/demos/images/nature-1.jpg',
    'https://swiperjs.com/demos/images/nature-1.jpg',
    'https://swiperjs.com/demos/images/nature-1.jpg',
    'https://swiperjs.com/demos/images/nature-1.jpg'
]

const descriptionProduct = `<div id="product_description" class="product_description_block _au_vw_ text-gray-600">
<h2 class="text-xl font-semibold">Wholesale Rosemary Essential Oil</h2>
<p><br><strong>Latin Name: Rosmarinus Officinalis.<br>Part of Plant Used: Flowering Tops.<br>Source: Spain<br>Extraction Method: Steam Distillation.</strong></p>
<p>The Rosemary Essential Oil is steam distilled from the flowering tips of the plant. The <strong>smell of rosemary essential oil is woody and herbal</strong> and is used in aromatherapy to refresh and stimulate the mind.</p>
<p>This <a href="https://www.ancientwisdom.biz/top12" rel="noopener noreferrer" target="_blank"><strong><u>essential oil</u></strong></a> is believed to treat wounds, burns, colds, flu, fatigue, digestive troubles, headaches, asthma, bronchitis, gout, rheumatism, liver and gall bladder problems, water retention and poor circulation. For a massage, rosemary oil is often mixed with a base oil or two like almond, apricot kernel or hazelnut oil. It is good for the hair as it is said to increase circulation to the scalp and promote hair growth. Never use this oil internally, and avoid it if you're pregnant or suffer from high blood pressure or epilepsy.</p>
<p>Remember to always dilute essential oils in a base oil if using on the skin - patch test recommended! <br><br>Ready to invigorate your senses? Shop our Rosemary Essential Oil collection now!</p>
<p><br></p><p><br><br><br></p>
</div>`

const productData = {
    title: "42x Satya Assorted Incense 15 gms - Display Pack",
    images: [
        { src: "https://www.ancientwisdom.biz/rwi/320x280_1851953.jpeg" },
        { src: "https://tailwindui.com/plus/img/ecommerce-images/product-page-01-product-shot-02.jpg" },
        { src: "https://www.ancientwisdom.biz/rwi/320x280_1851955.jpeg" }
    ],
    code: "EID-65",
    rrp: "RRP: £2.65/Pack",
    stock: 0,
    original_price: {
        price: "£32.14",
        rrp: "£0.77/Pack"
    },
    discount: {
        percentage: 5,
        price: "£30.53",
        rrp: "£0.73/Pack",
        isApplied: true
    }
}
</script>

<template>
    <div class="grid grid-cols-11 gap-x-8 p-14">
        <div class="col-span-3">
           <SwiperGalery :images="images"/>
        </div>
        <div class="col-span-6">
            <h1 class="font-semibold text-xl mb-2">{{ productData.title }}</h1>
            <!-- Section: Code, RRP, Stock -->
            <div class="text-gray-500 mb-4">
                <div class="flex justify-between text-xs">
                    <div>{{ productData.code }}</div>
                    <div>{{ productData.rrp }}</div>
                </div>

                <div class="flex justify-between mb-3">
                    <div class="flex items-center gap-x-2">
                        <span>Stock:</span>
                        <span>
                            <FontAwesomeIcon icon='fas fa-circle'
                                :class="[productData.stock ? 'text-green-500' : 'text-red-500']" fixed-width
                                aria-hidden='true' v-tooltip="productData.stock ? 'Ready stock' : 'Out of stock'" />
                        </span>
                        <span>Product made on demand</span>
                    </div>
                    <div>
                        <FontAwesomeIcon icon='fal fa-heart' class='text-xl' fixed-width aria-hidden='true' />
                    </div>
                </div>
            </div>

            <!-- Section: Price and Discount -->
            <div class="mb-8">
                <div class="flex items-center gap-x-2">
                    <span>Price:</span>
                    <FontAwesomeIcon v-if="!productData.discount.isApplied" icon='fal fa-check' fixed-width
                        aria-hidden='true' />
                    <span>{{ productData.original_price.price }}</span>
                </div>

                <div class="flex items-center gap-x-2 text-orange-500 mt-1">
                    <div class="rounded flex items-center text-sm w-fit px-1 py-0.5"
                        :class="[productData.discount.isApplied ? 'border border-green-500 bg-green-50 text-green-500' : 'border border-gray-500 bg-gray-200 text-gray-500']">
                        <FontAwesomeIcon :icon="productData.discount.isApplied ? 'fas fa-star' : 'fas fa-star-half-alt'"
                            fixed-width aria-hidden='true' />
                        <div class="flex items-center">
                            <FontAwesomeIcon icon='fal fa-long-arrow-alt-down' size="sm" fixed-width
                                aria-hidden='true' />
                            <span>{{ productData.discount.percentage }}%</span>
                        </div>
                    </div>
                    <FontAwesomeIcon v-if="productData.discount.isApplied" icon='fal fa-check' fixed-width
                        aria-hidden='true' />
                    <span>{{ productData.discount.price }}</span>
                </div>
            </div>

            <!-- Section: Button order now -->
            <div class="flex gap-x-2 w-80 mb-12">
                <div class="w-full xl:w-28">
                    <PureInputNumber class="py-0" />
                </div>
                <Button label="Order now" icon="fas fa-hand-pointer" type="black" full />
            </div>

            <div v-html="descriptionProduct" class="text-gray-600"></div>
        </div>

        <div  class="col-span-2 divide-y divide-gray-300 text-gray-500 text-sm">
            <div v-if="data.setting.product_spec" v-for="row in tableData" :key="row.label" class="py-1 grid grid-cols-5 gap-x-4">
                <div class="col-span-2">{{ row.label }}</div>
                <component :is="row.url ? 'a' : 'div'" :href="row.url" class="col-span-3 text-blue-600 hover:underline">
                    {{ row.value }}
                </component>
            </div>
        </div>
    </div>
</template>

<style>
/* Optional: Add any additional styling for the gallery */
.p-galleria .p-galleria-item {
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>
