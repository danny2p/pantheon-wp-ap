import React from 'react';
import {
    Accordion,
    AccordionItem,
    AccordionItemHeading,
    AccordionItemButton,
    AccordionItemPanel,
} from 'react-accessible-accordion';

import '../../../css/admin/onboarding/accordion.scss';

const SimpleAccordion = (props) => {
    return (
        <div className="wprm-admin-onboarding-accordion-container">
            {
                props.hasOwnProperty( 'title' )
                &&
                <h2>
                    { props.title }
                </h2>
            }
            <Accordion
                className="wprm-admin-onboarding-accordion"
                allowZeroExpanded={ true }
            >
                {
                    props.items.map((item, index) => (
                        <AccordionItem key={ index }>
                            <AccordionItemHeading>
                                <AccordionItemButton>
                                    { item.header }
                                </AccordionItemButton>
                            </AccordionItemHeading>
                            <AccordionItemPanel>
                                { item.content }
                            </AccordionItemPanel>
                        </AccordionItem>
                    ))
                }
            </Accordion>
        </div>
    );
}
export default SimpleAccordion;