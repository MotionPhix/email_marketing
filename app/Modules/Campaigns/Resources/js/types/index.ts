export interface Campaign {
  id: number
  uuid: string
  name: string
  subject: string
  content: string
  template_data: Record<string, any> | null
  from_name: string | null
  from_email: string | null
  reply_to: string | null
  scheduled_at: string | null
  started_at: string | null
  completed_at: string | null
  status: 'draft' | 'scheduled' | 'sending' | 'sent' | 'failed'
  total_recipients: number
  sent_count: number
  opened_count: number
  clicked_count: number
  bounced_count: number
  complained_count: number
  created_at: string
  updated_at: string
  lists: MailingList[]
}

export interface MailingList {
  id: number
  name: string
  description: string | null
  subscriber_count: number
}

export interface CampaignStats {
  total: number
  sent: number
  scheduled: number
  draft: number
}

export interface CampaignsPageProps {
  campaigns: {
    data: Campaign[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  stats: CampaignStats
}

export interface CampaignFormProps {
  lists: MailingList[]
  defaultFromName?: string
  defaultFromEmail?: string
  campaign?: Campaign
}
