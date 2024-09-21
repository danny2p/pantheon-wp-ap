window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.debug = {
	version: () => {
		return wprm_public.version;
	},
	recipe: () => {
		const recipes = WPRecipeMaker.manager.findRecipesOnPage();

		if ( recipes ) {
			return WPRecipeMaker.manager.getRecipeImmediately( recipes[0] );
		}

		return false;
	},
};