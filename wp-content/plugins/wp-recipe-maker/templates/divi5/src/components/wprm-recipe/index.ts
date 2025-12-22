import metadata from './module.json';
import { WprmRecipeEdit } from './edit';
import { placeholderContent } from './placeholder-content';
import conversionOutline from './conversion-outline.json';

import './module.scss';
import './style.scss';

export const wprmRecipeModule = {
  metadata,
  placeholderContent,
  conversionOutline,
  renderers: {
    edit: WprmRecipeEdit,
  },
};
