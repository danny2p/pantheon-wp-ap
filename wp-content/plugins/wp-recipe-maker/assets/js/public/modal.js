import MicroModal from 'micromodal';
import '../../css/public/modal.scss';

window.WPRecipeMaker = typeof window.WPRecipeMaker === "undefined" ? {} : window.WPRecipeMaker;

window.WPRecipeMaker.modal = {
    open( uid, data = {} ) {
        MicroModal.show('wprm-popup-modal-' + uid, {
            onShow: modal => {
                const type = modal.dataset.type;
                document.dispatchEvent( new CustomEvent( 'wprm-modal-open', { detail: { type, uid, modal, data } } ) );
            },
            awaitCloseAnimation: true,
        });
    },
    close( uid ) {
        MicroModal.close('wprm-popup-modal-' + uid);
    },
};
