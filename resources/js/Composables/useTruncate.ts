export const useTruncate = (string: string, truncateLength: number, different: number = 4) => {
    if (string) {
        if(string.length > truncateLength){
            if(string.length > truncateLength + different) {
                return `${string.substring(0, truncateLength)}...`
            }
        }
        return string
    }
    else {
        return string
    }
}