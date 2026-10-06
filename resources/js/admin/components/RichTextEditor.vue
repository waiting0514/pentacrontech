<template>
    <div class="rich-text-editor">
        <QuillEditor v-model:content="content" content-type="html" theme="snow" :toolbar="toolbarOptions"
            @ready="onEditorReady" />
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { uploadService } from '../../services';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue']);

const content = ref(props.modelValue);

watch(() => props.modelValue, (newValue) => {
    if (newValue !== content.value) {
        content.value = newValue;
    }
});

watch(content, (newValue) => {
    emit('update:modelValue', newValue);
});

const toolbarOptions = [
    [{ 'header': [1, 2, 3, false] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ 'color': [] }, { 'background': [] }],
    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
    [{ 'align': [] }],
    ['link', 'image'],
    ['clean']
];

const onEditorReady = (quill) => {
    const toolbar = quill.getModule('toolbar');
    toolbar.addHandler('image', () => {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();

        input.onchange = async () => {
            const file = input.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('file', file);

                try {
                    const response = await uploadService.uploadImage(formData);
                    const range = quill.getSelection();
                    quill.insertEmbed(range.index, 'image', response.data.data.url);
                } catch (error) {
                    console.error('Image upload error:', error);
                }
            }
        };
    });
};
</script>

<style scoped>
.rich-text-editor :deep(.ql-container) {
    min-height: 200px;
    font-size: 16px;
}

.rich-text-editor :deep(.ql-editor) {
    min-height: 200px;
}
</style>
