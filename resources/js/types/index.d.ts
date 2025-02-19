export interface User {
  id: number
  name: string
  email: string
  current_team_id: number
  profile_photo_url: string
  created_at: string
  updated_at: string
}

export interface Team {
  id: number
  name: string
  personal_team: boolean
  created_at: string
  updated_at: string
}

export interface EmailTemplate {
  id: number
  name: string
  description: string | null
  content: string
  created_at: string
  updated_at: string
  deleted_at: string | null
}

export interface CampaignStats {
  id: number
  campaign_id: number
  recipients_count: number
  delivered_count: number
  opened_count: number
  clicked_count: number
  bounced_count: number
  complained_count: number
  unsubscribed_count: number
  created_at: string
  updated_at: string
}

export interface CampaignEvent {
  id: number
  campaign_id: number
  subscriber_id: number
  type: 'delivered' | 'opened' | 'clicked' | 'bounced' | 'complained' | 'unsubscribed'
  metadata: Record<string, any> | null
  created_at: string
  updated_at: string
}

export interface Index {
  id: number
  name: string
  subject: string
  from_name: string
  from_email: string
  reply_to: string | null
  content: string
  template_id: number | null
  status: 'draft' | 'scheduled' | 'sending' | 'sent' | 'failed'
  settings: {
    track_opens?: boolean
    track_clicks?: boolean
    schedule?: {
      timezone: string
      send_at: string
    }
  } | null
  recipients: {
    lists: number[]
    segments: number[]
    excluded_lists: number[]
    excluded_segments: number[]
  }
  scheduled_at: string | null
  sent_at: string | null
  created_at: string
  updated_at: string
  deleted_at: string | null

  // Relationships
  template?: EmailTemplate
  stats?: CampaignStats
  events?: CampaignEvent[]
  team?: Team
  user?: User
}

interface Link {
  url: string | null
  label: string
  active: boolean
}

export interface PaginationMeta {
  current_page: number
  first_page_url: string
  from: number | null
  last_page: number
  last_page_url: string
  links: Link[]
  next_page_url: string | null
  path: string
  per_page: number
  prev_page_url: string | null
  to: number | null
  total: number
}

export interface PaginatedResponse<T> extends PaginationMeta {
  data: T[];
}
export interface CampaignFilters {
  search?: string
  status?: 'all' | Index['status']
  date_from?: string
  date_to?: string
  sort_by?: keyof Index
  sort_direction?: 'asc' | 'desc'
}

export interface EmailDesign {
  design: unknown
  html?: string
}

export type PreviewDevice = 'desktop' | 'mobile' | 'tablet'

export interface EditorProps {
  initialContent: string | null
  processing?: boolean
  previewMode?: boolean
  previewDevice?: PreviewDevice
  isSaving?: boolean
  lastSaved?: string | null
  modelValue: string
}

export interface SavePayload {
  design: string
  isDraft: boolean
}

export type EditorEmits = {
  (e: 'back'): void
  (e: 'save', payload: SavePayload): void
  (e: 'content-change'): void
  (e: 'update:modelValue', value: string): void
}
