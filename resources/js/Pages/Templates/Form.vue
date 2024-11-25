<script setup>
import { useForm } from '@inertiajs/vue3';

const { design } = defineProps({
  design: Object
})

const form = useForm({
  name: design.name,
  content: design.content,
});

const submit = () => design.uuid
  ?  form.put(route('templates.update', design.id))
  : form.post(route('templates.store'))

const fetchPreview = async (templateId) => {
  if (! selectedAudienceId.value) {
    alert("Please select an audience for a preview.");
    return;
  }

  const response = await axios.post('/templates/preview', {
    template_id: templateId,
    audience_id: selectedAudienceId.value,
  });

  previewContent.value = response.data.html;
  showPreviewModal.value = true;
};

const closePreviewModal = () => {
  showPreviewModal.value = false;
};
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold">
      {{ design.uuid ? 'Update' : 'Create' }} Template
    </h1>

    <form @submit.prevent="submit" class="mt-4">
      <div class="mb-4">
        <label for="name" class="block text-sm font-medium">Name</label>
        <input v-model="form.name" id="name" type="text" class="input" required />
      </div>

      <div class="mb-4">
        <label for="content" class="block text-sm font-medium">Content</label>
        <textarea v-model="form.content" id="content" rows="5" class="textarea"></textarea>
      </div>

      <button type="submit" class="btn btn-primary" :disabled="form.processing">Save</button>
    </form>
  </div>
</template>
