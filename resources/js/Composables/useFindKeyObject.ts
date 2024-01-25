// This only return the first result found
export const useValueInDeepObject: any = (object: { [key: string]: any }, keyFind: string) => {
    if(!object) {
        return null
    }

    if (object.hasOwnProperty(keyFind)){
        return object[keyFind]
    }

    // if deep object have keyfind
    if(JSON.stringify(object).includes(`"${keyFind}":`)) {
        // Looping the key
        for (var i = 0; i < Object.keys(object).length; i++) {
            // if the key is object call the useValueInDeepObject
            if (typeof object[Object.keys(object)[i]] == "object") {
                // make a recursive call
                var childObj = useValueInDeepObject(object[Object.keys(object)[i]], keyFind)
                
                // if the recursive call return value
                if (childObj != null){
                    return childObj
                }
            }
        }
    } else {
        return null
    }
}

// layout.navigation.org?.[layout.currentParams.organisation].shops_navigation.dssk.shop.topMenu