import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

interface Template {
  id: number;
  name: string;
  content: string;
  created_at: string;
  updated_at: string;
}

interface Audience {
  id: number;
  name: string;
  count: number;
}

interface Campaign {
  uuid?: string;
  title: string;
  subject: string;
  description?: string;
  template_id?: number;
  audience_id?: number;
  scheduled_at?: string;
  status?: 'draft' | 'scheduled' | 'sending' | 'sent' | 'failed';
}

export const useCampaignStore = defineStore('campaign', () => {
  // State
  const campaign = ref<Campaign | null>(null);
  const template = ref<Template | null>(null);
  const isLoading = ref(false);
  const errors = ref<Record<string, string>>({});
  const lastSavedAt = ref<string | null>(null);

  // Computed
  const isValid = computed(() => {
    if (!campaign.value) return false;

    return Boolean(
      campaign.value.title &&
      campaign.value.subject &&
      campaign.value.template_id &&
      campaign.value.audience_id
    );
  });

  const isDirty = computed(() => {
    return Boolean(campaign.value && !lastSavedAt.value);
  });

  const campaignStatus = computed(() => {
    return campaign.value?.status || 'draft';
  });

  // Actions
  const setCampaign = (newCampaign: Campaign) => {
    campaign.value = {
      ...campaign.value,
      ...newCampaign
    };
    errors.value = {};
  };

  const setTemplate = (newTemplate: Template) => {
    template.value = newTemplate;
    if (campaign.value) {
      campaign.value.template_id = newTemplate.id;
    }
  };

  const updateCampaignField = <K extends keyof Campaign>(
    field: K,
    value: Campaign[K]
) => {
    if (campaign.value) {
      campaign.value[field] = value;
      delete errors.value[field];
    }
  };

  const setError = (field: string, message: string) => {
    errors.value[field] = message;
  };

  const resetErrors = () => {
    errors.value = {};
  };

  const resetCampaign = () => {
    campaign.value = null;
    template.value = null;
    errors.value = {};
    lastSavedAt.value = null;
    isLoading.value = false;
  };

  const markAsSaved = () => {
    lastSavedAt.value = new Date().toISOString();
  };

  const setLoading = (loading: boolean) => {
    isLoading.value = loading;
  };

  return {
    // State
    campaign,
    template,
    isLoading,
    errors,
    lastSavedAt,

    // Computed
    isValid,
    isDirty,
    campaignStatus,

    // Actions
    setCampaign,
    setTemplate,
    updateCampaignField,
    setError,
    resetErrors,
    resetCampaign,
    markAsSaved,
    setLoading,
  };
});
