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
  id?: number
  name: string
  description?: string
  subject: string
  preview_text?: string
  content: string
  category: 'newsletter' | 'promotional' | 'transactional' | 'notification'
  type: 'html' | 'markdown' | 'drag-drop'
  thumbnail?: string
  is_default: boolean
  variables?: Record<string, any>
  design?: any
  tags?: string[]
  created_at?: string
  updated_at?: string
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
