import React, { useCallback, useEffect, useState } from 'react';
import { createPortal } from 'react-dom';
import { __, sprintf } from '@wordpress/i18n';

const parseRecipeId = (value?: string): number | null => {
  if (!value) {
    return null;
  }

  const parsed = parseInt(value, 10);

  return Number.isInteger(parsed) && parsed > 0 ? parsed : null;
};

const getSearchDocuments = (): Document[] => {
  const docs: Document[] = [];

  if (typeof window !== 'undefined' && window.parent && window.parent !== window) {
    try {
      const parentDoc = window.parent.document;
      if (parentDoc) {
        docs.push(parentDoc);
      }
    } catch (error) {
      // eslint-disable-next-line no-console
      console.warn('[WPRM Divi 5] Unable to access parent document.', error);
    }
  }

  if (typeof document !== 'undefined' && !docs.includes(document)) {
    docs.push(document);
  }

  return docs;
};

const findRecipeFieldContainer = (): HTMLElement | null => {
  const docs = getSearchDocuments();

  if (!docs.length) {
    return null;
  }

  // Expanded selectors list based on Divi 5 patterns
  const selectors: string[] = [
    '[data-attr-name="recipe.innerContent"]',
    '[data-name="recipe.innerContent"]',
    '[data-attr="recipe.innerContent"]',
    '[attr-name="recipe.innerContent"]',
    'et-builder-field[attr-name="recipe.innerContent"]',
    '[data-field-name="recipe.innerContent"]',
    '.et-vb-field-text[id*="recipe-innerContent"]',
    // Generic fallback for fields containing "recipe" in attributes
    'div[class*="et-field"][attr-name*="recipe"]',
  ];

  for (const doc of docs) {
    for (const selector of selectors) {
      const element = doc.querySelector(selector) as HTMLElement | null;

      if (element) {
        return element;
      }
    }

    // Look for Shadow DOM hosts
    const shadowHosts = doc.querySelectorAll<HTMLElement>('*');
    for (const host of Array.from(shadowHosts)) {
      if (host.shadowRoot) {
        const shadowElement = host.shadowRoot.querySelector<HTMLElement>('[id*="recipe-innerContent"]');
        if (shadowElement) {
          return host;
        }
      }
    }

    // Label text fallback
    const labelTexts = [
      __('Recipe ID (required)', 'wp-recipe-maker'),
      __('Recipe ID', 'wp-recipe-maker'),
    ];

    const labels = Array.from(doc.querySelectorAll('label'));
    for (const label of labels) {
      const matchesLabel = labelTexts.some((text) => label.textContent?.trim().includes(text));

      if (matchesLabel) {
        const fieldWrapper =
          label.closest('[data-attr-name]') ||
          label.closest('[data-name]') ||
          label.closest('[data-attr]') ||
          label.closest('[attr-name]') ||
          label.closest('et-builder-field') ||
          label.closest('.et-vb-field') ||
          label.closest('.et-field');

        if (fieldWrapper) {
          return fieldWrapper as HTMLElement;
        }
      }
    }
  }

  return null;
};

const findRecipeFieldInput = (container: HTMLElement): HTMLInputElement | null => {
  if ('shadowRoot' in container && (container as HTMLElement).shadowRoot) {
    const shadowInput = (container as HTMLElement).shadowRoot?.querySelector<HTMLInputElement>(
      '#et-vb-field-input-text-recipe-innerContent, input'
    );

    if (shadowInput) {
      return shadowInput;
    }
  }

  const input = container.querySelector<HTMLInputElement>(
    '#et-vb-field-input-text-recipe-innerContent, input[name*="recipe"], input[data-name*="recipe"], input[type="text"]'
  );

  return input ?? null;
};


const getAvailableModal = () => {
  if (typeof window === 'undefined') {
    return null;
  }

  const visited = new Set<Window>();
  const queue: Window[] = [];

  const enqueue = (win?: Window | null) => {
    if (win && !visited.has(win)) {
      queue.push(win);
    }
  };

  enqueue(window);
  enqueue(window.parent);
  enqueue(window.top);

  while (queue.length) {
    const current = queue.shift()!;
    if (visited.has(current)) {
      continue;
    }

    visited.add(current);

    try {
      const modal = (current as any).WPRM_Modal;
      if (modal?.open) {
        return modal;
      }
    } catch (error) {
      // Cross-origin access might fail
    }

    if (current.parent && current.parent !== current) {
      enqueue(current.parent);
    }
    if (current.top && current.top !== current) {
      enqueue(current.top);
    }
  }

  return null;
};

const setNativeInputValue = (input: HTMLInputElement, value: string) => {
  const descriptor = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value');

  if (descriptor?.set) {
    descriptor.set.call(input, value);
  } else {
    input.value = value;
  }

  input.dispatchEvent(new Event('input', { bubbles: true }));
  input.dispatchEvent(new Event('change', { bubbles: true }));
};

const getRecipeIdFromSelection = (selection: unknown): number | null => {
  if (typeof selection === 'string') {
    // Handle shortcodes like [wprm-recipe id="123"]
    if (selection.includes('[wprm-recipe')) {
      const match = selection.match(/id="?(\d+)"?/);
      if (match && match[1]) {
        return parseInt(match[1], 10);
      }
    }

    const parsed = parseInt(selection, 10);
    return Number.isInteger(parsed) && parsed > 0 ? parsed : null;
  }

  if (typeof selection === 'number') {
    return selection > 0 ? selection : null;
  }

  if (typeof selection === 'object' && selection !== null) {
    const candidate =
      (selection as { id?: number | string }).id ??
      (selection as { value?: number | string }).value ??
      (selection as { recipe_id?: number | string }).recipe_id;

    if (typeof candidate === 'number') {
      return candidate > 0 ? candidate : null;
    }

    if (typeof candidate === 'string') {
      const parsed = parseInt(candidate, 10);
      return Number.isInteger(parsed) && parsed > 0 ? parsed : null;
    }
  }

  return null;
};

type SidebarControlsProps = {
  id: string;
  onRecipeUpdated: (recipeId: number | null) => void;
};

export const SidebarControls = ({ id, onRecipeUpdated }: SidebarControlsProps) => {
  const [container, setContainer] = useState<HTMLElement | null>(null);
  const [selectionMessage, setSelectionMessage] = useState<string | null>(null);
  const [currentRecipeId, setCurrentRecipeId] = useState<string>('');

  useEffect(() => {
    const check = () => {
      const found = findRecipeFieldContainer();

      if (!found) {
        if (container) {
          delete container.dataset.wprmButtonOwner;
          setContainer(null);

          if ((container as any).__wprmInputCleanup) {
            (container as any).__wprmInputCleanup();
            delete (container as any).__wprmInputCleanup;
          }
        }
        setCurrentRecipeId('');
        return;
      }

      const owner = found.dataset.wprmButtonOwner;
      if (owner && owner !== id) {
        return;
      }

      const input = findRecipeFieldInput(found);
      const inputValue = input?.value || '';

      if (found !== container) {
        found.dataset.wprmButtonOwner = id;
        setContainer(found);
        setCurrentRecipeId(inputValue);

        if (input) {
          const handleInputChange = () => {
            setCurrentRecipeId(input.value || '');
          };

          input.addEventListener('input', handleInputChange);
          input.addEventListener('change', handleInputChange);

          (found as any).__wprmInputCleanup = () => {
            input.removeEventListener('input', handleInputChange);
            input.removeEventListener('change', handleInputChange);
          };
        }
      } else if (input && input.value !== currentRecipeId) {
        setCurrentRecipeId(input.value || '');
      }
    };

    check();
    const interval = setInterval(check, 500);

    return () => {
      clearInterval(interval);
      if (container) {
        delete container.dataset.wprmButtonOwner;
        if ((container as any).__wprmInputCleanup) {
          (container as any).__wprmInputCleanup();
          delete (container as any).__wprmInputCleanup;
        }
      }
    };
  }, [container, id, currentRecipeId]);

  const updateRecipeId = useCallback((recipeId: number | null) => {
    if (!recipeId) {
        setSelectionMessage(__('Please pick a recipe to continue.', 'wp-recipe-maker'));
        return;
    }

    if (container) {
        const input = findRecipeFieldInput(container);
        if (input) {
            setNativeInputValue(input, String(recipeId));
            setCurrentRecipeId(String(recipeId));
            setSelectionMessage('');
            onRecipeUpdated(recipeId);
        } else {
            setSelectionMessage(
            sprintf(
                /* translators: %d is replaced with the selected recipe ID. */
                __('Copy recipe ID #%d into the field.', 'wp-recipe-maker'),
                recipeId
            )
            );
        }
    }
  }, [container, onRecipeUpdated]);

  const handleRecipeSelection = useCallback(() => {
    const modal = getAvailableModal();

    if (!modal?.open) {
      console.warn('[WPRM Divi 5] WPRM_Modal not found.');
      setSelectionMessage(
        __('The recipe modal is not available. Please try reloading.', 'wp-recipe-maker')
      );
      return;
    }

    modal.open('select', {
      title: __('Select a WP Recipe Maker recipe', 'wp-recipe-maker'),
      button: __('Use this recipe', 'wp-recipe-maker'),
      fields: {
        recipe: {},
      },
      insertCallback: (fields: { recipe?: unknown }) => {
        const selectedId = getRecipeIdFromSelection(fields?.recipe);
        updateRecipeId(selectedId);
      },
    });
  }, [updateRecipeId]);

  const handleCreateRecipe = useCallback(() => {
    const modal = getAvailableModal();

    if (!modal?.open) {
      console.warn('[WPRM Divi 5] WPRM_Modal not found.');
      setSelectionMessage(
        __('The recipe modal is not available. Please try reloading.', 'wp-recipe-maker')
      );
      return;
    }

    modal.open('recipe', {
      saveCallback: (recipe: { id?: number | string }) => {
        const selectedId = getRecipeIdFromSelection(recipe);
        updateRecipeId(selectedId);
      },
    });
  }, [updateRecipeId]);

  const handleEditRecipe = useCallback(() => {
    const modal = getAvailableModal();

    if (!modal?.open) {
      console.warn('[WPRM Divi 5] WPRM_Modal not found.');
      setSelectionMessage(
        __('The recipe modal is not available. Please try reloading.', 'wp-recipe-maker')
      );
      return;
    }

    const recipeId = parseRecipeId(currentRecipeId);
    if (!recipeId) {
      setSelectionMessage(__('Invalid recipe ID.', 'wp-recipe-maker'));
      return;
    }

    modal.open('recipe', {
      recipeId: recipeId,
      saveCallback: () => {
        setSelectionMessage('');

        if (container) {
          const input = findRecipeFieldInput(container);
          if (input) {
            const originalValue = input.value;
            setNativeInputValue(input, '');
            setCurrentRecipeId('');

            setTimeout(() => {
              setNativeInputValue(input, originalValue);
              setCurrentRecipeId(originalValue);
              onRecipeUpdated(recipeId);
            }, 50);
          }
        }
      },
    });
  }, [currentRecipeId, container, onRecipeUpdated]);

  const handleClearRecipe = useCallback(() => {
    if (container) {
      const input = findRecipeFieldInput(container);
      if (input) {
        setNativeInputValue(input, '');
        setCurrentRecipeId('');
        setSelectionMessage('');
        onRecipeUpdated(null);
      }
    }
  }, [container, onRecipeUpdated]);

  if (!container) {
    return null;
  }

  const hasRecipeId = currentRecipeId.trim() !== '';

  return createPortal(
    <div className="wprm-divi5-sidebar-controls">
      <div style={{ display: 'flex', flexDirection: 'column', gap: '6px' }}>
        {!hasRecipeId ? (
          <>
            <button
              type="button"
              className="wprm-divi5-recipe__sidebar-button"
              onClick={handleRecipeSelection}
            >
              {__('Search recipe', 'wp-recipe-maker')}
            </button>
            <button
              type="button"
              className="wprm-divi5-recipe__sidebar-button"
              onClick={handleCreateRecipe}
            >
              {__('Create new Recipe', 'wp-recipe-maker')}
            </button>
          </>
        ) : (
          <>
            <button
              type="button"
              className="wprm-divi5-recipe__sidebar-button"
              onClick={handleEditRecipe}
            >
              {__('Edit Recipe', 'wp-recipe-maker')}
            </button>
            <button
              type="button"
              className="wprm-divi5-recipe__sidebar-button"
              onClick={handleClearRecipe}
            >
              {__('Clear Recipe', 'wp-recipe-maker')}
            </button>
          </>
        )}
      </div>
      {selectionMessage && (
        <p className="wprm-divi5-recipe__sidebar-message" style={{ display: 'block' }}>
          {selectionMessage}
        </p>
      )}
    </div>,
    container
  );
};
