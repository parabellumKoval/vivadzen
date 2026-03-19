type CampaignLike = {
  is_active?: boolean
  discount_percent?: number
  is_timed?: boolean
  starts_at?: string | null
  ends_at?: string | null
  show_timer_card?: boolean
  show_timer_product?: boolean
  name?: string | null
}

type CampaignTimerMode = 'card' | 'product'

export const useCampaignPresentation = () => {
  const { t } = useI18n({ useScope: 'global' })

  const parseDate = (value?: string | null): Date | null => {
    if (!value) {
      return null
    }

    const date = new Date(value)
    return Number.isNaN(date.getTime()) ? null : date
  }

  const isTimerEnabled = (campaign: CampaignLike | null | undefined, mode: CampaignTimerMode): boolean => {
    if (!campaign?.is_timed) {
      return false
    }

    if (mode === 'card') {
      return Boolean(campaign.show_timer_card)
    }

    return Boolean(campaign.show_timer_product)
  }

  const isActive = (
    campaign: CampaignLike | null | undefined,
    nowDate: Date = new Date()
  ): boolean => {
    if (!campaign) {
      return false
    }

    if (campaign.is_active === false) {
      return false
    }

    if (typeof campaign.discount_percent === 'number' && campaign.discount_percent <= 0) {
      return false
    }

    if (!campaign.is_timed) {
      return true
    }

    const startsAt = parseDate(campaign.starts_at)
    const endsAt = parseDate(campaign.ends_at)

    const startsOk = !startsAt || startsAt.getTime() <= nowDate.getTime()
    const endsOk = !endsAt || endsAt.getTime() >= nowDate.getTime()

    return startsOk && endsOk
  }

  const getTimerText = (
    campaign: CampaignLike | null | undefined,
    mode: CampaignTimerMode = 'card',
    nowDate: Date = new Date()
  ): string | null => {
    if (!isTimerEnabled(campaign, mode)) {
      return null
    }

    const endsAt = parseDate(campaign?.ends_at)
    if (!endsAt) {
      return null
    }

    const diffMs = endsAt.getTime() - nowDate.getTime()
    if (diffMs <= 0) {
      return null
    }

    const hourMs = 60 * 60 * 1000
    const dayMs = 24 * hourMs

    if (diffMs < dayMs) {
      const hours = Math.max(1, Math.floor(diffMs / hourMs))
      return t('campaign.until_end_hours', { hours })
    }

    const days = Math.max(1, Math.floor(diffMs / dayMs))
    return t('campaign.until_end_days', { days })
  }

  const getLabel = (campaign: CampaignLike | null | undefined): string | null => {
    if (!isActive(campaign)) {
      return null
    }

    const label = typeof campaign?.name === 'string' ? campaign.name.trim() : ''
    return label || null
  }

  return {
    isActive,
    getTimerText,
    getLabel
  }
}
