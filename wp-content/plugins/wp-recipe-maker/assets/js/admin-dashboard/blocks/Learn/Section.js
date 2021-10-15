import React, { Fragment } from 'react';
 
const Section = (props) => {
    return (
        <div className="wprm-admin-dashboard-learn-section-container">
            <div className="wprm-admin-dashboard-learn-section-title">{ props.title }</div>
            <div className="wprm-admin-dashboard-learn-section">{ props.children }</div>
        </div>
    );
}
export default Section;