// Conver string from 'shops_navigation' to 'shop', etc
export const generateNavigationName = (navKey: string) => {
    return navKey.split('_')[0].slice(0, -1)
}

// Generate string 'shop' to 'currentShop'
export const generateCurrentString = (str: string | undefined) => {
    if (!str) return ''
    
    return 'current' + str.charAt(0).toUpperCase() + str.slice(1)
}