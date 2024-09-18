<script setup lang='ts'>
import Product1 from '@/Components/Iris/AW/Product1.vue'
import FamilyBanner from '@/Components/Iris/AW/FamilyBanner.vue'


const familyBanner = {
    description: `
        <div id="webpage_content_header_text" class="text-gray-600" style="">
            <h1 class="text-xl font-bold">Wholesale Nag Champa Incense</h1>
            <div class="description mt-4"><p style="text-align: justify;">Nag Champa is probably the best selling incense on the planet! Everyone should stock it as an essential item. &nbsp;<a class="_gre_ _str_" href="/tips-how-to-sell-more-nag-champa-incense" rel="noopener noreferrer" target="_blank"></a>
                <a class="_gre_" href="https://www.ancientwisdom.biz/how-to-sell-nag-champa-incense"><strong>Check out our Sales Tips Page</strong>.</a> The original Blue pack made famous by the patronage of Satya Sai Baba is now only one of a whole range of incense and other scented products made with this distinctive fragrance.</p>
                <p style="text-align: justify;">Sticks: Each pack contains 15 Incense Sticks. &nbsp;</p>
                <p class="mb-4">Approx. burn time: 30 minutes. Cones: Each pack contains 12 Incense Cones. Approx. cone burn time: 20 minutes. Use with an incense holder for best results.&nbsp;</p>
                <p class="text-lg"><strong>Made in Bangalore</strong>
                </p>
                <p style="text-align: justify;"><strong><span style="color: rgb(209, 72, 65);">Make sure you order plenty and make a big stack</span></strong></p>
            </div>
        </div>`,
    image: 'https://www.ancientwisdom.biz/wi.php?id=1842040&s=690x690'
}


const productsList = [
    {
        title: "12x Nag Champa 15g",
        image: "https://www.ancientwisdom.biz/rwi/320x280_1842044.jpeg",
        code: "EID-04",
        rrp: "RRP : £1.35/pack",
        stock: 43,
        original_price: {
            price: "£4.68",
            rrp: "£0.39/pack"
        },
        discount: {
            percentage: 5,
            price: "£4.45",
            rrp: "£0.37/pack",
            isApplied: false
        }
    },
    {
        title: "600x Nag Champa 15g (Full Carton - 50 boxes of 12)",
        image: "https://www.ancientwisdom.biz/rwi/320x280_1842042.jpeg",
        code: "EID-04C",
        rrp: "RRP : £1.35/pack",
        stock: 0,
        original_price: {
            price: "£4.68",
            rrp: "£0.39/pack"
        },
        discount: {
            percentage: 5,
            price: "£4.45",
            rrp: "£0.37/pack",
            isApplied: true
        }
    },
    {
        title: "4x Satya Nagchampa Incense 250 Gms",
        image: "https://www.ancientwisdom.biz/rwi/320x280_1842018.jpeg",
        code: "EID-51",
        rrp: "RRP : £24.00/Pack",
        stock: 43,
        original_price: {
            price: "£44.94",
            rrp: "£11.24/Pack"
        },
        discount: {
            percentage: 5,
            price: "£42.69",
            rrp: "£10.67/Pack",
            isApplied: false
        }
    },
    {
        title: "42x Satya Assorted Incense 15 gms - Display Pack",
        image: "https://www.ancientwisdom.biz/rwi/320x280_1851953.jpeg",
        code: "EID-65",
        rrp: "RRP : £2.65/Pack",
        stock: 43,
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
    },
]
</script>

<template>
    <div class="bg-white text-gray-700 w-full py-16 sm:py-24 space-y-16">
        <FamilyBanner :familyBanner />

        <div class="grid grid-cols-4 gap-x-2 gap-y-4">
            <Product1 v-for="product in productsList" :product />
        </div>
    </div>
</template>