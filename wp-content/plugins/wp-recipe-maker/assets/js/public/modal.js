import MicroModal from 'micromodal';
import '../../css/public/modal.scss';

window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.modal = {
    savedScrollPosition: 0,
    currentOpenUid: null,
    
    open( uid, data = {} ) {
        // Close currently open modal if there is one
        if ( this.currentOpenUid && this.currentOpenUid !== uid ) {
            this.close( this.currentOpenUid );
        }

        // Prevent body scroll by fixing it in place
        this.savedScrollPosition = window.pageYOffset || document.documentElement.scrollTop;
        
        // Check if html has a margin-top (e.g., from WordPress admin bar)
        const htmlStyle = window.getComputedStyle( document.documentElement );
        const htmlMarginTop = htmlStyle.marginTop;
        let htmlMarginTopValue = 0;
        if ( htmlMarginTop && htmlMarginTop !== '0px' ) {
            htmlMarginTopValue = parseFloat( htmlMarginTop );
        }
        
        document.body.classList.add( 'wprm-popup-modal-open' );
        // Adjust body top position to account for html margin-top
        document.body.style.top = `-${this.savedScrollPosition - htmlMarginTopValue}px`;
        
        MicroModal.show('wprm-popup-modal-' + uid, {
            onShow: modal => {
                // Track this as the currently open modal
                this.currentOpenUid = uid;
                const type = modal.dataset.type;
                document.dispatchEvent( new CustomEvent( 'wprm-modal-open', { detail: { type, uid, modal, data } } ) );
            },
            onClose: modal => {
                // Clear the currently open modal tracking
                if ( this.currentOpenUid === uid ) {
                    this.currentOpenUid = null;
                }
                const type = modal.dataset.type;
                document.dispatchEvent( new CustomEvent( 'wprm-modal-close', { detail: { type, uid, modal, data } } ) );
                
                // Re-enable body scroll and restore scroll position
                const scrollPosition = this.savedScrollPosition;
                document.body.classList.remove( 'wprm-popup-modal-open' );
                document.body.style.top = '';
                this.savedScrollPosition = 0;
                
                // Restore scroll position with instant behavior
                window.scrollTo( {
                    top: scrollPosition,
                    behavior: 'instant'
                } );
            },
            awaitCloseAnimation: true,
        });
    },
    close( uid ) {
        // Ignore duplicate close requests for modals that are no longer open.
        if ( `${ this.currentOpenUid }` !== `${ uid }` ) {
            return;
        }

        MicroModal.close('wprm-popup-modal-' + uid);
        // Clear the currently open modal tracking if this was the open one
        if ( this.currentOpenUid === uid ) {
            this.currentOpenUid = null;
        }
    },
};
