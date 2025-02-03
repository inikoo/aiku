export interface routeType {
    name: string
    parameters?: string[]
    method?: 'get' | 'post' | 'patch' | 'delete'
    body?: {
        [key: string]: any
    }
}