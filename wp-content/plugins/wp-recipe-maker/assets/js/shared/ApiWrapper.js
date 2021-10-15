export default {
    call( endpoint, method = 'GET', body = false ) {
        let nonce = wprm_admin.api_nonce;

        if ( 'object' === typeof window.wpApiSettings && window.wpApiSettings.nonce ) {
            nonce = window.wpApiSettings.nonce;
        }

        let args = {
            method,
            headers: {
                'X-WP-Nonce': nonce,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                // Don't cache API calls.
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': 0,
            },
            credentials: 'same-origin',
        };

        // Use POST for PUT and DELETE and emulate for better compatibility.
        if ( 'PUT' === method || 'DELETE' === method ) {
            args.method = 'POST';
            args.headers['X-HTTP-Method-Override'] = method;
        }

        // Add optional body data.
        if ( body ) {
            args.body = JSON.stringify(body);
        }

        // Prevent double ? in endpoint by keeping only the first one.
        let occurrence = 0;

        endpoint = endpoint.replace( /\?/g, function (match) {
            occurrence++;
            return 2 <= occurrence ? "&" : match;
        } );

        return fetch(endpoint, args).then(function (response) {
            if ( response.ok ) {
                return response.json();
            } else {
                // Log errors in console and try to get as much debug information as possible.
                console.log(endpoint, args);
                console.log(response);
                const message = "Something went wrong. Using a firewall like Cloudflare or Sucuri? Try whitelisting your IP. If that doesn't work, please contact support@bootstrapped.ventures with the following details:";
                const responseDetails = `${response.url} ${response.redirected ? '(redirected)' : ''}- ${response.status} - ${response.statusText}`;

                try {
                    response.text().then(text => {
                        console.log(text);

                        if ( -1 !== text.indexOf( 'rest_cookie_invalid_nonce' ) ) {
                            // Got logged out.
                            alert( 'You got logged out or your session expired. Please try logging out of WordPress and back in again.' );
                        } else {
                            alert( `${message}\r\n\r\n${responseDetails}\r\n\r\n${text}` );
                        }
                    })
                } catch(e) {
                    console.log(e);
                    alert( `${message}\r\n\r\n${responseDetails}\r\n\r\n${e}` );
                }

                return false;
            }
        });
    },
};
