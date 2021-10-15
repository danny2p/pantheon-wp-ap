WPRecipeMakerYoast = function() {
    YoastSEO.app.registerPlugin( 'wprecipemaker', {status: 'ready'} );
    YoastSEO.app.registerModification( 'content', this.wprmContentModification, 'wprecipemaker', 5 );
}

WPRecipeMakerYoast.prototype.wprmContentModification = function( data ) {
    // Remove block editor blocks.
    data = data.replace(/<!-- wp:wp-recipe-maker\/recipe [\s\S]*?\/wp:wp-recipe-maker\/[^\s]* -->/gm, '');
    data = data.replace(/<!-- wp:wp-recipe-maker\/recipe-snippet [\s\S]*? \/-->/gm, '');

    // Remove classic editor shortcode.
    data = data.replace(/(<p>)?\s*\[[^\[]*wprm-recipe[^\]]*\]\s*(<\/p>)?/ig, '');

    return data;
};

jQuery(window).on('YoastSEO:ready', function () {
    new WPRecipeMakerYoast();
});