import { ref, computed } from 'vue'
import { useToast } from '@/composables/useToast'

export function useTemplates() {
  const templates = ref<Template[]>([])
  const loading = ref(false)
  const { toast } = useToast()

  const fetchTemplates = async () => {
    loading.value = true
    try {
      const response = await fetch('/api/templates')
      templates.value = await response.json()
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to fetch templates',
        variant: 'destructive',
      })
    } finally {
      loading.value = false
    }
  }

  const deleteTemplate = async (id: number) => {
    try {
      const response = await fetch(`/api/templates/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
      })

      if (!response.ok) throw new Error('Failed to delete template')

      templates.value = templates.value.filter(t => t.id !== id)

      toast({
        title: 'Success',
        description: 'Template deleted successfully',
      })
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to delete template',
        variant: 'destructive',
      })
    }
  }

  const duplicateTemplate = async (id: number) => {
    try {
      const response = await fetch(`/api/templates/${id}/duplicate`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
      })

      if (!response.ok) throw new Error('Failed to duplicate template')

      await fetchTemplates()

      toast({
        title: 'Success',
        description: 'Template duplicated successfully',
      })
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to duplicate template',
        variant: 'destructive',
      })
    }
  }

  return {
    templates,
    loading,
    fetchTemplates,
    deleteTemplate,
    duplicateTemplate,
  }
}
