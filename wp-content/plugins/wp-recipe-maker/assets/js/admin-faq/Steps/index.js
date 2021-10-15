import React from 'react';
import StepWelcome from './StepWelcome';
import StepCreating from './StepCreating';
import StepTemplate from './StepTemplate';
import StepSnippets from './StepSnippets';
import StepNext from './StepNext';

import '../../../css/admin/onboarding/steps.scss';

const steps = [
    {name: 'Welcome', component: <StepWelcome />},
    {name: 'Creating Recipes', component: <StepCreating />},
    {name: 'Template', component: <StepTemplate />},
    {name: 'Snippets', component: <StepSnippets />},
    {name: 'Next Steps', component: <StepNext />}
];
export default steps;