import type { AxiosInstance } from 'axios';

export function createAiGenerationsApi(http: AxiosInstance) {
    return {
        async show(id: number): Promise<App.DTO.AiGenerationDto> {
            const { data } = await http.get<App.DTO.AiGenerationDto>(`/seller/ai-generations/${id}`);

            return data;
        },
    };
}
