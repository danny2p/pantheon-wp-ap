const FEATURE_EXPLORER_TEMPLATE = {
    mode: 'modern',
    type: 'recipe',
    location: 'feature-explorer',
    custom: true,
    premium: false,
    name: 'Feature Explorer',
    slug: 'feature-explorer',
    html: `[wprm-recipe-image size="1000x400!" border_width="0px" style="rounded" rounded_radius="0px"]
<div class="wprm-layout-container wprm-padding-40">
  <div class="wprm-layout-column-container wprm-align-middle wprm-column-gap-10 wprm-column-rows-recipe-500">
    <div class="wprm-layout-column wprm-align-rows-center">
      [wprm-recipe-name tag="h2"]
      [wprm-recipe-author label_container="1" label="Author: " author_image="0" text_style="semi-bold" label_style="semi-bold"]
    </div>
    <div class="wprm-layout-column wprm-align-right wprm-align-rows-center" style="--wprm-layout-column-text-color: var(--glacier-accent-color);">
      [wprm-recipe-nutrition field="calories" unit="1" text_style="semi-bold"]
      [wprm-spacer]
    </div>
  </div>
  <div class="wprm-layout-column-container wprm-column-gap-40 wprm-column-rows-recipe-500 wprm-row-gap-20 glacier-meta">
    <div class="wprm-layout-column wprm-column-width-33 wprm-align-center">
      [wprm-recipe-rating display="stars-details" style="separate" icon_color="var(--glacier-interactivity-color)" icon_size="1.5em" icon_padding="2px" average_decimals="1"]
      [wprm-spacer size="20px"]
      [wprm-recipe-share-options-popup style="inline-button" text_style="semi-bold" horizontal_padding="15px" border_radius="50px" text="Share" icon="share" popup_icon_color="#ffffff" popup_icon_hover_color="#ffffff" popup_text_color="#ffffff" popup_text_hover_color="#ffffff" underline="0" popup_align="flex-start" icon_pinterest="pinterest" icon_facebook="facebook" icon_twitter="x" popup_background="#333333" underline_on_hover="1"]
      [wprm-recipe-print style="inline-button" horizontal_padding="15px" border_radius="50px" text_style="semi-bold" icon="printer-3" text="Print"]    
      [wprm-recipe-download-pdf style="inline-button" horizontal_padding="15px" border_radius="50px" text_style="semi-bold" icon="article" text="PDF"]
      [wprm-recipe-add-to-collection style="inline-button" horizontal_padding="15px" border_radius="50px" text_style="semi-bold" icon="bookmark" text="Save"]
      [wprm-recipe-cook-mode style="inline-button" text_style="semi-bold" horizontal_padding="15px" border_radius="50px" icon="chef-hat-2"]
    </div>
    <div class="wprm-layout-column wprm-column-width-66 wprm-align-rows-center">
      [wprm-recipe-meta-container fields="times" style="inline" table_borders_inside="1" table_border_style="solid" table_border_color="var(--glacier-text-color)" time_shorthand="1" text_style="semi-bold" table_borders="inside-only" label_prep_time="Prep" label_cook_time="Cook" label_total_time="Total" inline_separator="short-line" label_style="normal" custom_label_color="1" custom_link_color="0" label_color="var(--glacier-text-color)" selected_fields="servings,course,total_time" block_color="var(--glacier-accent-color)" custom_block_color="1" separator_color="var(--glacier-text-color)"]
      [wprm-spacer size="20px"]
      [wprm-recipe-summary text_style="normal"]
      [wprm-spacer size="20px"]
      [wprm-recipe-meta-container fields="custom" selected_fields="servings, course, cuisine" style="inline" servings_adjustable="tooltip" text_style="normal" pills_alignment="center" label_style="normal" label_separator=" " pills_gap="10px" pills_background="var(--glacier-accent-color)" pills_border_width="0px" pills_border_radius="100px" pills_horizontal_padding="15px" pills_vertical_padding="5px" table_borders="inside-only" table_border_style="solid" table_border_color="var(--glacier-text-color)" inline_separator="short-line" separator_color="var(--glacier-text-color)" custom_block_color="1" custom_label_color="1" label_color="var(--glacier-text-color)" block_color="var(--glacier-accent-color)"]
      [wprm-recipe-tag key="allergens" display_style="images" image_tooltip="none" image_size="24x24!" class="wprm-feature-explorer-taxonomy-icons" label="Allergens" text_style="normal" separator="" label_container="1"]
    </div>
  </div>
</div>
[wprm-recipe-ingredients header="Ingredients" notes_style="faded" group_tag="div" unit_conversion="before" unit_conversion_style="switch" adjustable_servings="before" servings_button_radius="5px" has_container="1" container_background="var(--glacier-background)" header_bottom_margin="0px" container_collapsible="0" list_style="checkbox" group_bottom_margin="5px" group_custom_color="0" group_color="" header_decoration="icon-line" servings_style="pills" pills_active_background="var(--glacier-interactivity-color)" pills_active_border="var(--glacier-interactivity-color)" pills_height="28px" pills_gap="6px" conversion_switch_off="var(--glacier-interactivity-color)" conversion_switch_off_text="#ffffff" conversion_switch_on="var(--glacier-interactivity-color)" serving_options_any_value="" conversion_switch_height="28px" header_background_color="var(--glacier-header-background)" header_vertical_padding="10px" header_horizontal_padding="20px" header_line_width="2px" header_line_color="var(--glacier-header-text)" header_icon="ingredients-2" header_icon_color="var(--glacier-header-text)" header_collapsible="1" header_collapsible_icon_color="var(--glacier-header-text)" checkbox_size="16px" checkbox_border_radius="16px" checkbox_left_position="0px" checkbox_top_position="2px" force_item_position="1" before_container="1" interactivity_container="1" interactivity_alignment="center" interactivity_order="regular" unit_conversion_button_radius="3px" interactivity_gap="4px" group_style="bold" interactivity_bottom_padding="0" interactivity_bottom_margin="-10px"]
[wprm-recipe-equipment header="Equipment" header_bottom_margin="0px" has_container="1" container_background="var(--glacier-background)" container_collapsible="0" list_style="checkbox" bottom_border="0" header_background_color="var(--glacier-header-background)" header_vertical_padding="10px" header_horizontal_padding="20px" header_decoration="icon-line" header_line_width="2px" header_line_color="var(--glacier-header-text)" header_icon="salt" header_icon_color="var(--glacier-header-text)" header_collapsible="1" header_collapsible_icon_color="var(--glacier-header-text)" checkbox_border_radius="14px" checkbox_size="14px" checkbox_left_position="0px" checkbox_top_position="2px" force_item_position="1"]
[wprm-recipe-instructions header="Method" group_tag="div" has_container="1" container_background="var(--glacier-background)" header_bottom_margin="0px" container_collapsible="0" text_margin="10px" list_style="decimal" list_tag="ol" group_bottom_margin="5px" media_toggle="before" image_border_radius="10px" image_size="medium" group_custom_color="0" group_color="#000000" header_decoration="icon-line" toggle_style="switch" toggle_switch_height="28px" toggle_switch_off="#cccccc" toggle_switch_off_text="#000000" toggle_switch_style="rounded" toggle_off_text="" toggle_text_style="normal" toggle_switch_on="var(--glacier-interactivity-color)" header_background_color="var(--glacier-header-background)" header_vertical_padding="10px" header_horizontal_padding="20px" header_collapsible="1" header_icon="oven" header_line_width="2px" header_line_color="var(--glacier-header-text)" header_icon_color="var(--glacier-header-text)" header_collapsible_icon_color="var(--glacier-header-text)" force_item_position="1" list_item_position="20px" group_style="bold" prevent_sleep="before" prevent_sleep_switch_type="inside" prevent_sleep_description="" interactivity_container="1" interactivity_alignment="center" interactivity_bottom_margin="-10px" prevent_sleep_switch_on="var(--glacier-interactivity-color)" prevent_sleep_on_text="Prevent Sleep Mode" tips_default_accent="var(--glacier-accent-color)"]
[wprm-nutrition-label style="grouped" header="Nutrition" has_container="1" container_background="var(--glacier-background)" container_collapsible="0" label_color="#333333" value_color="var(--glacier-accent-color)" group_width="250px" header_bottom_margin="0px" group_column_gap="20px" bottom_border_style="solid" text_style="normal" header_background_color="var(--glacier-header-background)" header_vertical_padding="10px" header_horizontal_padding="20px" header_decoration="icon-line" header_line_width="2px" header_collapsible="1" header_collapsible_icon_color="var(--glacier-header-text)" header_line_color="var(--glacier-header-text)" header_icon="pear" header_icon_color="var(--glacier-header-text)" group_alignment="space-around" separate_value_from_label="1" label_separator="" daily="0" daily_seperator="dash" group_item_style="pills"]
[wprm-recipe-video header="Video" header_bottom_margin="0px" header_vertical_padding="10px" header_horizontal_padding="20px" header_decoration="icon-line" header_line_width="2px" header_line_color="var(--glacier-header-text)" header_collapsible="1" header_collapsible_icon_color="var(--glacier-header-text)" header_background_color="var(--glacier-header-background)" header_icon="youtube" header_icon_color="var(--glacier-header-text)" has_container="1" container_background="var(--glacier-background)"]
[wprm-recipe-notes header="Notes" header_bottom_margin="0px" has_container="1" container_background="var(--glacier-background)" header_decoration="icon-line" header_icon="book" header_vertical_padding="10px" header_horizontal_padding="20px" header_line_width="2px" header_background_color="var(--glacier-header-background)" header_line_color="var(--glacier-header-text)" header_icon_color="var(--glacier-header-text)" container_icon_color="#ff0000" header_collapsible="1" header_collapsible_icon_color="var(--glacier-header-text)"]
[wprm-private-notes header="Private Notes" header_collapsible="1" header_decoration="icon-line" header_line_width="2px" header_background_color="var(--glacier-header-background)" header_vertical_padding="10px" header_horizontal_padding="20px" header_line_color="var(--glacier-header-text)" header_icon="eye" header_icon_color="var(--glacier-header-text)" header_collapsible_icon_color="var(--glacier-header-text)" has_container="1" container_background="var(--glacier-background)"]
[wprm-call-to-action background_color="#000000" icon_color="#ffffff" header_color="#ffffff" text_color="#ffffff" link_color="#ffffff" action="custom" custom_text="%link% how it was!" custom_link_url="#comment" custom_link_text="Let us know" custom_link_target="_self" icon="heart-empty" padding="30px" border_radius="12px" icon_position="top" header_tag="h3" margin="20px" icon_gap="5px"]`,
    css: `.wprm-recipe-template-feature-explorer {
    margin: 20px auto;
    --glacier-background: #ffffff; /*wprm_background type=color*/
    background-color: var(--glacier-background);
    font-family: "Nunito", sans-serif; /*wprm_main_font_family type=font*/
    font-size: 16px; /*wprm_main_font_size type=font_size*/
    line-height: 1.5em; /*wprm_main_line_height type=font_size*/
    --glacier-text-color: #1e2543; /*wprm_main_text type=color*/
    color: var(--glacier-text-color);
    border: 1px solid var(--glacier-text-color);
    max-width: 1600px; /*wprm_max_width type=size*/

    --glacier-interactivity-color: #1E1E1E; /*wprm_interactivity_color type=color*/
    --glacier-accent-color: #518ec7; /*wprm_accent_color type=color*/

    --glacier-header-text: #1e1e1e; /*wprm_header_text type=color*/
    --glacier-header-background: #F4F4F4; /*wprm_header_background_text type=color*/
}
.wprm-recipe-template-feature-explorer a {
    color: #000000; /*wprm_link type=color*/
}
.wprm-recipe-template-feature-explorer p, .wprm-recipe-template-feature-explorer li {
    font-family: "Nunito", sans-serif; /*wprm_main_font_family type=font*/
    font-size: 1em;
    line-height: 1.5em; /*wprm_main_line_height type=font_size*/
}
.wprm-recipe-template-feature-explorer li {
    margin: 0 0 0 32px;
    padding: 0;
}
.rtl .wprm-recipe-template-feature-explorer li {
    margin: 0 32px 0 0;
}
.wprm-recipe-template-feature-explorer ol, .wprm-recipe-template-feature-explorer ul {
    margin: 0;
    padding: 0;
}
.wprm-recipe-template-feature-explorer br {
    display: none;
}
.wprm-recipe-template-feature-explorer .wprm-recipe-name,
.wprm-recipe-template-feature-explorer .wprm-recipe-header {
    font-family: "Nunito", sans-serif; /*wprm_header_font_family type=font*/
    color: var(--glacier-header-text);
    line-height: 1.3em; /*wprm_header_line_height type=font_size*/
}
.wprm-recipe-template-feature-explorer .wprm-recipe-header * {
    font-family: "Nunito", sans-serif; /*wprm_main_font_family type=font*/
}
.wprm-recipe-template-feature-explorer h1,
.wprm-recipe-template-feature-explorer h2,
.wprm-recipe-template-feature-explorer h3,
.wprm-recipe-template-feature-explorer h4,
.wprm-recipe-template-feature-explorer h5,
.wprm-recipe-template-feature-explorer h6 {
    font-family: "Nunito", sans-serif; /*wprm_header_font_family type=font*/
    color: #1e1e1e; /*wprm_header_text type=color*/
    line-height: 1.3em; /*wprm_header_line_height type=font_size*/
    margin: 0;
    padding: 0;
}
.wprm-recipe-template-feature-explorer h1 {
    font-size: 2.2em; /*wprm_h1_size type=font_size*/
}
.wprm-recipe-template-feature-explorer h2 {
    font-size: 2.2em; /*wprm_h2_size type=font_size*/
}
.wprm-recipe-template-feature-explorer h3 {
    font-size: 1.8em; /*wprm_h3_size type=font_size*/
}
.wprm-recipe-template-feature-explorer h4 {
    font-size: 1em; /*wprm_h4_size type=font_size*/
}
.wprm-recipe-template-feature-explorer h5 {
    font-size: 1em; /*wprm_h5_size type=font_size*/
}
.wprm-recipe-template-feature-explorer h6 {
    font-size: 1em; /*wprm_h6_size type=font_size*/
}
.wprm-recipe-template-feature-explorer.wprm-min-500 .glacier-meta {
    margin-top: 40px;
}
.wprm-recipe-template-feature-explorer.wprm-min-500 .glacier-meta .wprm-layout-column:first-child {
    border-right: 1px solid var(--glacier-text-color);
    padding-right: 20px;
    margin-right: -20px;
}
.wprm-recipe-template-feature-explorer .wprm-recipe-allergens-container {
    display: flex;
    align-items: center;
    gap: 5px;
}`,
};

export default FEATURE_EXPLORER_TEMPLATE;
