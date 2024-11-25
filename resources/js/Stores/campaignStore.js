import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useCampaignStore = defineStore('campaign', () => {
  // Define state as refs
  const campaign = ref(null);
  const template = ref(null);

  // Actions
  const setCampaign = (newCampaign) => {
    campaign.value = newCampaign;
  };

  const setTemplate = (newTemplate) => {
    template.value = newTemplate;
  };

  const resetCampaign = () => {
    campaign.value = null;
    template.value = null;
  };

  // Return the store's state and actions
  return {
    campaign,
    template,
    setCampaign,
    setTemplate,
    resetCampaign,
  };
});
