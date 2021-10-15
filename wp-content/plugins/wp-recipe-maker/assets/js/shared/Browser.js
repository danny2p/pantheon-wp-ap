import Bowser from 'bowser';

export function isProblemBrowser() {
    const browser = Bowser.getParser( window.navigator.userAgent );
    return browser.satisfies({
        edge: "<80",
        ie: '>0',
    });
}
