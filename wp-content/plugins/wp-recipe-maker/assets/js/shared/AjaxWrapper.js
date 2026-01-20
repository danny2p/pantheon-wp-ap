/**
 * Wrapper for WordPress AJAX endpoints using form-urlencoded format.
 * Provides consistent error handling similar to ApiWrapper.
 */
export default {
    /**
     * Call a WordPress AJAX endpoint.
     *
     * @param {string} action The AJAX action name (e.g., 'wprm_create_post_for_recipe').
     * @param {Object} params Additional parameters to send in the request body.
     * @returns {Promise} Promise that resolves with the response data or false on error.
     */
    call(action, params = {}) {
        // Get nonce from admin settings.
        const nonce = wprm_admin.nonce;

        // Build form-urlencoded body.
        const bodyParams = new URLSearchParams({
            action: action,
            security: nonce,
            ...params,
        });

        const args = {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json, text/plain, */*',
                'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8',
                // Don't cache AJAX calls.
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': 0,
            },
            body: bodyParams.toString(),
        };

        return fetch(wprm_admin.ajax_url, args).then(function (response) {
            if (response.ok) {
                return response.json().then(function (json) {
                    if (json.success) {
                        // Return data if available, otherwise return true to indicate success.
                        // wp_send_json_success() without data returns {"success": true} without a data property.
                        return json.data !== undefined ? json.data : true;
                    } else {
                        // Handle WordPress AJAX error response.
                        showAjaxErrorMessage(action, args, response, json);
                        return false;
                    }
                }).catch(function (jsonError) {
                    // Handle JSON parsing errors.
                    console.error('Failed to parse JSON response:', action, jsonError);
                    showAjaxErrorMessage(action, args, response, null, jsonError);
                    return false;
                });
            } else {
                // Handle HTTP error response.
                showAjaxErrorMessage(action, args, response);
                return false;
            }
        }).catch(function (error) {
            // Handle network errors or other fetch failures.
            console.error('AJAX request failed:', action, error);
            showAjaxErrorMessage(action, args, null, null, error);
            return false;
        });
    },
};

/**
 * Show error message for AJAX endpoint failures.
 *
 * @param {string} action The AJAX action that failed.
 * @param {Object} args The fetch arguments used.
 * @param {Response|null} response The response object, if available.
 * @param {Object|null} json The parsed JSON response, if available.
 * @param {Error|null} error The error object, if available.
 */
async function showAjaxErrorMessage(action, args, response, json = null, error = null) {
    // Log errors in console and try to get as much debug information as possible.
    console.log('AJAX Error:', action, args);
    if (response) {
        console.log('Response:', response);
    }
    if (json) {
        console.log('JSON:', json);
    }
    if (error) {
        console.log('Error:', error);
    }

    let message = '';

    // Handle different error scenarios.
    if (error) {
        // Network error or fetch failure.
        message = 'Network error occurred. Please check your internet connection and try again.\r\n\r\n';
        message += `Error: ${error.message || error}`;
    } else if (response) {
        // HTTP error response.
        const status = parseInt(response.status);
        let hint = false;

        if (300 <= status && status <= 399) {
            hint = 'A redirection is breaking the AJAX endpoint. Are any redirections set up in the .htaccess file or using a plugin?';
        } else if (401 === status || 403 === status) {
            hint = 'Something is blocking access. Are you or your webhost using a firewall like Cloudflare WAF or Sucuri? Try whitelisting your own IP address or this specific action.';
        } else if (404 === status) {
            hint = 'The AJAX endpoint could not be found. This might be a plugin conflict or server configuration issue.';
        } else if (500 <= status && status <= 599) {
            hint = 'The server is throwing an error. It could be hitting a memory or execution limit. Check with your webhost what the exact error is in the logs.';
        }

        if (hint) {
            message += `${hint}\r\n\r\n`;
        }

        // Response details.
        const responseDetails = `${response.url} ${response.redirected ? '(redirected)' : ''}- ${response.status} - ${response.statusText}`;
        message += `Response: ${responseDetails}`;
    }

    // Handle WordPress AJAX error response format.
    if (json && json.data && json.data.message) {
        message += `\r\n\r\nServer message: ${json.data.message}`;
    } else if (json && json.message) {
        message += `\r\n\r\nServer message: ${json.message}`;
    }

    // Check for nonce/session errors.
    let showAlert = true;
    if (json && json.data && json.data.message) {
        const errorMessage = json.data.message.toLowerCase();
        if (-1 !== errorMessage.indexOf('permission') || -1 !== errorMessage.indexOf('nonce') || -1 !== errorMessage.indexOf('logged out') || -1 !== errorMessage.indexOf('session')) {
            alert('You got logged out or your session expired. Please try logging out of WordPress and back in again.');
            showAlert = false;
        }
    }

    if (showAlert) {
        message += '\r\n\r\nPress OK to contact support@bootstrapped.ventures for support (opens an email popup).';

        if (confirm(message)) {
            const email = 'support@bootstrapped.ventures';
            const subject = 'WP Recipe Maker AJAX Error Message';
            const body = `I received the error message below at ${window.location.href}\r\n\r\nAction: ${action}\r\n\r\n${message}`;

            window.open(`mailto:${encodeURIComponent(email)}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`);
        }
    }
}
