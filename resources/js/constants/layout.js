export const CONTAINER_CLASS = {
    default: 'app-container',
    narrow: 'app-container app-container--narrow',
    wide: 'app-container app-container--wide',
};

export function normalizePath(url = '') {
    const path = url.split('?')[0].replace(/\/$/, '');

    return path || '/';
}

export function pathMatches(currentUrl, href, { exact = false } = {}) {
    const current = normalizePath(currentUrl);
    const target = normalizePath(new URL(href, window.location.origin).pathname);

    if (exact) {
        return current === target;
    }

    return current === target || current.startsWith(`${target}/`);
}
