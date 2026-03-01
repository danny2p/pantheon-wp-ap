const CSV_BOM = '\ufeff';

const toCsvCell = ( value ) => {
    if ( null === value || typeof value === 'undefined' ) {
        return '';
    }

    const cellValue = String( value ).replace( /"/g, '""' );

    return `"${ cellValue }"`;
};

const sanitizeFileName = ( fileName ) => {
    const sanitized = String( fileName )
        .trim()
        .replace( /[^a-z0-9._-]+/gi, '-' )
        .replace( /-+/g, '-' )
        .replace( /^[-.]+|[-.]+$/g, '' );

    return sanitized || 'report';
};

export function downloadToCsv( fileName, headers, rows ) {
    const csvContent = [
        headers.map( ( header ) => toCsvCell( header ) ).join( ',' ),
        ...rows.map(
            ( row ) => row.map( ( value ) => toCsvCell( value ) ).join( ',' )
        ),
    ].join( '\r\n' );

    const blob = new Blob( [ CSV_BOM + csvContent ], { type: 'text/csv;charset=utf-8;' } );
    const csvFileName = `${ sanitizeFileName( fileName ) }.csv`;

    if ( window.navigator && window.navigator.msSaveOrOpenBlob ) {
        window.navigator.msSaveOrOpenBlob( blob, csvFileName );
        return;
    }

    const url = window.URL.createObjectURL( blob );
    const link = document.createElement( 'a' );

    link.setAttribute( 'href', url );
    link.setAttribute( 'download', csvFileName );
    link.style.display = 'none';

    document.body.appendChild( link );
    link.click();
    document.body.removeChild( link );

    window.URL.revokeObjectURL( url );
}
