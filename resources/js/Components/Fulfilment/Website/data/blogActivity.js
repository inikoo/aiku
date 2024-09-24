export default {
	block: [
		{
			id: 1,
			name: "Wowsbar Banner",
			icon: ["fal", "presentation"],
			type: "wowsbar",
			component: "bannerWowsbar",
			fieldValue: {
				banner_id : null,
				banner_slug : null,
				emptyState: true,
				height: {
					value: "335",
					unit: "px",
				},
				width: {
					value: "100",
					unit: "%",
				},
			},
			fieldProps: {},
		},
		/*   {
          id: 2,
          name: 'Product Page',
          icon: ['fal', 'cube'],
          type: 'product',
          component: 'ProductPage',
          fieldValue : {
            product : null,
            emptyState : true
          },
          fieldProps : {}
        }, */
		{
			id: 3,
			name: "Text",
			icon: ["fal", "text"],
			type: "text",
			component: "text",
			fieldValue: {
				value: `<h2 style="text-align: center"><strong><span>Export HTML or JSON</span></strong></h2><p style="text-align: center"></p><p style="text-align: center"><span style="color: rgb(0, 0, 0); font-size: 14px">It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</span></p>`,
			},
			fieldProps: {},
		},
		/*         {
          id: 4,
          name: 'Family Page Offer',
          icon: ['fal', 'newspaper'],
          type: 'familyPage',
          component: 'FamilyPageOffer',
          fieldValue : {
              value : null,
          },
          fieldProps : {}
        }, */
		{
			id: 5,
			name: "Product List",
			icon: ["fal", "cube"],
			type: "ProductPage",
			component: "ProductList",
			fieldValue: {
				value: null,
			},
			fieldProps: {},
		},
		{
			id: 6,
			name: "CTA 1",
			icon: ["fal", "rectangle-wide"],
			type: "CTA",
			component: "CTA",
			fieldValue: {
				headline: `<h2><strong><span style="color: #FFFFFF">What ingredients should be considered when selecting body and hand soaps?</span></strong></h2>`,
				description: '<p><span style="color: #FFFFFF">Provide your customers with informed advice.</span></p><p></p>',
				button: `Read blog`,
			},
			fieldProps: {},
		},
		{
			id: 7,
			name: "Rewiews",
			icon: ["fal", "stars"],
			type: "Reviews",
			component: "Reviews",
			fieldValue: {
				value: null,
			},
			fieldProps: {},
		},
		{
			id: 8,
			name: "Image",
			icon: ["fal", "image"],
			type: "Image",
			component: "Image",
			fieldValue: {
				value: null,
			},
			fieldProps: {},
		},
		{
			id: 9,
			name: "CTA 2",
			icon: ["fal", "rectangle-wide"],
			type: "CTA",
			component: "CTA2",
			fieldValue: {
				headline: `<h2><strong><span style="color: #FFFFFF">Boost your productivity today</span></strong></h2>`,
				description: `<p style="text-align: center"><span style="color: #D1D1D1; font-size: 20px">Incididunt sint fugiat pariatur cupidatat consectetur sit cillum anim</span></p><p style="text-align: center"><span style="color: #D1D1D1; font-size: 20px"> id veniam aliqua proident excepteur commodo do ea.</span></p><p></p>`,
				button: `Get Started`,
			},
			fieldProps: {},
		},
    {
			id: 11,
			name: "I Frame",
			icon: ["fal", "paperclip"],
			type: "iframe",
			component: "Iframe",
			fieldValue: {
				link: null,
				emptyState: true,
				height: {
					value: "335",
					unit: "px",
				},
				width: {
					value: "100",
					unit: "%",
				},
			},
			fieldProps: {},
		},
		{
			id: 10,
			name: "Gallery",
			icon: ["fal", "images"],
			type: "image",
			component: "Gallery",
			fieldValue: {
				value:  [
          {
            id: 1,
            imageSrc: 'https://tailwindui.com/img/ecommerce-images/home-page-02-product-01.jpg',
            imageAlt: 'Black machined steel pen with hexagonal grip and small white logo at top.',
            text :  `<h2><span style="font-size: 36px">haloo</span></h2><p>lorem ipsum test tstetsbjsd<br>sfdnklsdjfml;jdl;f;</p>`,
          },
          {
            id: 2,
            imageSrc: 'https://tailwindui.com/img/ecommerce-images/home-page-02-product-01.jpg',
            imageAlt: 'Black machined steel pen with hexagonal grip and small white logo at top.',
            text :  `<h2><span style="font-size: 36px">haloo</span></h2><p>lorem ipsum test tstetsbjsd<br>sfdnklsdjfml;jdl;f;</p>`,
          },
          {
            id: 3,
            imageSrc: 'https://tailwindui.com/img/ecommerce-images/home-page-02-product-01.jpg',
            imageAlt: 'Black machined steel pen with hexagonal grip and small white logo at top.',
            text :  `<h2><span style="font-size: 36px">haloo</span></h2><p>lorem ipsum test tstetsbjsd<br>sfdnklsdjfml;jdl;f;</p>`,
          },
          {
            id: 4,
            imageSrc: 'https://tailwindui.com/img/ecommerce-images/home-page-02-product-01.jpg',
            imageAlt: 'Black machined steel pen with hexagonal grip and small white logo at top.',
            text :  `<h2><span style="font-size: 36px">haloo</span></h2><p>lorem ipsum test tstetsbjsd<br>sfdnklsdjfml;jdl;f;</p>`,
          },
        ],
			},
			fieldProps: {},
		},
		{
			id: 11,
			name: "CTA 3",
			icon: ["fal", "rectangle-wide"],
			type: "CTA",
			component: "CTA3",
			fieldValue: {
				description: "<div id='webpage_content_header_text' class='text-gray-600'><h1 class='text-xl font-bold'>Wholesale Nag Champa Incense</h1><div class='description mt-4'><p style='text-align: justify;'>Nag Champa is probably the best selling incense on the planet! Everyone should stock it as an essential item. &nbsp;<a class='_gre_ _str_' href='/tips-how-to-sell-more-nag-champa-incense' rel='noopener noreferrer' target='_blank'></a><a class='_gre_' href='https://www.ancientwisdom.biz/how-to-sell-nag-champa-incense'><strong>Check out our Sales Tips Page</strong>.</a> The original Blue pack made famous by the patronage of Satya Sai Baba is now only one of a whole range of incense and other scented products made with this distinctive fragrance.</p><p style='text-align: justify;'>Sticks: Each pack contains 15 Incense Sticks. &nbsp;</p><p class='mb-4'>Approx. burn time: 30 minutes. Cones: Each pack contains 12 Incense Cones. Approx. cone burn time: 20 minutes. Use with an incense holder for best results.&nbsp;</p><p class='text-lg'><strong>Made in Bangalore</strong></p><p style='text-align: justify;'><strong><span style='color: rgb(209, 72, 65);'>Make sure you order plenty and make a big stack</span></strong></p></div></div>",
				image: "https://www.ancientwisdom.biz/wi.php?id=1842040&s=690x690"
			},
			fieldProps: {},
		},
	],
}
