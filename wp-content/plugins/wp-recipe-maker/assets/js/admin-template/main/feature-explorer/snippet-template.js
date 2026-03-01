const FEATURE_EXPLORER_SNIPPET_TEMPLATE = {
    mode: 'modern',
    type: 'snippet',
    location: 'feature-explorer',
    custom: true,
    premium: false,
    name: 'Feature Explorer Snippet',
    slug: 'feature-explorer-snippet',
    html: `<div class="wprm-recipe-snippet-summary-container">
    [wprm-recipe-summary]
    <div class="wprm-recipe-snippet-summary-actions">
        [wprm-recipe-jump icon="arrow-down" style="inline-button" border_color="#ffffff" border_radius="5px"]
        [wprm-recipe-jump-video icon="movie" style="inline-button" border_color="#ffffff" border_radius="5px"]
        [wprm-recipe-print icon="printer" style="inline-button" border_color="#ffffff" border_radius="5px"]
    </div>
</div>
[wprm-recipe-image size="100x100!" style="rounded"]`,
    css: `.wprm-recipe-template-feature-explorer-snippet {
    border-style: solid; /* wprm_border_style type=border */
	border-width: 0px; /* wprm_border_width type=size */
    border-color: #aaaaaa; /* wprm_border type=color */
    border-radius: 5px; /* wprm_border_radius type=size */
    background-color: #ededed; /* wprm_background type=color */

    font-family: inherit; /* wprm_font_family type=font */
    font-size: 14px;
    color: #333333; /* wprm_text_color type=color */
    text-align: left;
    margin-top: 0px; /* wprm_margin_top type=size */
    margin-bottom: 15px; /* wprm_margin_bottom type=size */

    display: flex;
    justify-content: space-between;
}

.wprm-recipe-snippet-summary-container {
    padding: 10px;

    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.wprm-recipe-snippet-summary-actions {
    margin-top: 10px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
}

.wprm-recipe-template-feature-explorer-snippet .wprm-recipe-image {
    padding: 10px; /* wprm_image_margin type=size */
    flex-shrink: 0;
}

@media all and (max-width: 640px) {
	.wprm-recipe-template-feature-explorer-snippet {
        flex-wrap: wrap-reverse;
        text-align: center;
    }
    .wprm-recipe-template-feature-explorer-snippet .wprm-recipe-image {
        margin: 0 auto;
        flex-shrink: 1;
    }
    .wprm-recipe-snippet-summary-actions {
        display: block;
    }
}`,
};

export default FEATURE_EXPLORER_SNIPPET_TEMPLATE;
