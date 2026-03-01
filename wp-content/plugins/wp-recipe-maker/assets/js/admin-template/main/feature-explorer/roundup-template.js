const FEATURE_EXPLORER_ROUNDUP_TEMPLATE = {
    mode: 'modern',
    type: 'roundup',
    location: 'feature-explorer',
    custom: true,
    premium: false,
    name: 'Feature Explorer Roundup',
    slug: 'feature-explorer-roundup',
    html: `[wprm-condition field="image"]
<div class="wprm-image-container">
  	[wprm-recipe-counter tag="div" text="%count%" text_style="bold"]
	[wprm-recipe-image size="750x300!"]
	[wprm-recipe-roundup-credit label="" icon="camera-2"]
</div>
[/wprm-condition]
<div class="wprm-summary-container">
    [wprm-recipe-name tag="div"]
    [wprm-spacer]
    [wprm-recipe-summary]
    [wprm-spacer]
    [wprm-recipe-roundup-link text="Check out this recipe" style="inline-button" border_color="#000000" border_radius="5px" button_color="#000000" text_color="#ffffff" icon_color="#ffffff"]
</div>`,
    css: `.wprm-recipe-template-feature-explorer-roundup {
    border-style: solid; /* wprm_border_style type=border */
	border-width: 1px; /* wprm_border_width type=size */
    border-color: #cccccc; /* wprm_border type=color */
    border-radius: 5px; /* wprm_border_radius type=size */
    background-color: #ffffff; /* wprm_background type=color */

    font-family: inherit; /* wprm_font_family type=font */
    font-size: 0.9em; /* wprm_font_size type=font_size */
    color: #333333; /* wprm_text_color type=color */
    text-align: left;
    margin-top: 30px; /* wprm_margin_top type=size */
    margin-bottom: 30px; /* wprm_margin_bottom type=size */
}

.wprm-recipe-template-feature-explorer-roundup .wprm-image-container {
    position: relative;
    max-width: 750px;
}
.wprm-recipe-template-feature-explorer-roundup .wprm-image-container .wprm-recipe-counter {
    position: absolute;
    left: 8px;
    top: 8px;
    background-color: white;
    opacity: 0.9;
    width: 32px;
    height: 32px;
    line-height: 32px;
    font-size: 20px;
    text-align:center;
    border-radius: 50%;
}
.wprm-recipe-template-feature-explorer-roundup .wprm-image-container .wprm-recipe-roundup-credit {
    position: absolute;
    right: 0;
    bottom: 0;
    background-color: white;
    padding: 3px 6px;
    opacity: 0.9;
}
.wprm-recipe-template-feature-explorer-roundup .wprm-recipe-name {
    font-size: 1.5em;
    line-height: 1.5em;
}
.wprm-recipe-template-feature-explorer-roundup .wprm-summary-container {
    padding: 10px;
}`,
};

export default FEATURE_EXPLORER_ROUNDUP_TEMPLATE;
