/**
 * Utility functions for working with category terms.
 */

/**
 * Convert an array of term names to term objects.
 * Looks up existing terms or creates new ones if the category is creatable.
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
        
        // Check if term exists in the category list (case-insensitive match)
        let term = categoryData.terms.find(t => {
            const tName = (t.name || '').trim();
            const tId = String(t.term_id || '').trim();
            return tName.toLowerCase() === trimmedTermName.toLowerCase() || tId.toLowerCase() === trimmedTermName.toLowerCase();
        });
        
        if (!term) {
            // For non-creatable categories (like suitablefordiet), only allow existing terms
            if (!isCreatable) {
                // Skip this term - it doesn't exist in the allowed list
                return;
            }
            
            // Create new term (similar to how FieldCategory does it)
            term = {
                term_id: trimmedTermName,
                name: trimmedTermName,
            };
            // Add to the global categories list
            categoryData.terms.push(term);
        }
        
        terms.push(term);
    });
    
    return terms;
}

