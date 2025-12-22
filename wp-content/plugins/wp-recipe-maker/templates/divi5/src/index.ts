import { addAction } from '@wordpress/hooks';
import { registerModule } from '@divi/module-library';

import { wprmRecipeModule } from './components/wprm-recipe';

addAction('divi.moduleLibrary.registerModuleLibraryStore.after', 'wprmRecipeMaker', () => {
  registerModule(wprmRecipeModule.metadata, {
    placeholderContent: wprmRecipeModule.placeholderContent,
    renderers: wprmRecipeModule.renderers,
    conversionOutline: wprmRecipeModule.conversionOutline,
  });
});
