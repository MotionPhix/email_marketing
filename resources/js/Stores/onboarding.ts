import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

interface OnboardingStep {
  id: number
  title: string
  description: string
  isCompleted: boolean
  data: Record<string, any>
}

interface StepError {
  message: string
  errors?: Record<string, string[]>
}

export const useOnboardingStore = defineStore('onboarding', () => {
  const currentStep = ref(1)
  const isLoading = ref(false)
  const error = ref<string | null>(null)
  const stepErrors = ref<Record<number, StepError>>({})

  const steps = ref<OnboardingStep[]>([
    {
      id: 1,
      title: 'Welcome',
      description: 'Get started with your email marketing journey',
      isCompleted: false,
      data: {}
    },
    {
      id: 2,
      title: 'Import Contacts',
      description: 'Import your existing email subscribers',
      isCompleted: false,
      data: {}
    },
    {
      id: 3,
      title: 'Setup Sending Domain',
      description: 'Configure your sending domain for better deliverability',
      isCompleted: false,
      data: {}
    },
    {
      id: 4,
      title: 'Create Template',
      description: 'Design your first email template',
      isCompleted: false,
      data: {}
    },
    {
      id: 5,
      title: 'Test Campaign',
      description: 'Send a test campaign to yourself',
      isCompleted: false,
      data: {}
    }
  ])

  const progress = computed(() => {
    const completed = steps.value.filter(step => step.isCompleted).length
    return (completed / steps.value.length) * 100
  })

  const currentStepData = computed(() => {
    return steps.value.find(step => step.id === currentStep.value)
  })

  async function updateStep(stepId: number, data: Record<string, any>) {
    isLoading.value = true
    error.value = null
    stepErrors.value[stepId] = null

    try {
      const response = await axios.post(route('onboarding.update-step'), {
        step: stepId,
        data
      })

      const step = steps.value.find(s => s.id === stepId)
      if (step) {
        step.isCompleted = true
        step.data = data
      }

      return true
    } catch (e) {
      if (axios.isAxiosError(e) && e.response) {
        const { message, errors } = e.response.data
        stepErrors.value[stepId] = { message, errors }
        error.value = message
      } else {
        error.value = 'An unexpected error occurred'
      }
      return false
    } finally {
      isLoading.value = false
    }
  }

  async function completeOnboarding() {
    isLoading.value = true
    error.value = null

    try {
      await axios.post(route('onboarding.complete'))
      return true
    } catch (e) {
      error.value = 'Failed to complete onboarding'
      return false
    } finally {
      isLoading.value = false
    }
  }

  function setCurrentStep(step: number) {
    currentStep.value = step
  }

  return {
    currentStep,
    steps,
    isLoading,
    error,
    progress,
    currentStepData,
    stepErrors,
    updateStep,
    completeOnboarding,
    setCurrentStep
  }
})
