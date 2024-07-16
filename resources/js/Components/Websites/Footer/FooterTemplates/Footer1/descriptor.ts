import { v4 as uuidv4 } from "uuid"

export const data = {
	column: {
		column_1: {
			name: "column 1",
			key: "1",
			data: [
				{
					name: "Help",
					id: uuidv4(),
					data: [
						{
							name: "Contact Us",
							id: uuidv4(),
						},
						{
							name: "Delivery",
							id: uuidv4(),
						},
						{
							name: "Returns Policy",
							id: uuidv4(),
						},
					],
				},
				{
					name: "About AW",
					id: uuidv4(),
					data: [
						{
							name: "AW Beginning",
							id: uuidv4(),
						},
						{
							name: "Business Ethics",
							id: uuidv4(),
						},
						{
							name: "Our Brands",
							id: uuidv4(),
						},
						{
							name: "Subscribe to the Newsletter",
							id: uuidv4(),
						},
					],
				},
				{
					name: "Showroom",
					id: uuidv4(),
					data: [
						{
							name: "Book Showroom Appointment",
							id: uuidv4(),
						},
					],
				},
				{
					name: "Customer Service",
					id: uuidv4(),
					data: [
						{
							name: "+44 (0) 1142 729 165",
							id: uuidv4(),
						},
						{
							name: "care@ancientwisdom.biz",
							id: uuidv4(),
						},
					],
				},
				{
					name: "Reviews",
					id: uuidv4(),
					data: [],
				},
				{
					name: "FAQ",
					id: uuidv4(),
					data: [],
				},
			],
		},
		column_2: {
			name: "column 2",
			key: "2",
			data: [
				{
					name: "Why Choose AW?",
					id: uuidv4(),
					data: [
						{
							name: "No Minimum Order",
							id: uuidv4(),
						},
						{
							name: "First Order Bonus",
							id: uuidv4(),
						},
						{
							name: "Buy Noy, Pay Later",
							id: uuidv4(),
						},
						{
							name: "Volume Discounts",
							id: uuidv4(),
						},
					],
				},
				{
					name: "Membership",
					id: uuidv4(),
					data: [
						{
							name: "Join The Gold Reward Membership",
							id: uuidv4(),
						},
					],
				},
				{
					name: "Discover",
					id: uuidv4(),
					data: [
						{
							name: "Catalogue - Live Stock Feeds",
							id: uuidv4(),
						},
						{
							name: "AW History & The Phoenix Effect",
							id: uuidv4(),
						},
						{
							name: "Distribution Centrein Europe",
							id: uuidv4(),
						},
						{
							name: "David's Travel Blog",
							id: uuidv4(),
						},
					],
				},
				{
					name: "Legal",
					id: uuidv4(),
					data: [
						{
							name: "Terms & Condition",
							id: uuidv4(),
						},
						{
							name: "Privacy Policy",
							id: uuidv4(),
						},
						{
							name: "Cookies Policy",
							id: uuidv4(),
						},
					],
				},
			],
		},
		column_3: {
			name: "column 3",
			key: "3",
			data: [
				{
					name: "Our Services",
					id: uuidv4(),
					data: [
						{
							name: "DROPSHIPPING",
							id: uuidv4(),
						},
						{
							name: "FULFILMENT",
							id: uuidv4(),
						},
						{
							name: "DIGITAL MARKETING",
							id: uuidv4(),
						},
					],
				},
				{
					name: "AW Partners",
					id: uuidv4(),
					data: [
						{
							name: "Agnes + Cat",
							id: uuidv4(),
						},
						{
							name: "Agnes + Cat",
							id: uuidv4(),
						},
						{
							name: "AW - Aromatics",
							id: uuidv4(),
						},
						{
							name: "AW - Portugal",
							id: uuidv4(),
						},
						{
							name: "AW - Germany",
							id: uuidv4(),
						},
						{
							name: "AW - Slovakia",
							id: uuidv4(),
						},
						{
							name: "AW - España",
							id: uuidv4(),
						},
						{
							name: "Agnes + France",
							id: uuidv4(),
						},
						{
							name: "Agnes + Poland",
							id: uuidv4(),
						},
						{
							name: "Agnes + Austria",
							id: uuidv4(),
						},
						{
							name: "Agnes + Europe",
							id: uuidv4(),
						},
						{
							name: "Agnes + Romania",
							id: uuidv4(),
						},
						{
							name: "Agnes + Czechia",
							id: uuidv4(),
						},
						{
							name: "Agnes + Italy",
							id: uuidv4(),
						},
					],
				},
			],
		},
        column_4: {
            name: "column 4",
            key: "4",
            data: {
                textBox1: `Ancient Wisdom Marketing Ltd. Affinity Park, Europa Drive Sheffield, S9 1XT`,
                textBox2: '<p>Vat No: GB764298589  </br> Reg. No: 04108870</p>',
                textBox3: 'Subscribe to the WhatsApp messages and benefit from exclusive discounts.'
            },
        },
    },
	usePayment: true,
	useSocial: true,
	copyRight: "Copyright © 2024 Aurora. All rights reserved. Terms of UsePrivacy Policy",
	PaymentData: {
		data: [
            {
				label: "Checkout.com",
				value: "checkout.com",
				image: "https://www.linqto.com/wp-content/uploads/2023/04/logo_2021-11-05_19-04-11.530.png",
			},
			{
				label: "visa",
				value: "visa",
				image: "https://e7.pngegg.com/pngimages/687/457/png-clipart-visa-credit-card-logo-payment-mastercard-usa-visa-blue-company.png",
			},
			{
				label: "Paypal",
				value: "paypal",
				image: "https://e7.pngegg.com/pngimages/292/77/png-clipart-paypal-logo-illustration-paypal-logo-icons-logos-emojis-tech-companies.png",
			},
			{
				label: "Mastercard",
				value: "mastercard",
				image: "https://i.pinimg.com/736x/38/2f/0a/382f0a8cbcec2f9d791702ef4b151443.jpg",
			},
			{
				label: "PastPay",
				value: "PastPay",
				image: "https://pastpay.com/wp-content/uploads/2023/07/PastPay-logo-dark-edge.png",
			},
		],
	},
}

export const bluprintForm = [
	{
		name: "Payments",
		key: "PaymentData",
		type: "payment_templates",
	},
]
