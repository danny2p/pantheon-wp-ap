import React from 'react';
import { __wprm } from 'Shared/Translations';
import StepWelcome from './StepWelcome';
import StepCreating from './StepCreating';
import StepTemplate from './StepTemplate';
import StepSnippets from './StepSnippets';
import StepNext from './StepNext';

import '../../../css/admin/onboarding/steps.scss';

const steps = [
    {name: __wprm( 'Welcome' ), component: <StepWelcome />},
    {name: __wprm( 'Creating Recipes' ), component: <StepCreating />},
    {name: __wprm( 'Template' ), component: <StepTemplate />},
    {name: __wprm( 'Snippets' ), component: <StepSnippets />},
    {name: __wprm( 'Next Steps' ), component: <StepNext />}
];
export default steps;
