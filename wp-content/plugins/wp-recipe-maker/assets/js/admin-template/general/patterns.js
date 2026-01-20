// Patterns that should be marked as "new" in the Add Blocks view
const newPatterns = [
    // Add pattern IDs here, e.g.:
    // 'ingredient-instruction-columns',
];

const patterns = {
    'ingredient-instruction-columns': {
        name: 'Ingredient & Instruction Columns',
        description: 'Two-column layout with ingredients on the left and instructions on the right.',
        html: '<div class="wprm-layout-column-container wprm-column-rows-tablet wprm-column-gap-10">\n'
            + '\t<div class="wprm-layout-column wprm-column-width-33">\n'
            + '\t\t[wprm-recipe-ingredients header="Ingredients" notes_style="faded" group_style="uppercase-faded" header_style="uppercase" unit_conversion=""]\n'
            + '\t</div>\n'
            + '\t<div class="wprm-layout-column wprm-column-width-66">\n'
            + '\t\t[wprm-recipe-instructions header="Instructions" group_style="uppercase-faded" header_style="uppercase" text_margin="5px" image_size="medium"]\n'
            + '\t</div>\n'
            + '</div>',
        css: false,
    },
    'image-overlay': {
        name: 'Image with Name Overlay',
        description: 'Recipe image with the name overlayed on top of the image.',
        html: '<div class="wprm-layout-container image-overlay-container">\n'
            + '\t[wprm-recipe-image size="600x300!"]\n'
            + '\t<div class="wprm-layout-container image-overlay">\n'
            + '\t\t[wprm-recipe-name]\n'
            + '\t</div>\n'
            + '</div>',
        css: '%template% .image-overlay-container {\n'
            + '\tposition: relative;\n'
            + '}\n'
            + '%template% .image-overlay {\n'
            + '\tposition: absolute;\n'
            + '\tbottom: 0;\n'
            + '\tbackground-color: white;\n'
            + '\tpadding: 5px 10px;\n'
            + '\tmargin: 10px\n'
            + '}',
    },
}

export default {
    patterns,
    newPatterns,
};
