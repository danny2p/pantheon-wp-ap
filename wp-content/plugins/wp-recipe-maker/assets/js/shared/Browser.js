import Bowser from 'bowser';

export function isProblemBrowser() {
    const browser = Bowser.getParser( window.navigator.userAgent );
    return browser.satisfies({
        edge: "<80",
        ie: '>0',
    });
}

export function isFirefox() {
    const browser = Bowser.getParser( window.navigator.userAgent );
    return 'Firefox' === browser.parsedResult.browser.name;
}