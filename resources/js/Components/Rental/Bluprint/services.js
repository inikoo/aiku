
export default {
    name : 'Services',
    key : 'services',
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
            type: 'inputPrice',
            class : 'w-80',
            propsOptions: {
                onChange: (value, column, rowData) => {
                    let discount = (1 - (parseFloat(value) / rowData.price)) * 100;
                    discount = parseFloat(discount.toFixed(2));
                    rowData.discount = discount;
                }
            }
        },
        {
            title : 'Discount',
            key : 'discount',
            type : 'discount',
            class : 'w-80',
            propsOptions : {
                onChange : (value,column,rowData)=> {
                    let discountedPrice = rowData.price - (rowData.price * (value / 100))
                    rowData.agreed_price = discountedPrice
                }
            }
        },
    ]
}   