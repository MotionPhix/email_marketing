export interface Setting {
  id: number
  key: string
  value: string | null
  type: 'string' | 'email' | 'password' | 'boolean' | 'select'
  group: string
  label: string
  description: string | null
  options: Record<string, string> | null
  is_public: boolean
  is_system: boolean
}

export interface EmailProvider {
  id: number
  name: string
  slug: string
  description: string
  required_fields: {
    [key: string]: {
      type: string
      label: string
      required: boolean
    }
  }
  is_enabled: boolean
}

export interface UserEmailProvider {
  id: number
  provider_id: number
  credentials: Record<string, string>
  is_active: boolean
  last_used_at: string | null
}

export interface SettingsFormData {
  settings: Record<string, any>
  provider: {
    id: number | null
    credentials: Record<string, string>
    is_active: boolean
  }
}

export interface SettingsPageProps {
  settings: Record<string, Setting[]>
  emailProviders: EmailProvider[]
  userProviders: UserEmailProvider[]
  groups: Record<string, string>
}
