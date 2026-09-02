<template>
    <form class="product-form" @submit.prevent="submit">
        <div class="product-form__field">
            <label for="name">Название</label>
            <InputText id="name" v-model="form.name" class="w-full" />
            <small v-if="form.errors.name" class="product-form__error">{{ form.errors.name }}</small>
        </div>

        <div class="product-form__field">
            <label for="price">Цена</label>
            <InputNumber id="price" v-model="form.price" :min="0" :min-fraction-digits="0" :max-fraction-digits="2" class="w-full" />
            <small v-if="form.errors.price" class="product-form__error">{{ form.errors.price }}</small>
        </div>

        <div class="product-form__field">
            <label for="category_id">Категория</label>
            <Select
                id="category_id"
                v-model="form.category_id"
                :options="categoryOptions"
                option-label="label"
                option-value="value"
                placeholder="Выберите категорию"
                class="w-full"
            />
            <small v-if="form.errors.category_id" class="product-form__error">{{ form.errors.category_id }}</small>
        </div>

        <div class="product-form__field">
            <label for="short_description">Краткое описание</label>
            <InputText id="short_description" v-model="form.short_description" class="w-full" />
            <small v-if="form.errors.short_description" class="product-form__error">{{ form.errors.short_description }}</small>
        </div>

        <div class="product-form__field">
            <label for="description">Описание</label>
            <Textarea id="description" v-model="form.description" rows="5" class="w-full" />
            <small v-if="form.errors.description" class="product-form__error">{{ form.errors.description }}</small>
        </div>

        <div class="product-form__field">
            <label for="advantages">Преимущества</label>
            <InputText
                id="advantages"
                v-model="form.advantages"
                placeholder="Быстрый, лёгкий, тихий"
                class="w-full"
            />
            <small class="product-form__hint">Через запятую — на сервере станет массивом</small>
            <small v-if="form.errors.advantages" class="product-form__error">{{ form.errors.advantages }}</small>
        </div>

        <div class="product-form__field">
            <label for="image">Изображение</label>
            <input id="image" type="file" accept="image/jpeg,image/png,image/webp" @change="onImageChange" />
            <small v-if="form.errors.image" class="product-form__error">{{ form.errors.image }}</small>

            <div v-if="previewUrl" class="product-form__preview">
                <img :src="previewUrl" alt="Превью" />
            </div>
            <div v-else-if="product?.image_url" class="product-form__preview">
                <img :src="product.image_url" :alt="product.name" />
            </div>
        </div>

        <div class="product-form__actions">
            <Button type="submit" :label="isEdit ? 'Сохранить' : 'Создать'" :loading="form.processing" />
            <Link href="/seller" class="product-form__cancel">Отмена</Link>
        </div>
    </form>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';

const props = defineProps<{
    product: App.DTO.ProductDto | null;
    categories: App.DTO.CategoryDto[];
}>();

const isEdit = computed(() => props.product !== null);

const categoryOptions = computed(() =>
    props.categories.map((category) => ({
        label: category.name,
        value: category.id,
    })),
);

const form = useForm({
    name: props.product?.name ?? '',
    price: props.product ? Number(props.product.price) : null as number | null,
    category_id: props.product?.category?.id ?? null as number | null,
    short_description: props.product?.short_description ?? '',
    description: props.product?.description ?? '',
    advantages: props.product?.advantages?.join(', ') ?? '',
    image: null as File | null,
});

const previewUrl = ref<string | null>(null);

function onImageChange(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }

    form.image = file;

    if (file) {
        previewUrl.value = URL.createObjectURL(file);
    }
}

onBeforeUnmount(() => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }
});

function submit(): void {
    if (isEdit.value && props.product) {
        form.put(`/seller/products/${props.product.id}`, {
            forceFormData: true,
        });
        return;
    }

    form.post('/seller/products', {
        forceFormData: true,
    });
}
</script>

<style scoped lang="scss">
.product-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    max-width: 520px;

    &__field {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    &__error {
        color: #c81e1e;
    }

    &__hint {
        color: #829ab1;
    }

    &__preview {
        width: 160px;
        height: 160px;
        overflow: hidden;
        background: #e4e7eb;

        img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    }

    &__actions {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    &__cancel {
        color: #486581;
    }
}
</style>