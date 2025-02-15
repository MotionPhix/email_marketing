import { ref, computed } from 'vue'
import type { CampaignStats } from '@/types/campaign'

export function useCampaignStats(campaignId: string) {
  const stats = ref<CampaignStats | null>(null)
  const loading = ref(true)
  const error = ref<string | null>(null)

  const fetchStats = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(`/api/campaigns/${campaignId}/stats`)
      if (!response.ok) {
        throw new Error('Failed to fetch campaign stats')
      }
      stats.value = await response.json()
    } catch (e) {
      error.value = e instanceof Error ? e.message : 'An error occurred'
    } finally {
      loading.value = false
    }
  }

  const refresh = () => fetchStats()

  // Computed properties for common metrics
  const deliveryRate = computed(() => stats.value?.deliveryRate ?? 0)
  const openRate = computed(() => stats.value?.openRate ?? 0)
  const clickRate = computed(() => stats.value?.clickRate ?? 0)
  const bounceRate = computed(() => {
    if (!stats.value) return 0
    return (stats.value.bounces / stats.value.recipients) * 100
  })

  // Initialize
  fetchStats()

  return {
    stats,
    loading,
    error,
    refresh,
    deliveryRate,
    openRate,
    clickRate,
    bounceRate,
  }
}
