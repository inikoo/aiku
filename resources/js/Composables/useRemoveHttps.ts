// To remove https:// or http:// from a string
export const useRemoveHttps = (text: string | undefined) => {
    if(!text) return ''

    return text.toString().replace(/^https?:\/\//g, '')
}