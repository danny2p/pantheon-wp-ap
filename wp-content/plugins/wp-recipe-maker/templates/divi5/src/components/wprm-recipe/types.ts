export type RecipeAttribute = {
  innerContent?: {
    desktop?: {
      value?: string;
    };
  };
};

export type WprmRecipeAttrs = {
  recipe?: RecipeAttribute;
  module?: Record<string, unknown>;
};

export type LatestRecipe = {
  id: number;
  text: string;
};

export type WprmDivi5Data = {
  nonce?: string;
  endpoints?: {
    preview?: string;
  };
  latestRecipes?: LatestRecipe[];
};

export type WprmRecipeEditProps = {
  attrs: WprmRecipeAttrs;
  elements: any;
  id: string;
  name: string;
};
