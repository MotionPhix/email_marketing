export interface Setting {
  id: number
  key: string
  value: string
  type: 'string' | 'boolean' | 'integer' | 'float' | 'json' | 'array' | 'email' | 'url'
  group: string
  label: string
  description: string | null
  options: Record<string, string> | null
  is_public: boolean
  is_system: boolean
}

export interface SettingsGroup {
  [key: string]: Setting[]
}

export interface SettingsPageProps {
  settings: SettingsGroup
  groups: Record<string, string>
}
