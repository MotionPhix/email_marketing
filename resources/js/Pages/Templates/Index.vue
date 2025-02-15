<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@tabler/icons-vue'
import { useToast } from '@/composables/useToast'

interface Template {
  id: number
  name: string
  description: string
  category: string
  thumbnail: string
  updated_at: string
}

const templates = ref<Template[]>([])
const searchQuery = ref('')
const selectedCategory = ref('all')
const categories = ref(['all', 'newsletter', 'promotional', 'transactional'])
const { toast } = useToast()

const filteredTemplates = computed(() => {
  return templates.value.filter(template => {
    const matchesSearch = template.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      template.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesCategory = selectedCategory.value === 'all' || template.category === selectedCategory.value
    return matchesSearch && matchesCategory
  })
})

const fetchTemplates = async () => {
  try {
    const response = await fetch('/api/templates')
    templates.value = await response.json()
  } catch (error) {
    toast({
      title: 'Error',
      description: 'Failed to fetch templates',
      variant: 'destructive',
    })
  }
}

onMounted(fetchTemplates)
</script>

<template>
  <div class="container mx-auto py-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Email Templates</h1>
        <p class="text-muted-foreground">
          Manage your email templates and create new ones
        </p>
      </div>
      <Link
        :href="route('templates.create')"
        class="inline-flex items-center"
      >
        <Button>
          <Icon name="plus" class="mr-2 h-4 w-4" />
          New Template
        </Button>
      </Link>
    </div>

    <!-- Filters -->
    <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-4">
        <Input
          v-model="searchQuery"
          placeholder="Search templates..."
          class="w-full sm:w-[300px]"
        >
          <template #prefix>
            <Icon name="search" class="h-4 w-4 text-muted-foreground" />
          </template>
        </Input>

        <Select v-model="selectedCategory">
          <option value="all">All Categories</option>
          <option
            v-for="category in categories.filter(c => c !== 'all')"
            :key="category"
            :value="category"
          >
            {{ category.charAt(0).toUpperCase() + category.slice(1) }}
          </option>
        </Select>
      </div>
    </div>

    <!-- Templates Grid -->
    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <Card
        v-for="template in filteredTemplates"
        :key="template.id"
        class="overflow-hidden"
      >
        <div class="aspect-[16/10] overflow-hidden">
          <img
            :src="template.thumbnail || '/placeholder-template.png'"
            :alt="template.name"
            class="h-full w-full object-cover transition-transform hover:scale-105"
          />
        </div>
        <CardContent class="p-4">
          <div class="flex items-start justify-between">
            <div>
              <h3 class="font-medium">{{ template.name }}</h3>
              <p class="text-sm text-muted-foreground">
                {{ template.description }}
              </p>
            </div>
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon">
                  <Icon name="dots-vertical" class="h-4 w-4" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent>
                <DropdownMenuItem asChild>
                  <Link :href="route('templates.edit', template.id)">
                    <Icon name="edit" class="mr-2 h-4 w-4" />
                    Edit
                  </Link>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                  <Link :href="route('templates.duplicate', template.id)">
                    <Icon name="copy" class="mr-2 h-4 w-4" />
                    Duplicate
                  </Link>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem
                  class="text-destructive"
                  @click="deleteTemplate(template.id)"
                >
                  <Icon name="trash" class="mr-2 h-4 w-4" />
                  Delete
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
          <div class="mt-4 flex items-center gap-2">
            <Badge>{{ template.category }}</Badge>
            <span class="text-xs text-muted-foreground">
              Updated {{ new Date(template.updated_at).toLocaleDateString() }}
            </span>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Empty State -->
    <div
      v-if="filteredTemplates.length === 0"
      class="mt-6 flex flex-col items-center justify-center rounded-lg border border-dashed p-8 text-center"
    >
      <Icon name="mail" class="h-12 w-12 text-muted-foreground" />
      <h3 class="mt-4 font-medium">No templates found</h3>
      <p class="mt-1 text-sm text-muted-foreground">
        Get started by creating a new email template
      </p>
      <Link
        :href="route('templates.create')"
        class="mt-4"
      >
        <Button>Create Template</Button>
      </Link>
    </div>
  </div>
</template>
