<template>
    <div class="product-ai">
        <Button
            type="button"
            label="Заполнить недостающие данные"
            severity="secondary"
            :loading="loading"
            :disabled="!canGenerate || loading"
            @click="generate"
        />
        <small v-if="loading" class="product-ai__hint">Генерация выполняется в фоне…</small>
        <small v-else-if="!canGenerate" class="product-ai__hint">Сначала укажите название</small>
        <Message v-if="error" severity="error" :closable="false">{{ error }}</Message>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import Button from 'primevue/button';
import Message from 'primevue/message';
import { useApi } from '@/shared/api';
import type { ProductAiGeneratePayload } from '@/shared/api/product-ai';
import { subscribeToUserChannel } from '@/shared/lib/echo';

type GenerationUpdatedEvent = {
    generation_id: number;
    status: 'pending' | 'processing' | 'completed' | 'failed';
};

const FAILED_MESSAGE = 'Не удалось выполнить генерацию';

const props = defineProps<{
    payload: ProductAiGeneratePayload;
    userId: number;
}>();

const emit = defineEmits<{
    generated: [result: App.DTO.ProductGenerationResultDto];
}>();

const loading = defineModel<boolean>('loading', { default: false });

const api = useApi();

const error = ref<string | null>(null);
const currentGenerationId = ref<number | null>(null);
const finishedBeforeResponse = new Set<number>();

let stopListening: () => void = () => {};

const canGenerate = computed(() => props.payload.name.trim() !== '');

onMounted(() => {
    stopListening = subscribeToUserChannel<GenerationUpdatedEvent>(
        props.userId,
        '.product-generation.updated',
        onGenerationUpdated,
    );
});

onUnmounted(() => {
    stopListening();
});

async function generate(): Promise<void> {
    if (!canGenerate.value || loading.value) {
        return;
    }

    loading.value = true;
    error.value = null;

    try {
        const generation = await api.productAi.generate(props.payload);

        currentGenerationId.value = generation.id;

        if (finishedBeforeResponse.delete(generation.id)) {
            await loadResult(generation.id);
        }
    } catch {
        error.value = FAILED_MESSAGE;
        loading.value = false;
    }
}

async function onGenerationUpdated(event: GenerationUpdatedEvent): Promise<void> {
    if (event.status !== 'completed' && event.status !== 'failed') {
        return;
    }

    if (event.generation_id !== currentGenerationId.value) {
        if (loading.value && currentGenerationId.value === null) {
            finishedBeforeResponse.add(event.generation_id);
        }

        return;
    }

    await loadResult(event.generation_id);
}

async function loadResult(id: number): Promise<void> {
    try {
        const generation = await api.aiGenerations.show(id);

        if (generation.status === 'completed' && generation.result) {
            emit('generated', generation.result);
        } else {
            error.value = generation.error ?? FAILED_MESSAGE;
        }
    } catch {
        error.value = FAILED_MESSAGE;
    } finally {
        currentGenerationId.value = null;
        loading.value = false;
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
