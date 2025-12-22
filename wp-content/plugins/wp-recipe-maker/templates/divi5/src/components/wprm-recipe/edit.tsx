import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { ModuleContainer } from '@divi/module';

import { ModuleStyles } from './styles';
import { moduleClassnames } from './module-classnames';
import { RecipeAttribute, WprmRecipeEditProps, WprmDivi5Data } from './types';
import { SidebarControls } from './sidebar-controls';
import { getBuilderData, fetchBuilderData } from './utils/builder-data';

const getDesktopValue = (recipe?: RecipeAttribute): string => (
  recipe?.innerContent?.desktop?.value ?? ''
);

const parseRecipeId = (value?: string): number | null => {
  if (!value) {
    return null;
  }

  const parsed = parseInt(value, 10);

  return Number.isInteger(parsed) && parsed > 0 ? parsed : null;
};


export const WprmRecipeEdit = ({ attrs, elements, id, name }: WprmRecipeEditProps) => {
  const builderDataRef = useRef<WprmDivi5Data>(getBuilderData());
  const [builderData, setBuilderData] = useState<WprmDivi5Data>(builderDataRef.current);
  const [previewCache, setPreviewCache] = useState<Record<number, string>>({});
  const [previewRefreshKey, setPreviewRefreshKey] = useState(0);
  const [loadingId, setLoadingId] = useState<number | null>(null);
  const [error, setError] = useState<string | null>(null);

  const recipeValue = getDesktopValue(attrs?.recipe);
  const recipeId = useMemo(() => parseRecipeId(recipeValue), [recipeValue]);
  const cachedPreview = recipeId ? previewCache[recipeId] : null;

  // Fetch builder data on mount if not available
  useEffect(() => {
    const hasData = builderData.nonce && builderData.endpoints?.preview;
    
    if (!hasData) {
      fetchBuilderData().then((data) => {
        if (data && data.nonce && data.endpoints?.preview) {
          builderDataRef.current = data;
          setBuilderData(data);
        }
      });
    }
  }, []); // Only run on mount

  useEffect(() => {
    if (!recipeId || cachedPreview) {
      setLoadingId(null);
      return;
    }

    // Use the state version of builderData which will be updated when fetched
    const { endpoints, nonce } = builderData;

    if (!endpoints?.preview || !nonce) {
      setError(__('Divi could not load the WP Recipe Maker preview data.', 'wp-recipe-maker'));
      return;
    }

    const controller = new AbortController();

    setLoadingId(recipeId);
    setError(null);

    const requestUrl = `${endpoints.preview.replace(/\/$/, '')}/${recipeId}?t=${Date.now()}`;

    fetch(requestUrl, {
      method: 'POST',
      headers: {
        'X-WP-Nonce': nonce,
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      credentials: 'same-origin',
      signal: controller.signal,
    })
      .then(async (response) => {
        const body = await response.json();

        if (!response.ok) {
          throw new Error(body?.message ?? 'Unknown error');
        }

        return body;
      })
      .then((html) => {
        setPreviewCache((current) => ({
          ...current,
          [recipeId]: html || '',
        }));
      })
      .catch((requestError) => {
        if (!controller.signal.aborted) {
          setError(
            requestError?.message
              ? requestError.message
              : __('Failed to load the recipe preview.', 'wp-recipe-maker')
          );
        }
      })
      .finally(() => {
        if (!controller.signal.aborted) {
          setLoadingId(null);
        }
      });

    return () => controller.abort();
  }, [recipeId, cachedPreview, builderData, previewRefreshKey]);

  const invalidatePreview = useCallback((idToInvalidate: number | null) => {
    if (!idToInvalidate) {
      return;
    }
    setPreviewCache((current) => {
      const updated = { ...current };
      delete updated[idToInvalidate];
      return updated;
    });
    setPreviewRefreshKey((key) => key + 1);
  }, []);

  const renderStatus = () => {
    if (!recipeId) {
      return (
        <div>
          <p className="wprm-divi5-recipe__status">
            {__(
              'Select a WP Recipe Maker recipe in the module settings to see the preview here.',
              'wp-recipe-maker'
            )}
          </p>
        </div>
      );
    }

    if (loadingId === recipeId) {
      return (
        <p className="wprm-divi5-recipe__status">
          {sprintf(
            /* translators: %d is replaced with the recipe ID. */
            __('Loading WPRM Recipe #%d…', 'wp-recipe-maker'),
            recipeId
          )}
        </p>
      );
    }

    if (error) {
      return (
        <p className="wprm-divi5-recipe__status">
          {error}
        </p>
      );
    }

    if (cachedPreview) {
      return (
        <div
          className="wprm-divi5-recipe__preview"
          dangerouslySetInnerHTML={{ __html: cachedPreview }}
        />
      );
    }

    return (
      <p className="wprm-divi5-recipe__status">
        {__(
          'Recipe preview not available yet. Try saving or reloading the builder.',
          'wp-recipe-maker'
        )}
      </p>
    );
  };

  return (
    <ModuleContainer
      attrs={attrs}
      elements={elements}
      id={id}
      name={name}
      classnamesFunction={moduleClassnames}
      stylesComponent={ModuleStyles}
    >
      {elements.styleComponents({
        attrName: 'module',
      })}
      <SidebarControls id={id} onRecipeUpdated={invalidatePreview} />
      <div className="wprm-divi5-recipe__inner">
        <div className="wprm-divi5-recipe__content">{renderStatus()}</div>
      </div>
    </ModuleContainer>
  );
};
