export interface Plan {
  uuid: string
  name: string
  price: number
  formattedPrice: string
  features: {
    campaign_limit: string
    email_limit: string
    recipient_limit: string
    can_schedule_campaigns: boolean
    personalisation: boolean
  }
}

export interface Subscription {
  uuid: string
  status: 'active' | 'cancelled' | 'expired' | 'scheduled' | 'pending'
  plan: {
    uuid: string
    name: string
    price: number
  }
  trial_ends_at: string | null
  ends_at: string | null
}

export interface BillingPageProps {
  plans: Plan[]
  currentSubscription: Subscription | null
}
