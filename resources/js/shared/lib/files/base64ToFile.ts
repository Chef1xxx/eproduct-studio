export function base64ToFile(base64: string, mimeType: string, fileName: string): File {
    const binary = atob(base64);
    const bytes = new Uint8Array(binary.length);

    for (let i = 0; i < binary.length; i++) {
        bytes[i] = binary.charCodeAt(i);
    }

    return new File([bytes], fileName, { type: mimeType });
}
