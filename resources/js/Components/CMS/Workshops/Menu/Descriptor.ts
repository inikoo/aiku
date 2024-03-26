const svgText = `<svg style="width:48px;height:48px" viewBox="0 0 24 24">
<path fill="currentColor" d="M18.5,4L19.66,8.35L18.7,8.61C18.25,7.74 17.79,6.87 17.26,6.43C16.73,6 16.11,6 15.5,6H13V16.5C13,17 13,17.5 13.33,17.75C13.67,18 14.33,18 15,18V19H9V18C9.67,18 10.33,18 10.67,17.75C11,17.5 11,17 11,16.5V6H8.5C7.89,6 7.27,6 6.74,6.43C6.21,6.87 5.75,7.74 5.3,8.61L4.34,8.35L5.5,4H18.5Z" />
</svg>`
const svgLink = `<svg style="width:48px;height:48px" viewBox="0 0 24 24">
<path fill="currentColor" d="M3.9,12C3.9,10.29 5.29,8.9 7,8.9H11V7H7A5,5 0 0,0 2,12A5,5 0 0,0 7,17H11V15.1H7C5.29,15.1 3.9,13.71 3.9,12M8,13H16V11H8V13M17,7H13V8.9H17C18.71,8.9 20.1,10.29 20.1,12C20.1,13.71 18.71,15.1 17,15.1H13V17H17A5,5 0 0,0 22,12A5,5 0 0,0 17,7Z" />
</svg>`
const svgImage = `<svg style="width:48px;height:48px" viewBox="0 0 24 24">
<path fill="currentColor" d="M8.5,13.5L11,16.5L14.5,12L19,18H5M21,19V5C21,3.89 20.1,3 19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19Z" />
</svg>`

export const DescriptorBlocks = [
	{
		id: 'text',
		label: 'Text',
		category: 'Basic',
		media: svgText,
		activate: true,
		content: {
			type: 'text',
			content: 'Insert your text here',
			style: { padding: '10px' },
		}
	}, {
		id: 'link',
		label: 'Link',
		category: 'Basic',
		media: svgLink,
		activate: true,
		content: {
			type: 'link',
			content: 'Insert your link here',
			style: { color: '#d983a6' }
		}
	}, {
		id: 'image',
		label: 'Image',
		category: 'Basic',
		media: svgImage,
		activate: true,
		content: { type: 'image' }
	}
]