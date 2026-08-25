<template>
    <PublicLayout>
        <article class="product-show">
            <div class="product-show__media">
                <img
                    v-if="product.image_path"
                    :src="product.image_path"
                    :alt="product.name"
                />
                <div v-else class="product-show__placeholder">Нет фото</div>
            </div>

            <div class="product-show__body">
                <Tag v-if="product.category" :value="product.category.name" class="product-show__tag" />
                <h1 class="product-show__title">{{ product.name }}</h1>
                <p class="product-show__price">{{ product.price }} ₽</p>

                <p v-if="product.short_description" class="product-show__short">
                    {{ product.short_description }}
                </p>

                <p v-if="product.description" class="product-show__description">
                    {{ product.description }}
                </p>

                <ul v-if="product.advantages?.length" class="product-show__advantages">
                    <li v-for="(item, index) in product.advantages" :key="index">
                        {{ item }}
                    </li>
                </ul>

                <Link href="/">
                    <Button label="К каталогу" severity="secondary" />
                </Link>
            </div>
        </article>
    </PublicLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import PublicLayout from '@/widgets/layout/PublicLayout.vue';

defineProps<{
    product: App.DTO.ProductDto;
}>();
</script>

<style scoped lang="scss">
.product-show {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;

    &__media {
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

    &__tag {
        margin-bottom: 0.75rem;
    }

    &__title {
        margin: 0 0 0.5rem;
    }

    &__price {
        margin: 0 0 1rem;
        font-size: 1.5rem;
        font-weight: 700;
    }

    &__short,
    &__description {
        margin: 0 0 1rem;
        line-height: 1.5;
        color: #486581;
    }

    &__advantages {
        margin: 0 0 1.5rem;
        padding-left: 1.25rem;
        line-height: 1.6;
    }
}
</style>