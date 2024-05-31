


export default {
    name : 'Physical Goods',
    key : 'physical_goods',
    column : [
        {
            title : 'Physical Goods',
            key : 'name'
        },
        {
            title : 'Price',
            key : 'price',
            type : 'number',
            propsOptions : {
                onChange : (value,column,rowData)=> {
                    let discountedPrice = value - (value * (rowData.discount / 100))
                    discountedPrice = discountedPrice.toFixed(2)
                    rowData.agreed_price = discountedPrice
                }
            }
        },
        {
            title : 'Discount',
            key : 'discount',
            type : 'discount',
            propsOptions : {
                onChange : (value,column,rowData)=> {
                    let discountedPrice = rowData.price - (rowData.price * (value / 100))
                    discountedPrice = discountedPrice.toFixed(2)
                    rowData.agreed_price = discountedPrice
                }
            }
        },
        {
            title : 'Agreed Price',
            key : 'agreed_price',
        },
    ]
}   