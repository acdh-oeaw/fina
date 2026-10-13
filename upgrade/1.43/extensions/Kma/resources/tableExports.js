// Load the export libraries before SRF constructs the buttons requested by FINA queries.
const pdfMake = require( './vendor/pdfmake.min.js' );
pdfMake.addVirtualFileSystem( require( './vendor/vfs_fonts.js' ) );
$.fn.dataTable.Buttons.jszip( require( './vendor/jszip.min.js' ) );
$.fn.dataTable.Buttons.pdfMake( pdfMake );
