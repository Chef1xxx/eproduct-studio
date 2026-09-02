<template>
    <Link :href="`/products/${product.id}`" class="product-card">
        <Card>
            <template #header>
                <div class="product-card__image">
                    <img
                        v-if="product.image_url"
                        :src="product.image_url"
                        :alt="product.name"
                    />
                    <div v-else class="product-card__placeholder">Нет фото</div>
                </div>
            </template>

            <template #title>{{ product.name }}</template>

            <template #subtitle>
                <Tag v-if="product.category" :value="product.category.name" />
            </template>

            <template #content>
                <p class="product-card__price">{{ product.price }} ₽</p>
            </template>
        </Card>
    </Link>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Card from 'primevue/card';
import Tag from 'primevue/tag';

defineProps<{
    product: App.DTO.ProductDto;
}>();
</script>

<style scoped lang="scss">
.product-card {
    display: block;
    text-decoration: none;
    color: inherit;

    &__image {
        aspect-ratio: 1 / 1;
        background: #e4e7eb;
        overflow: hidden;

        img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    }

    &__placeholder {
        height: 100%;
        display: grid;
        place-items: center;
        color: #829ab1;
    }

    &__price {
        margin: 0;
        font-weight: 600;
        font-size: 1.125rem;
    }
}
</style>