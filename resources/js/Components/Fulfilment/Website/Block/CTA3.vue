<script setup lang='ts'>
import Button from '@/Components/Elements/Buttons/Button.vue'


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

</script>

<template>
    <div class="relative overflow-hidden rounded-lg lg:h-96">
        <div class="absolute inset-0">
            <img :src="familyBanner.image" alt="" class="h-full w-full object-cover object-center" />
        </div>

        <div aria-hidden="true" class="relative h-96 w-full lg:hidden" />
        <div aria-hidden="true" class="relative h-32 w-full lg:hidden" />

        <div
            class="absolute inset-x-0 bottom-0 rounded-bl-lg rounded-br-lg bg-white bg-opacity-75 p-6 backdrop-blur backdrop-filter sm:flex sm:items-center sm:justify-between lg:inset-x-auto lg:inset-y-0 lg:w-96 lg:flex-col lg:items-start lg:rounded-br-none lg:rounded-tl-lg">
            <div v-html="familyBanner.description" class="text-gray-600 pr-3 overflow-y-auto mb-4" />

            <Button type="indigo" :key="1" label="View the collection" size="xl" full />
        </div>
    </div>
</template>