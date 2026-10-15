export const noteCategoryClass: Record<string, string> = {
    gray: 'border-gray-300 bg-gray-50 text-gray-700',
    purple: 'border-purple-300 bg-purple-50 text-purple-800',
    blue: 'border-blue-300 bg-blue-50 text-blue-800',
    green: 'border-green-300 bg-green-50 text-green-800',
    red: 'border-red-300 bg-red-50 text-red-800',
    orange: 'border-orange-300 bg-orange-50 text-orange-800',
    yellow: 'border-yellow-300 bg-yellow-50 text-yellow-800',
    black: 'border-gray-800 bg-gray-800 text-white',
}

export const noteSeverityIcon: Record<string, { icon: string, class: string }> = {
    info: { icon: 'fal fa-info-circle', class: 'text-gray-400' },
    warning: { icon: 'fal fa-exclamation-triangle', class: 'text-amber-500' },
    critical: { icon: 'fas fa-exclamation-circle', class: 'text-red-600' },
}
