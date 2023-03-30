<template>
    <div class="relative overflow-hidden bg-white">
        <!-- Decorative background image and gradient -->
        <div aria-hidden="true" class="absolute inset-0">
            <div class="absolute inset-0 mx-auto max-w-7xl overflow-hidden xl:px-8">
                <img src="https://tailwindui.com/img/ecommerce-images/home-page-02-sale-full-width.jpg" alt="" class="h-full w-full object-cover object-center" />
            </div>
            <div class="absolute inset-0 bg-white bg-opacity-75" />
            <div class="absolute inset-0 bg-gradient-to-t from-white via-white" />
        </div>

        <!-- Callout -->
        <section aria-labelledby="sale-heading" class="relative mx-auto flex max-w-7xl flex-col items-center px-4 pt-32 text-center sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:max-w-none">
                <h2 id="sale-heading" class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl">Get 25% off during our one-time sale</h2>
                <p class="mx-auto mt-4 max-w-xl text-xl text-gray-600">Most of our products are limited releases that won't come back. Get your favorite items while they're in stock.</p>
                <a href="#" class="mt-6 inline-block w-full rounded-md border border-transparent bg-gray-900 py-3 px-8 font-medium text-white hover:bg-gray-800 sm:w-auto">Get access to our one-time sale</a>
            </div>
        </section>

        <!-- Testimonials -->
        <section aria-labelledby="testimonial-heading" class="relative mx-auto max-w-7xl py-24 px-4 sm:px-6 lg:py-32 lg:px-8">
            <div class="mx-auto max-w-2xl lg:max-w-none">
                <h2 id="testimonial-heading" class="text-2xl font-bold tracking-tight text-gray-900">What are people saying?</h2>

                <div class="mt-16 space-y-16 lg:grid lg:grid-cols-3 lg:gap-x-8 lg:space-y-0">
                    <blockquote v-for="testimonial in testimonials" :key="testimonial.id" class="sm:flex lg:block">
                        <svg width="24" height="18" viewBox="0 0 24 18" aria-hidden="true" class="flex-shrink-0 text-gray-300">
                            <path d="M0 18h8.7v-5.555c-.024-3.906 1.113-6.841 2.892-9.68L6.452 0C3.188 2.644-.026 7.86 0 12.469V18zm12.408 0h8.7v-5.555C21.083 8.539 22.22 5.604 24 2.765L18.859 0c-3.263 2.644-6.476 7.86-6.451 12.469V18z" fill="currentColor" />
                        </svg>
                        <div class="mt-8 sm:mt-0 sm:ml-6 lg:mt-10 lg:ml-0">
                            <p class="text-lg text-gray-600">{{ testimonial.quote }}</p>
                            <cite class="mt-4 block font-semibold not-italic text-gray-900">{{ testimonial.attribution }}</cite>
                        </div>
                    </blockquote>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
const testimonials = [
    {
        id: 1,
        quote:
            'My order arrived super quickly. The product is even better than I hoped it would be. Very happy customer over here!',
        attribution: 'Sarah Peters, New Orleans',
    },
    {
        id: 2,
        quote:
            'I had to return a purchase that didn’t fit. The whole process was so simple that I ended up ordering two new items!',
        attribution: 'Kelly McPherson, Chicago',
    },
    {
        id: 3,
        quote:
            'Now that I’m on holiday for the summer, I’ll probably order a few more shirts. It’s just so convenient, and I know the quality will always be there.',
        attribution: 'Chris Paul, Phoenix',
    },
]
</script>
