import { v4 as uuidv4 } from "uuid"

const menuItem = () => {
	return {
		content: "dummy", menu_id: uuidv4() 
	}
}

const menuTypeList = () => {
	return {
		title: "Menu list 1",
		type: "list",
		column_id: uuidv4(),
		data: [
			menuItem(),
			menuItem()
		],
	}
}

const menuTypeDescription = () => {
	return {
		title: "Description",
		type: "description",
		column_id: uuidv4(),
		content:
			"Lorem Ipsum is simply dummy te printernto electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
	}
}

const menuTypeInfo = () => {
	return {
		title: "add info",
		type: "info",
		id: uuidv4(),
		data: [
			{
				value: "Ancient Wisdom s.r.o.",
				icon: "fas fa-map-marker-alt",
				id: uuidv4(),
			},
			{
				title: "billingAddress",
				value: "Billin",
				icon: "fas fa-map",
				id: uuidv4(),
			},
			{
				title: "vat",
				value: "VAT: SK2120525440",
				icon: "fas fa-balance-scale",
				id: uuidv4(),
			},
			{
				title: "building",
				value: "Reg: 50920600",
				icon: "fas fa-building",
				id: uuidv4(),
			},
			{
				title: "phone",
				value: "+421 (0)33 558 60 71",
				icon: "fas fa-phone",
				id: uuidv4(),
			},
			{
				title: "email",
				value: "<a href=mailto:contact@awgifts.eu>contact@awgifts.eu</a>",
				icon: "fas fa-envelope",
				id: uuidv4(),
			},
		],
	}
}

const footerDataLayout = {
	initialColumns: [menuTypeList(), menuTypeDescription(), menuTypeList()],
	socials: [
		{
			label: "Facebook",
			href: "",
			icon: "fab fa-facebook",
			social_id: uuidv4(),
		},
		{
			label: "Instagram",
			href: "",
			icon: "fab fa-instagram",
			social_id: uuidv4(),
		},
		{
			label: "Twitter",
			href: "",
			icon: "fab fa-twitter",
			social_id: uuidv4(),
		},
		{
			label: "Github",
			href: "",
			icon: "fab fa-github",
			social_id: uuidv4(),
		},
	],
	copyRight: "2024 Aw Advantage Inc. All rights reserved.",
	image: {
		imageSrc: null,
	},
}

const footerDataTools = {
	hand: [
		{ name: "edit", icon: ["fas", "fa-hand-pointer"], value: "click" },
		{ name: "grab", icon: ["fas", "hand-rock"], value: "grab" },
	],
	theme: [
		{ name: "Light Theme", value: "light-theme" },
		{ name: "Dark Theme", value: "dark-theme" },
	/* 	{ name: "Simple Theme", value: "simple-theme" }, */
	],
	columnsType: [
		{ name: "Description", value: "description" },
		{ name: "List", value: "list" },
		/* 	{ name: "Info", value: "info" }, */
	],
	columnsToolsLink: [{ name: "Add Item", value: "add" }],
	columnsToolsInfo: [{ name: "Add Item", value: "add" }],
}

export { menuTypeList, menuTypeDescription, menuTypeInfo, footerDataLayout, footerDataTools, menuItem }
