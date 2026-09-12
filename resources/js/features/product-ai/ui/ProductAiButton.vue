<template>
    <div class="product-ai">
        <Button
            type="button"
            label="Заполнить недостающие данные"
            severity="secondary"
            :loading="isGenerating"
            :disabled="!canGenerate || isGenerating"
            @click="generate"
        />
        <small v-if="isGenerating" class="product-ai__hint">Генерируем… это может занять до минуты</small>
        <small v-else-if="!canGenerate" class="product-ai__hint">Сначала укажите название</small>
        <Message v-if="error" severity="error" :closable="false">{{ error }}</Message>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import Button from 'primevue/button';
import Message from 'primevue/message';
import { useApi } from '@/shared/api';
import type { ProductAiGeneratePayload } from '@/shared/api/product-ai';

const props = defineProps<{
    payload: ProductAiGeneratePayload;
}>();

const emit = defineEmits<{
    generated: [result: App.DTO.ProductGenerationResultDto];
}>();

const api = useApi();

const isGenerating = ref(false);
const error = ref<string | null>(null);

const canGenerate = computed(() => props.payload.name.trim() !== '');

async function generate(): Promise<void> {
    if (!canGenerate.value || isGenerating.value) {
        return;
    }

    isGenerating.value = true;
    error.value = null;

    try {
        emit('generated', await api.productAi.generate(props.payload));
    } catch {
        error.value = 'Не удалось выполнить генерацию';
    } finally {
        isGenerating.value = false;
    }
}
</script>

<style scoped lang="scss">
.product-ai {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.35rem;

    &__hint {
        color: #829ab1;
    }
}
</style>
