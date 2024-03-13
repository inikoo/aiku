import { v4 as uuidv4 } from "uuid"

const menuItem = () => {
	return {
		content: "dummy", 
        menu_id: uuidv4() 
	}
}

const menuTypeList = () => {
	return {
		title: "Menu 1",
		type: "group",
		column_id: uuidv4(),
		group: [
			menuItem(),
			menuItem()
		],
	}
}

const menuTypeDescription = () => {
    return {
		title: "Menu 2",
		type: "single",
		column_id: uuidv4(),
	}
}



const menuDataLayout = {
	initialColumns: [menuTypeList(), menuTypeDescription(), menuTypeList()],
}

const menuDataTools = {
	hand: [
		{ name: "edit", icon: ["fas", "fa-hand-pointer"], value: "click" },
		{ name: "grab", icon: ["fas", "hand-rock"], value: "grab" },
	],
	theme: [
		{ name: "Light Theme", value: "light-theme" },
		{ name: "Dark Theme", value: "dark-theme" },
	],
	columnsType: [
		{ name: "group", value: "group" },
		{ name: "single", value: "single" },
	],
}

export { menuTypeList, menuTypeDescription, menuDataLayout, menuDataTools, menuItem }
