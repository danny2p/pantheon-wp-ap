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
                showErrorMessage( endpoint, args, response );
                return false;
            }
        });
    },
};

async function showErrorMessage( endpoint, args, response ) {
    // Log errors in console and try to get as much debug information as possible.
    console.log(endpoint, args);
    console.log(response);

    let message = '';

    // Specific text per status.
    const status = parseInt( response.status );
    let hint = false;

    if ( 300 <= status && status <= 399 ) {
        hint = 'A redirection is breaking the API endpoint. Are any redirections set up in the .htaccess file or using a plugin?';
    } else if ( 401 === status || 403 === status ) {
        hint = 'Something is blocking access. Are you or your webhost using a firewall like Cloudflare WAF or Sucuri? Try whitelisting your own IP address or this specific action.';
    } else if ( 404 === status ) {
        hint = 'The rest API endpoint could not be found. Are your permalinks set up correctly?';
    } else if ( 500 <= status && status <= 599 ) {
        hint = 'The server is throwing an error. It could be hitting a memory or execution limit. Check with your webhost what the exact error is in the logs.';
    }

    if ( hint ) {
        message += `${hint}\r\n\r\n`;
    }

    message += 'Press OK to contact support@bootstrapped.ventures for support (opens an email popup).';

    // Response details.
    const responseDetails = `${response.url} ${response.redirected ? '(redirected)' : ''}- ${response.status} - ${response.statusText}`;
    message += `\r\n\r\n${responseDetails}`;

    let showAlert = true;

    try {
        await response.text().then(text => {
            console.log(text);

            if ( -1 !== text.indexOf( 'rest_cookie_invalid_nonce' ) ) {
                // Got logged out.
                alert( 'You got logged out or your session expired. Please try logging out of WordPress and back in again.' );
                showAlert = false;
            } else {
                message += `\r\n\r\n${text}`;
            }
        })
    } catch(e) {
        console.log(e);
        message += `\r\n\r\n${e}`;
    }

    if ( showAlert && confirm( message ) ) {
        const email = 'support@bootstrapped.ventures';
        const subject = 'WP Recipe Maker Error Message';
        const body = `I received the error message below at ${ window.location.href }\r\n\r\n${ message }`;

        window.open( `mailto:${ encodeURIComponent( email ) }?subject=${ encodeURIComponent( subject ) }&body=${ encodeURIComponent( body ) }` );
    }
}