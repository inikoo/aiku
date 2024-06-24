import { v4 as uuidv4 } from 'uuid';

export const navigation =[
  {
    label: 'New In & Trending',
	id: uuidv4(),
  },
  {
    label: 'Offers',
	id: uuidv4(),
  },
  {
    label: 'Aromatherapy',
	id: uuidv4(),
  },
  {
    label: 'Beauty & Spa',
	id: uuidv4(),
    subnavs: [
      {
        title: "Beauty Products",
		id: uuidv4(),
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
		id: uuidv4(),
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
		id: uuidv4(),
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
		id: uuidv4(),
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
		id: uuidv4(),
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
		id: uuidv4(),
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
		id: uuidv4(),
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
		id: uuidv4(),
        links: [
          "Fine Fragrance Perfume Oils"
        ]
      }
    ]
  },
  {
    label: 'Accessories',
	id: uuidv4(),
  },
  {
    label: 'Artisan Tea',
	id: uuidv4(),
  },
  {
    label: 'Home Fragrance',
	id: uuidv4(),
  },
  {
    label: 'Home & Garden',
	id: uuidv4(),
  },
  {
    label: 'Gemstones & Esoteric Gifts',
	id: uuidv4(),
  },
  {
    label: 'Incense',
	id: uuidv4(),
  },
  {
    label: 'Displays & Packaging',
	id: uuidv4(),
  },
]