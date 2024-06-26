import { v4 as uuidv4 } from "uuid"

export const navigation = [
	{
		label: "New In & Trending",
		id: uuidv4(),
    type : 'single'
	},
	{
		label: "Offers",
		id: uuidv4(),
    type : 'single'
	},
	{
		label: "Aromatherapy",
		id: uuidv4(),
    type : 'single'
	},
	{
		label: "Beauty & Spa",
		id: uuidv4(),
    type : 'multiple',
		subnavs: [
			{
				title: "Beauty Products",
				id: uuidv4(),
				links: [
					{ label: "Aromatherapy Hand & Body Lotion", link: "", id: uuidv4() },
					{ label: "Aromatherapy Hand & Body Lotion", link: "", id: uuidv4() },
					{ label: "Fragrance Hand & Body Lotions", link: "", id: uuidv4() },
					{ label: "Organic Body Oil", link: "", id: uuidv4() },
					{ label: "Aromatherapy Shea Body Butters", link: "", id: uuidv4() },
					{ label: "Aromatherapy Hand & Body Lotion", link: "", id: uuidv4() },
				],
			},
			{
				title: "Bath Bombs",
				id: uuidv4(),
				links: [
					{ label: "Crystal Jewellery Bath Bomb", link: "", id: uuidv4() },
					{ label: "Chakra Bath Fizzers Sets", link: "", id: uuidv4() },
					{ label: "Valentines Bath Bombs", link: "", id: uuidv4() },
					{ label: "Christmas Bath Bomb Gift Pack", link: "", id: uuidv4() },
					{ label: "Bath Dust", link: "", id: uuidv4() },
					{ label: "Cocktail Scented Bath Bombs", link: "", id: uuidv4() },
					{ label: "Chill Pills Gift Packs", link: "", id: uuidv4() },
					{ label: "Bath Bomb Kit", link: "", id: uuidv4() },
				],
			},
			{
				title: "Soaps",
				id: uuidv4(),
				links: [
					{ label: "Artisan Olive Oil Soap", link: "", id: uuidv4() },
					{ label: "Wild & Natural Soap Loaf", link: "", id: uuidv4() },
					{ label: "Sliced Wild & Natural Soap Loaf", link: "", id: uuidv4() },
					{ label: "Cool Waves Soap Loaves", link: "", id: uuidv4() },
					{ label: "Essential Oil Soap Loaf", link: "", id: uuidv4() },
					{ label: "Designer Soap Loaf", link: "", id: uuidv4() },
					{ label: "Loofah - Star Soap Loaf", link: "", id: uuidv4() },
					{ label: "Loofah - Round Soap Loaf", link: "", id: uuidv4() },
				],
			},
			{
				title: "Shop By Products",
				id: uuidv4(),
				links: [
					{ label: "Gift Sets / Hampers", link: "", id: uuidv4() },
					{ label: "Bath Bombs", link: "", id: uuidv4() },
					{ label: "Soaps", link: "", id: uuidv4() },
					{ label: "Soap Flowers", link: "", id: uuidv4() },
					{ label: "Products with Gemstones", link: "", id: uuidv4() },
					{ label: "Shampoos", link: "", id: uuidv4() },
					{ label: "Body Lotions & Butters", link: "", id: uuidv4() },
				],
			},
			{
				title: "Beauty Accessories",
				id: uuidv4(),
				links: [
					{ label: "Gemstone Face Rollers", link: "", id: uuidv4() },
					{ label: "Teardrop Konjac Sponge", link: "", id: uuidv4() },
					{ label: "Natural Japan Style Konjac Sponge", link: "", id: uuidv4() },
					{ label: "Egyptian Luxury Loofah", link: "", id: uuidv4() },
					{ label: "Glove, Mitts & Wraps", link: "", id: uuidv4() },
					{ label: "Fun Fruit Shape Sponges", link: "", id: uuidv4() },
					{ label: "Natural Soap Bags and Scrunches", link: "", id: uuidv4() },
				],
			},
			{
				title: "Bath Salts & Florals",
				id: uuidv4(),
				links: [
					{ label: "Bath Salts in Vials", link: "", id: uuidv4() },
					{ label: "Wild Hare Salt & Flowers Sets", link: "", id: uuidv4() },
					{ label: "Himalayan Bath Salt Blends", link: "", id: uuidv4() },
					{ label: "Aromatherapy Bath Potion in Kraft Bags", link: "", id: uuidv4() },
					{ label: "Floral Bath Soak & Facial Steam Blend", link: "", id: uuidv4() },
					{ label: "Aromatherapy Bath Potion - 7 kg", link: "", id: uuidv4() },
					{ label: "Himalayan Bath Salt - 25 kg", link: "", id: uuidv4() },
				],
			},
			{
				title: "Soap Flowers",
				id: uuidv4(),
				links: [
					{ label: "Soap Flowers Gift Boxes", link: "", id: uuidv4() },
					{ label: "Gift Soap Flower Bouquet", link: "", id: uuidv4() },
					{ label: "Ready to Retail Soap Flowers", link: "", id: uuidv4() },
					{ label: "Petite Soap Flower Bouquets", link: "", id: uuidv4() },
					{ label: "Luxury Soap Flowers", link: "", id: uuidv4() },
					{ label: "Soap Flower Bouquets", link: "", id: uuidv4() },
					{ label: "Craft Soap Flowers", link: "", id: uuidv4() },
				],
			},
			{
				title: "Fragrance",
				id: uuidv4(),
				links: [{ label: "Craft Soap Flowers", link: "", id: uuidv4() }],
			},
		],
	},
	{
		label: "Accessories",
		id: uuidv4(),
    type : 'single'
	},
	{
		label: "Artisan Tea",
		id: uuidv4(),
    type : 'single'
	},
	{
		label: "Home Fragrance",
		id: uuidv4(),
    type : 'single'
	},
	{
		label: "Home & Garden",
		id: uuidv4(),
    type : 'single'
	},
	{
		label: "Gemstones & Esoteric Gifts",
		id: uuidv4(),
    type : 'single'
	},
	{
		label: "Incense",
		id: uuidv4(),
    type : 'single'
	},
	{
		label: "Displays & Packaging",
		id: uuidv4(),
    type : 'single'
	},
]
