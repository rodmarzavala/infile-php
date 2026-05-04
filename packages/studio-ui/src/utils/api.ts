export const getApiUrl = (path: string): string => {
  // @ts-ignore
  const base = window.FEL_STUDIO_API_URL || '/fel-studio/api';
  return `${base}${path}`;
};

export const getApiHeaders = (extraHeaders: Record<string, string> = {}): Record<string, string> => {
  const headers: Record<string, string> = {
    ...extraHeaders
  };

  // CSRF for Laravel/Symfony
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (csrfToken) {
    headers['X-CSRF-TOKEN'] = csrfToken;
  }

  // Nonce for WordPress
  // @ts-ignore
  if (window.InfileStudioData && window.InfileStudioData.nonce) {
    // @ts-ignore
    headers['X-WP-Nonce'] = window.InfileStudioData.nonce;
  }

  return headers;
};
