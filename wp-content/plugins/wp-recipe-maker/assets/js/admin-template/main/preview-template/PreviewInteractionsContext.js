import React from 'react';

const PreviewInteractionsContext = React.createContext({
    hoveringBlock: undefined,
    onChangeHoveringBlock: undefined,
    editingBlock: undefined,
    mode: undefined,
    copyPasteMode: undefined,
    copyPasteBlock: undefined,
    shortcodes: undefined,
});

export default PreviewInteractionsContext;
