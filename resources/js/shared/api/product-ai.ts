import type { AxiosInstance } from 'axios';

export type ProductAiGeneratePayload = {
    productId: number | null;
    name: string;
    price: number | null;
    categoryId: number | null;
    shortDescription: string;
    description: string;
    advantages: string;
    image: File | null;
};

export function createProductAiApi(http: AxiosInstance) {
    return {
        async generate(payload: ProductAiGeneratePayload): Promise<App.DTO.AiGenerationDto> {
            const formData = new FormData();

            formData.append('name', payload.name);
            appendIfFilled(formData, 'product_id', payload.productId);
            appendIfFilled(formData, 'price', payload.price);
            appendIfFilled(formData, 'category_id', payload.categoryId);
            appendIfFilled(formData, 'short_description', payload.shortDescription);
            appendIfFilled(formData, 'description', payload.description);
            appendIfFilled(formData, 'advantages', payload.advantages);

            if (payload.image) {
                formData.append('image', payload.image);
            }

            const { data } = await http.post<App.DTO.AiGenerationDto>(
                '/seller/products/ai/generate',
                formData,
            );

            return data;
        },
    };
}

function appendIfFilled(formData: FormData, key: string, value: string | number | null): void {
    if (value === null || value === '') {
        return;
    }

    formData.append(key, String(value));
}
