export const useGetCurrencySymbol = (countryCode: string | undefined) => {
    if(!countryCode) return ''

    const options = { style: 'currency', currency: countryCode }
    const formatter = new Intl.NumberFormat('en-US', options)  // 'en-US' to easier to format
    const parts = formatter.formatToParts(1)
    const symbolPart = parts.find(part => part.type === 'currency')
    return symbolPart ? symbolPart.value : null
}