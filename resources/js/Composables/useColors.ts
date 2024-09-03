export const useBasicColor = (colorName: string | undefined) => {
    if (!colorName) return ''

    switch (colorName) {
        case "blue":
            return "#009eff"

        case "yellow":
            return "#f7d000"

        case "green":
            return "#00ce00"

        case "red":
            return "#fa6565"

        case "orange":
            return "#fcb661"

        case "pink":
            return "#e6007e"

        case "purple":
            return "#5000e6"

        case "indigo":
            return "#1624ff"

        case "turquoise":
            return "#40E0D0"

        case "magenta":
            return "#FF00FF"

        case "cyan":
            return "#00FFFF"
            
        case "lime":
            return "#00FF00"

        case "olive":
            return "#808000"

        case "gold":
            return "#FFD700"

        case "silver":
            return "#C0C0C0"

        case "coral":
            return "#FF7F50"

        default:
            break
    }
}