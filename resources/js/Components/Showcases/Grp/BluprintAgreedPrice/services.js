
export default {
    name : 'Services',
    key : 'services',
    checkbox : false,
    column : [
        {
            title : 'Services',
            key : 'name',
            type : 'name',
            class : 'w-80'
        },
        {
            title : 'Original price',
            key : 'price',
            type : 'price',
            class : 'w-80'
        },
        {
            title: 'Price',
            key: 'agreed_price',
            type: 'price',
            class : 'w-80',
        },
        {
            title : 'Discount',
            key : 'discount',
            type : 'discount',
            class : 'w-80',
        },
    ]
}   