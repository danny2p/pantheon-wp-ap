// import html2canvas from 'html2canvas';
// import * as JsPDF from 'jspdf';

window.WPRMPrint = {
    args: {},
    setArgs( args ) {
        this.args = args;
        document.dispatchEvent( new Event( 'wprmPrintArgs' ) );
    },
	init() {
        // On click print button.
        document.querySelector( '#wprm-print-button-print' ).addEventListener( 'click', (e) => {
            e.preventDefault();
            this.onClickPrint();
        });

        // On click toggle.
        const toggles = [ ...document.querySelectorAll( '.wprm-print-toggle' )];

        for ( let toggle of toggles ) {
            // Event listener.
            toggle.addEventListener( 'change', (e) => {
                this.onClickToggle( toggle );
            });

            // Initial state.
            this.onClickToggle( toggle );
        }

        // On click Email.
        const emailButton = document.querySelector( '#wprm-print-button-email' );
        if ( emailButton ) {
            emailButton.addEventListener( 'click', (e) => {
                e.preventDefault();
                this.onClickEmail();
            });
        }

        // On click PDF.
        const pdfButton = document.querySelector( '#wprm-print-button-pdf' );
        if ( pdfButton ) {
            pdfButton.addEventListener( 'click', (e) => {
                e.preventDefault();
                this.onClickPdf();
            });
        }

        // Add padding at bottom of recipe template to fit footer ad.
        const footerAd = document.querySelector( '#wprm-print-footer-ad' );
        if ( footerAd ) {
            const footerAdHeight = footerAd.offsetHeight;
            document.querySelector( 'body' ).style.paddingBottom = footerAdHeight + 'px';
        }

        // Optional remove links.
        if ( window.hasOwnProperty( 'wprm_print_settings' ) && window.wprm_print_settings.print_remove_links ) {
            this.removeLinks();
        }

        // Trigger init event.
        document.dispatchEvent( new Event( 'wprmPrintInit' ) );
    },
    removeLinks() {
        const links = document.querySelector( '#wprm-print-content' ).querySelectorAll( 'a:not(.wprm-recipe-link)' );

        for ( let link of links ) {
            link.outerHTML = '<span>' + link.innerHTML + '</span>';
        }
    },
    onClickToggle( toggle ) {
        // Get elements to toggle.
        let elems = [];
        if ( 'wprm-print-toggle-recipe-image' === toggle.id ) {
            elems = document.querySelectorAll( '.wprm-recipe-image' );
        } else if ( 'wprm-print-toggle-recipe-instruction-media' === toggle.id ) {
            elems = document.querySelectorAll( '.wprm-recipe-instruction-media' );
        } else if ( 'wprm-print-toggle-collection' === toggle.id ) {
            elems = document.querySelectorAll( '.wprmprc-shopping-list-collection' );
        } else if ( 'wprm-print-toggle-shopping-list' === toggle.id ) {
            elems = document.querySelectorAll( '.wprmprc-shopping-list-list' );
        } else if ( 'wprm-print-toggle-checked-items' === toggle.id ) {
            elems = document.querySelectorAll( '.wprmprc-shopping-list-list-ingredient-checked' );
        }

        // Toggle display for elems.
        for ( let elem of elems ) {
            if ( toggle.checked ) {
                elem.style.display = '';
            } else {
                elem.style.display = 'none';
            }
        }
    },
    onClickPrint() {
        // Optional set URL to have it display nice in the print footer.
        let currentUrl = false;
        if ( window.hasOwnProperty( 'wprm_print_url' ) && window.wprm_print_url ) {
            currentUrl = window.location.href;
            window.history.replaceState( {}, document.title, window.wprm_print_url );
        }

        window.print();

        if ( currentUrl ) {
            window.history.replaceState( {}, document.title, currentUrl );
        }
    },
    onClickEmail() {
        window.location = 'mailto:?body=' + window.location;
    },
    onClickPdf() {
        // const printContent = document.getElementById( 'wprm-print-content' );

        // html2canvas( printContent, {
        //     scrollY: -window.scrollY,
        //     imageTimeout: 5000,
        //     useCORS: true,
        // }).then( canvas => {
        //     document.getElementById( 'print-pdf' ).appendChild( canvas );

        //     let img = canvas.toDataURL( 'image/png' );
        //     let pdf = new JsPDF( 'portrait', 'mm' );

        //     pdf.addImage( img, 'JPEG', 5, 5, 200, 287 );
        //     pdf.save( document.title + '.pdf' );

        //     document.getElementById( 'print-pdf' ).innerHTML = '';
        // });
    },
};

ready(() => {
	window.WPRMPrint.init();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}