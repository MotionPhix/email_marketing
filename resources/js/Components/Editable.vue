<script setup>
import {nextTick, ref, watch} from 'vue';

const inputRef = ref()

const props = defineProps({
  modelValue: String,
  placeholder: {
    type: String,
    default: 'Click to edit'
  }
});

const emit = defineEmits(['update:modelValue', 'submit', 'cancel']);

const isEditing = ref(false);
const inputValue = ref(props.modelValue);
const textWidthRef = ref(null);
const inputWidth = ref(0);

const updateInputWidth = () => {

  nextTick(() => {
    inputWidth.value = textWidthRef.value.offsetWidth + 16;
  });

};

const edit = () => {
  isEditing.value = true;

  nextTick(() => {

    inputRef.value.focus();
    inputRef.value.select();

    updateInputWidth();
  });
};

const submit = () => {
  isEditing.value = false;
  emit('update:modelValue', inputValue.value);
  emit('submit', inputValue.value);
};

const cancel = () => {
  isEditing.value = false;
  inputValue.value = props.modelValue;
  emit('cancel');
};

watch(inputValue, updateInputWidth);
</script>

<template>
  <div class="editable-root">
    <div v-if="isEditing" class="editable-area">
      <input
        ref="inputRef"
        v-model="inputValue"
        @blur="submit"
        @keyup.enter="submit"
        @keyup.esc="cancel"
        class="editable-input"
        :placeholder="placeholder"
        :style="{ width: inputWidth + 'px' }"
      />
<!--      <div class="editable-actions">-->
<!--        <button @click="submit" class="editable-submit">Submit</button>-->
<!--        <button @click="cancel" class="editable-cancel">Cancel</button>-->
<!--      </div>-->
      <span ref="textWidthRef" class="hidden-text">
        {{ inputValue || placeholder }}
      </span>
    </div>

    <div v-else class="editable-preview" @click="edit">
      {{ inputValue || placeholder }}
    </div>
  </div>
</template>

<style scoped lang="scss">
.editable-root {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.editable-area {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.editable-input {
  width: 100%;
  padding: 0.5rem;
  border: none !important;
  @apply text-2xl focus:outline-none focus:ring-0 focus:bg-white;
  /*border: 0; /* 1px solid #ccc;*/
  /*border-radius: 0; /* 0.25rem;*/
}

.editable-actions {
  display: flex;
  gap: 0.5rem;
}

.editable-submit,
.editable-cancel {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
}

.editable-submit {
  background-color: #4caf50;
  color: white;
}

.editable-cancel {
  background-color: #f44336;
  color: white;
}

.editable-preview {
  padding: 0.5rem;
  /*border: 1px solid #ccc;*/
  /*border-radius: 0.25rem;
  cursor: pointer;*/
}

.hidden-text {
  visibility: hidden;
  white-space: nowrap;
  position: absolute;
}
</style>
