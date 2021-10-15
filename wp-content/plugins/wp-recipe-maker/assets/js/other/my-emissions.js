document.addEventListener('DOMContentLoaded', () => {
    const customer_identifier = window.hasOwnProperty( 'wprm_my_emissions' ) ? wprm_my_emissions.identifier : false;
    const recipelabels = document.querySelectorAll('.myemissionslabel');

    if ( customer_identifier && 0 < recipelabels.length ) {
        recipelabels.forEach((element, index) => {
            // Get WPRM ID.
            let wprm_id = element.dataset.hasOwnProperty( 'recipe' ) ? element.dataset.recipe : false;

            if ( ! wprm_id ) {
                wprm_id = document.querySelectorAll('.wprm-recipe-container')[index].getAttribute('data-recipe-id');
            }

            if ( wprm_id ) {
                // Hide until label is actually there.
                const container = element.closest( '.wprm-recipe-my-emissions-container' );
                container.style.display = 'none';

                // Request label.
                var url = "https://recipelabels.myemissions.green/" + customer_identifier + "/" + wprm_id + "/" ; 
                fetch(url).then((response) => {
                    if (response.ok) {
                        return response.json();
                    } else if (!window.location.href.endsWith("preview=true")){
                        const pageurl = window.location.protocol + '//' + window.location.host + window.location.pathname; 
                        fetch('https://recipelabels.myemissions.green/add-recipe/', {
                            method: 'POST',
                            headers: {'Content-type': 'application/json; charset=UTF-8'},
                            body: JSON.stringify({
                                customer: customer_identifier,
                                recipe_identifier: wprm_id,  
                                url: pageurl 
                            }),
                        })
                        throw new Error('Recipe label does not exist. The request for a label has been logged and it will be created shortly.');
                    }
                })
                .then(data => {
                    if (data.label_html != undefined) {
                        element.innerHTML = data.label_html;
                        container.style.display = '';
                    }else{return false;}
                })
                .catch(error => {
                    console.log(error);
                });
            }
        });
    }
});