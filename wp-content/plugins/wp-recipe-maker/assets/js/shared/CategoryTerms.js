/**
 * Utility functions for working with category terms.
 */

/**
 * Convert an array of term names to term objects.
 * Creates term objects that will be handled by WordPress when saving.
 * Note: Terms are no longer pre-loaded to improve performance on sites with many terms.
 * 
 * @param {string} categoryKey - The category key (e.g., 'course', 'cuisine')
 * @param {string[]} termNames - Array of term names to convert
 * @returns {Object[]} Array of term objects with term_id and name properties
 */
export function convertTermNamesToObjects(categoryKey, termNames) {
    const categoryData = wprm_admin_modal.categories[categoryKey];
    if (!categoryData) {
        return [];
    }
    
    const isCreatable = categoryData.creatable !== false;
    const terms = [];
    
    termNames.forEach(termName => {
        const trimmedTermName = termName.trim();
        
        // Skip empty terms
        if (!trimmedTermName) {
            return;
        }
        
        // For non-creatable categories (like suitablefordiet), WordPress will validate
        // that the term exists when saving. We'll create the term object here and let
        // WordPress handle the validation.
        
        // Create term object - WordPress will match by name or create if needed
        const term = {
            term_id: trimmedTermName, // Will be converted to ID by WordPress if term exists
            name: trimmedTermName,
        };
        
        terms.push(term);
    });
    
    return terms;
}

