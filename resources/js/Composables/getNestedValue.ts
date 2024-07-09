
export const setFormValue = (data: Object, fieldName: String|Array) => {
    if (Array.isArray(fieldName)) {
        console.log('s',getNestedValue(data, fieldName))
        return getNestedValue(data, fieldName)
    } else {
        return data[fieldName]
    }
}

export const getNestedValue = (obj: Object, keys: Array) => {
    return keys.reduce((acc, key) => {
       /*  console.log(acc, key) */
        if (acc && typeof acc === "object" && key in acc){
            console.log('lk')
            return acc[key]
        } 
        return null
    }, obj)
};