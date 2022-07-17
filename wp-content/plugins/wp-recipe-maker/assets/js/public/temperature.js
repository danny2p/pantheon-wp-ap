import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';

window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.temperature = {
	init() {
		WPRecipeMaker.temperature.addTooltips();
	},
	addTooltips() {
		const temperatures = WPRecipeMaker.temperature.getTemperatures();

        for ( let temperature of temperatures ) {
            if ( temperature.help ) {
                tippy( temperature.container, {
                    content: temperature.help,
                });
            }
        }
    },
    getTemperatures() {
        let temperatures = [];
        const containers = document.querySelectorAll('.wprm-temperature-container');

        for ( let container of containers ) {
            temperatures.push( WPRecipeMaker.temperature.getData( container ) );
        }

        return temperatures;
    },
    getData( container ) {
        return {
            container,
            value: container.dataset.value,
            unit: container.dataset.unit,
            help: container.dataset.help,
        };
    },
};

ready(() => {
	window.WPRecipeMaker.temperature.init();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}