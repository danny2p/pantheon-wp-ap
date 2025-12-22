import { WprmDivi5Data } from '../types';

export const getBuilderData = (): WprmDivi5Data => {
  if (typeof window !== 'undefined') {
    const data = (window as unknown as { WPRMDivi5Data?: WprmDivi5Data }).WPRMDivi5Data;
    return data ?? {};
  }

  return {};
};

export const fetchBuilderData = async (): Promise<WprmDivi5Data> => {
  try {
    let restUrl = '/wp-json/';

    if (typeof window !== 'undefined') {
      if ((window as any).wp?.apiSettings?.root) {
        restUrl = (window as any).wp.apiSettings.root;
      } else if ((window as any).wpApiSettings?.root) {
        restUrl = (window as any).wpApiSettings.root;
      } else {
        const apiRootMeta = document.querySelector('script[data-api-root]');
        if (apiRootMeta) {
          restUrl = (apiRootMeta as HTMLElement).dataset.apiRoot || restUrl;
        }
      }
    }

    if (!restUrl.endsWith('/')) {
      restUrl += '/';
    }

    const endpoint = `${restUrl}wp-recipe-maker/v1/utilities/divi5-builder-data`;

    const nonce =
      typeof window !== 'undefined'
        ? (window as any).wp?.apiSettings?.nonce || (window as any).wpApiSettings?.nonce || null
        : null;

    const headers: HeadersInit = {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    };

    if (nonce) {
      headers['X-WP-Nonce'] = nonce;
    }

    const response = await fetch(endpoint, {
      method: 'GET',
      headers,
      credentials: 'same-origin',
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
    }

    const data = await response.json();

    if (typeof window !== 'undefined') {
      (window as any).WPRMDivi5Data = {
        ...(window as any).WPRMDivi5Data,
        ...data,
      };
    }

    return data;
  } catch (error) {
    return {};
  }
};

