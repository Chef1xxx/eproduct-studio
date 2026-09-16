import type { Directive } from 'vue';

const overlays = new WeakMap<HTMLElement, HTMLElement>();
const originalPositions = new WeakMap<HTMLElement, string>();

function show(el: HTMLElement): void {
    if (overlays.has(el)) {
        return;
    }

    if (getComputedStyle(el).position === 'static') {
        originalPositions.set(el, el.style.position);
        el.style.position = 'relative';
    }

    const overlay = document.createElement('div');
    overlay.className = 'v-loading-overlay';

    const spinner = document.createElement('span');
    spinner.className = 'v-loading-overlay__spinner';

    const text = document.createElement('span');
    text.className = 'v-loading-overlay__text';
    text.textContent = 'Загрузка...';

    overlay.append(spinner, text);
    el.appendChild(overlay);
    overlays.set(el, overlay);
}

function hide(el: HTMLElement): void {
    overlays.get(el)?.remove();
    overlays.delete(el);

    if (originalPositions.has(el)) {
        el.style.position = originalPositions.get(el) ?? '';
        originalPositions.delete(el);
    }
}

export const vLoading: Directive<HTMLElement, boolean> = {
    mounted(el, binding) {
        if (binding.value) {
            show(el);
        }
    },
    updated(el, binding) {
        if (binding.value === binding.oldValue) {
            return;
        }

        if (binding.value) {
            show(el);
        } else {
            hide(el);
        }
    },
    unmounted(el) {
        hide(el);
    },
};

declare module 'vue' {
    interface GlobalDirectives {
        vLoading: typeof vLoading;
    }
}
