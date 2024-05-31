
export default {
    name : 'Services',
    key : 'services',
    column : [
        {
            title : 'Services',
            key : 'name',
            type : 'name',
        },
        {
            title : 'Original price',
            key : 'original_price',
            type : 'price'
        },
        {
            title: 'Price',
            key: 'agreed_price',
            type: 'inputPrice',
            propsOptions: {
                onChange: (value, column, rowData) => {
                    let discount = (1 - (value / rowData.original_price)) * 100;
                    discount = parseFloat(discount.toFixed(2));
                    rowData.discount = discount;
                }
            }
        },
        {
            title : 'Discount',
            key : 'discount',
            type : 'discount',
            propsOptions : {
                onChange : (value,column,rowData)=> {
                    let discountedPrice = rowData.original_price - (rowData.original_price * (value / 100))
                    rowData.agreed_price = discountedPrice
                }
            }
        },
    ]
}   