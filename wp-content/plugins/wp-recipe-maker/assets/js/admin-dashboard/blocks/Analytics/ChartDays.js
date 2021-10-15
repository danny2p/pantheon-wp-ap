import React, { Fragment } from 'react';
import { BarChart, Bar, Cell, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

import { __wprm } from 'Shared/Translations';
 
const ChartDays = (props) => {
    return (
        <div className="wprm-admin-dashboard-block-chart-days-container">
            <div className="wprm-admin-dashboard-block-chart-title">{ `📊 ${ __wprm( 'Daily Interaction' ) }` }</div>
            <ResponsiveContainer
                width="100%"
                height={300}
            >
                <BarChart
                    data={props.data}
                >
                <XAxis
                    dataKey="date"
                />
                <YAxis
                    type="number"
                    domain={[0, 'dataMax']}
                    allowDecimals={ false }
                />
                <Tooltip />
                <Bar
                    dataKey="total"
                    name={ __wprm( 'Interactions' ) }
                    fill="#2271b1"
                />
            </BarChart>
            </ResponsiveContainer>
        </div>
    );

    
}
export default ChartDays;