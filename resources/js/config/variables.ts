export interface Variable {
  name: string
  description: string
  key: string
  category: 'subscriber' | 'campaign' | 'system'
}

export const availableVariables: Variable[] = [
  {
    name: 'First Name',
    description: 'Subscriber\'s first name',
    key: '{{subscriber.first_name}}',
    category: 'subscriber'
  },
  {
    name: 'Last Name',
    description: 'Subscriber\'s last name',
    key: '{{subscriber.last_name}}',
    category: 'subscriber'
  },
  {
    name: 'Email',
    description: 'Subscriber\'s email address',
    key: '{{subscriber.email}}',
    category: 'subscriber'
  },
  {
    name: 'Campaign Name',
    description: 'Name of the campaign',
    key: '{{campaign.name}}',
    category: 'campaign'
  },
  {
    name: 'Unsubscribe Link',
    description: 'Link to unsubscribe from the mailing list',
    key: '{{unsubscribe_link}}',
    category: 'system'
  },
  {
    name: 'Web Version',
    description: 'Link to view the email in browser',
    key: '{{web_version}}',
    category: 'system'
  },
  {
    name: 'Current Date',
    description: 'Current date in local format',
    key: '{{current_date}}',
    category: 'system'
  }
]
