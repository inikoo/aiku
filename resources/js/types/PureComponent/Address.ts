export interface AddressValue {
    id?: number
    address_line_1?: string
    address_line_2?: string
    sorting_code?: string
    postal_code?: number
    locality?: string
    dependant_locality?: string
    administrative_area?: string
    country_code?: string
    country_id: number
}

export interface AddressOptions {
    countriesAddressData: {
        [key: string]: {
            administrativeAreas: []
            fields: {
                address_line_1: {
                    label: string
                    required: boolean
                }
                address_line_2: {
                    label: string
                    required?: boolean
                }
                locality: {
                    label: string
                    required?: boolean
                }
                postal_code: {
                    label: string
                    required?: boolean
                }
            }
            label: string  // "Bangladesh (BD)"
        }
    }
}

export interface Address {
    value: AddressValue
    options: AddressOptions
}