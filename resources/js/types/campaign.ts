export interface Campaign {
  id: string
  name: string
  subject: string
  sender: string
  recipients: number
  sentAt: Date
  status: 'draft' | 'scheduled' | 'sending' | 'sent' | 'failed'
}

export interface CampaignStats {
  opens: number
  clicks: number
  bounces: number
  unsubscribes: number
  complaints: number
  deliveryRate: number
  openRate: number
  clickRate: number
  geoStats: {
    country: string
    opens: number
    clicks: number
  }[]
  deviceStats: {
    device: string
    count: number
  }[]
  timeStats: {
    timestamp: Date
    opens: number
    clicks: number
  }[]
}

export interface EmailTemplate {
  id: string
  name: string
  subject: string
  content: string
  previewText?: string
  createdAt: string
  updatedAt: string
}

export interface CampaignDraft {
  name: string
  subject: string
  fromName: string
  fromEmail: string
  replyTo?: string
  content: string
  previewText?: string
  template?: EmailTemplate
  scheduledAt?: string
  recipients: {
    segments?: string[]
    lists?: string[]
    excludedLists?: string[]
  }
  settings: {
    trackOpens: boolean
    trackClicks: boolean
    autoInlineCss: boolean
    ipPool?: string
  }
}

export type CampaignStatus = 'draft' | 'scheduled' | 'sending' | 'sent' | 'failed'
