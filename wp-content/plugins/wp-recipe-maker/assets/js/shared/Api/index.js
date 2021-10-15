const { hooks } = WPRecipeMaker['wp-recipe-maker/dist/shared'];

import Analytics from './Analytics';
import CustomTaxonomy from './CustomTaxonomy';
import Dashboard from './Dashboard';
import General from './General';
import Import from './Import';
import Manage from './Manage';
import Modal from './Modal';
import Rating from './Rating';
import Recipe from './Recipe';
import Settings from './Settings';
import Template from './Template';
import Utilities from './Utilities';

const api = hooks.applyFilters( 'api', {
    analytics: Analytics,
    customTaxonomy: CustomTaxonomy,
    dashboard: Dashboard,
    general: General,
    import: Import,
    manage: Manage,
    modal: Modal,
    rating: Rating,
    recipe: Recipe,
    settings: Settings,
    template: Template,
    utilities: Utilities,
} );

export default api;