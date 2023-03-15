window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.temperature = {
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
            help: container.dataset.tooltip,
        };
    },
};
