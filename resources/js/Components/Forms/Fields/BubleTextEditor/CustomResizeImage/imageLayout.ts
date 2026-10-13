export type ImageAlignment = 'left' | 'none' | 'right'

export const IMAGE_WIDTH_PRESETS = [25, 50, 75, 100]

export const MINIMUM_IMAGE_WIDTH_PERCENT = 10
export const MAXIMUM_IMAGE_WIDTH_PERCENT = 100

export const clampImageWidthPercent = (percent: number): number => {
  if (!Number.isFinite(percent)) return MAXIMUM_IMAGE_WIDTH_PERCENT

  return Math.min(MAXIMUM_IMAGE_WIDTH_PERCENT, Math.max(MINIMUM_IMAGE_WIDTH_PERCENT, Math.round(percent)))
}

export const toImageWidth = (percent: number): string => `${clampImageWidthPercent(percent)}%`

export const imageWidthPercent = (width?: string | null): number | null => {
  if (!width) return MAXIMUM_IMAGE_WIDTH_PERCENT

  const percentMatch = /^(\d+(?:\.\d+)?)%$/.exec(width.trim())

  return percentMatch ? clampImageWidthPercent(Number(percentMatch[1])) : null
}

export const imageAlignmentStyle = (alignment?: ImageAlignment | string | null): string => {
  if (alignment === 'left') return 'float: left; margin-right: 1rem;'
  if (alignment === 'right') return 'float: right; margin-left: 1rem;'

  return 'display: block; margin: 0 auto;'
}

export const imageWidthStyle = (width?: string | null): string => `width: ${width || '100%'}; height: auto;`
