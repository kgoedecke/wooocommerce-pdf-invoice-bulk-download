( function( $, root, undefined ) {

    $( function() {
        'use strict';

        var app = {
            options: {
                url: BulkDownloadVars.ajaxurl,
                type: 'POST',
                dataType: 'json',
                beforeSubmit: function( arr, $form, options ) {

                    arr.push( { 'name' : 'nonce', 'value' : BulkDownloadVars.nonce } );
                    arr.push( { 'name' : 'action', 'value' : 'wp_pdf_invoices_request' } );

                    $form.find( '.message__item' ).hide();
                    $form.find( '.message__item--processing' ).show();
                    $form.find( '.spinner' ).addClass( 'is-active' );
                    $form.find( '.button-primary' ).addClass( 'button-disabled' ).prop( 'disabled', true );
                },
                error: function( response, statusText, errorText, $form ) {
                    $form.find( '.message__item--processing' ).hide();
                    $form.find( '.spinner' ).removeClass( 'is-active' );
                    $form.find( '.button-primary' ).removeClass( 'button-disabled' ).prop( 'disabled', false );

                    var errorMessage = BulkDownloadVars.messages.serverError;

                    if ( errorText ) {
                        errorMessage = errorText + '. ' + errorMessage;
                    }

                    $form.find( '.message__item--error' )
                        .html( errorMessage )
                        .show();
                },
                success: function( response, statusText, jqXHR, $form ) {
                    $form.find( '.message__item--processing' ).hide();
                    $form.find( '.spinner' ).removeClass( 'is-active' );
                    $form.find( '.button-primary' ).removeClass( 'button-disabled' ).prop( 'disabled', false );

                    if ( true === response.success ) {

                        if ( false !== response.data ) {
                            $form.find( '.message__item--success' ).show();
                            window.location = response.data;
                            return 1;
                        }

                    } else {

                        var errorMessage = BulkDownloadVars.messages.generalError;

                        if ( response.data ) {
                            errorMessage = response.data;
                        }

                        $form.find( '.message__item--error' )
                            .html( errorMessage )
                            .show();
                    }
                },
            },

            init: function() {
                this.initDatePickers();
                this.initFilters();
                $( '.invoice-bulk-download' ).ajaxForm( this.options );
            },

            initFilters: function() {
                $( '#download-filter' ).on( 'change', function( e ) {
                    var groupName = $( this ).val();

                    if ( groupName === 'month-group' ) {
                        $( '#' + groupName ).prop( 'disabled', false );
                        $( '#range-group' ).prop( 'disabled', true );

                    } else if ( groupName === 'range-group' ) {
                        $( '#' + groupName ).prop( 'disabled', false );
                        $( '#month-group' ).prop( 'disabled', true );
                    }
                } );
            },

            initDatePickers: function() {
                $( '.invoice-bulk-download .datepicker' ).datepicker( {
                    dateFormat: 'yy-mm-dd'
                } );
            }
        };

        app.init();
    } );

} )( jQuery, this );
