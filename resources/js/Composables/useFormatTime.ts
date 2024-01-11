import { format, formatDuration, intervalToDuration, addSeconds, formatDistanceToNowStrict, isPast } from 'date-fns'
import formatDistanceToNow from 'date-fns/formatDistanceToNow'
import { zhCN, enUS, fr, de, id, ja, sk, es } from 'date-fns/locale'

const localesCode: any = { zhCN, enUS, fr, de, id, ja, sk, es }

interface OptionsTime {
    formatTime?: string
    localeCode?: string
}

export const useFormatTime = (dateIso: string | Date, OptionsTime?: OptionsTime) => {
    if (!dateIso) return '-'  // If the provided data date is null

    let tempLocaleCode = OptionsTime?.localeCode === 'zh-Hans' ? 'zhCN' : OptionsTime?.localeCode ?? 'enUS'
    let tempDateIso = new Date(dateIso)

    if (OptionsTime?.formatTime === 'hms') return format(tempDateIso, 'PPpp', { locale: localesCode[tempLocaleCode] })  // Nov 2, 2023, 3:03:26 PM
    if (OptionsTime?.formatTime === 'hm') return format(tempDateIso, 'PPp', { locale: localesCode[tempLocaleCode] })  // Nov 2, 2023, 3:03 PM

    return format(tempDateIso, 'PPP', { locale: localesCode[tempLocaleCode] }) // October 13th, 2023
}

// Relative time range (10 days ago)
export const useRangeFromNow = (dateIso: string | Date, OptionsTime?: OptionsTime) => {
    if (!dateIso) return '-'  // If the provided data date is null

    let tempLocaleCode = OptionsTime?.localeCode === 'zh-Hans' ? 'zhCN' : 'localeCode'
    const date = new Date(dateIso)

    return formatDistanceToNow(date, { locale: localesCode[tempLocaleCode], includeSeconds: true })
}

// Time countdown 1
export const useTimeCountdown: any = (dateIso: string, options?: { human?: boolean, zero?: boolean }) => {
    if (!dateIso) return '-'  // If the provided data date is null

    const countdown = intervalToDuration({
        start: new Date(),
        end: new Date(dateIso)
    })

    if(isPast(new Date(dateIso))) return false  // If the provided date already passed then return false

    if(options?.human) return formatDuration(countdown, options)  // 5 days 23 hours 3 minutes 58 seconds

    return countdown  // { "years": 0, "months": 0, "days": 0, "hours": 0, "minutes": 51, "seconds": 0 } 
}


// Time countdown 2
export const useSecondCountdown: any = (dateIso: string | Date, duration: number, options?: { human?: boolean, zero?: boolean }) => {
    if (!dateIso) return false  // If the provided data date is null

    const newDate = addSeconds(new Date(dateIso), duration)
    if(isPast(newDate)) return false
    return formatDistanceToNowStrict(newDate)  // 23 seconds
}

// Convert miliseconds to hours-minutes-seconds
export const useMilisecondToTime = (miliSecond: number) => {
    // Calculate minutes and seconds
    const hours = Math.floor(miliSecond / (1000 * 60 * 60))
    const minutes = Math.floor((miliSecond % (1000 * 60 * 60)) / (1000 * 60))
    const seconds = Math.floor((miliSecond % (1000 * 60)) / 1000)

    // Format the result
    const formattedTime =
        (hours > 0 ? `${hours} hour${hours > 1 ? 's' : ''}` : '') +
        (minutes > 0 ? `${hours > 0 ? ' ' : ''}${minutes} minute${minutes > 1 ? 's' : ''}` : '') +
        (seconds > 0
            ? `${(hours > 0 || minutes > 0)
                ? ' '
                : ''}
                ${seconds} second${seconds > 1
                    ? 's'
                    : ''}`
            : '0 second')

    return formattedTime // 2 hours 56 minutes 23 seconds
}