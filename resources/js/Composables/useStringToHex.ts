import stc from 'string-to-color'

export const useStringToHex = (value?: string, opacity?: number) => {
    if(!value) return '#6b7280'  // Fallback gray color

    return stc(value)  // Hex
}