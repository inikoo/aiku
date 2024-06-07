export default {
    block : [
        {
          id: 1,
          name: 'Wowsbar Banner',
          icon: ['fal', 'presentation'],
          imageSrc: 'https://tailwindui.com/img/ecommerce-images/category-page-05-image-card-01.jpg',
          imageAlt: 'wowsbar banner',
          type: 'wowsbar',
          component: 'bannerWowsbar',
          fieldValue : {
            link : null,
            emptyState : true,
            height : {
              value : '335',
              unit : 'px'
            },
            width :{
              value : '100',
              unit : '%'
            },
          },
          fieldProps : {
          }
        },  
        {
          id: 2,
          name: 'Product Page',
          icon: ['fal', 'cube'],
          imageSrc: 'https://tailwindui.com/img/ecommerce-images/category-page-05-image-card-01.jpg',
          imageAlt: 'wowsbar banner',
          type: 'product',
          component: 'ProductPage',
          fieldValue : {
            product : null,
            emptyState : true
          },
          fieldProps : {}
        },
        {
          id: 3,
          name: 'Text',
          icon: ['fal', 'text'],
          imageSrc: 'https://tailwindui.com/img/ecommerce-images/category-page-05-image-card-01.jpg',
          imageAlt: 'Text',
          type: 'text',
          component: 'text',
          fieldValue : {
              value : `<h2 style="text-align: center"><strong><span>Export HTML or JSON</span></strong></h2><p style="text-align: center"></p><p style="text-align: center"><span style="color: rgb(0, 0, 0); font-size: 14px">It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</span></p>`
          },
          fieldProps : {}
        },
      ]
      
}