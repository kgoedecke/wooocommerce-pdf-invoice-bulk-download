<?php

class WooCommerce_PDF_Invoices_Bulk_Async_Request extends WP_Async_Request {

	protected $action = 'pdf_invoices_request';
	public $date_after  = null;
	public $date_before = null;

	/**
	 * Handle
	 */
	protected function handle() {

		if ( isset( $_POST['filter'] ) ) {

			if ( $_POST['filter'] == 'month-group' ) {
				$timestamp = strtotime( sprintf( '%s %s', sanitize_text_field( $_POST['order-month'] ), sanitize_text_field( $_POST['order-year'] ) ) );
				$this->date_after  = date( 'Y-m-01', $timestamp );
				$this->date_before = date( 'Y-m-t', $timestamp );

			} else if ( $_POST['filter'] == 'range-group' ) {
				$this->date_after  = sanitize_text_field( $_POST['start-date'] );
				$this->date_before = sanitize_text_field( $_POST['end-date'] );
			}
		}

		if ( null !== $this->date_after && null !== $this->date_before ) {
			$result = $this->generate_zip( wc_clean( $_POST['order-statuses'] ) );

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( $result->get_error_message() );
			} else {
				wp_send_json_success( $result );
			}
		}

		return false;
	}

	public function generate_zip( $order_statuses ) {
		$args = array(
			'date_after'  => $this->date_after,
			'date_before' => $this->date_before,
			'status'      => $order_statuses,
			'type'        => 'shop_order',
			'limit'       => -1,
		);

		$orders = wc_get_orders( $args );

		if ( empty( $orders ) ) {
			return new WP_Error( 'error', sprintf( esc_html__( 'Order from %1$s to %2$s not found.', 'woocommerce-pdf-invoices-bulk-download' ), $this->date_after, $this->date_before ) );
		}

		return $this->run( $orders );
	}

	public function create_zip( $files = array(), $destination = '', $args = array(), $callback = null ) {
		$defaults = array(
			'overwrite' => false,
			'remove_pdf_after_process' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$upload_dir = wp_upload_dir();
		$dest_path  = trailingslashit( path_join( $upload_dir['basedir'], WooCommerce_PDF_Invoices_Bulk_Download::$dir_name ) );
		$dest_url   = trailingslashit( path_join( $upload_dir['baseurl'], WooCommerce_PDF_Invoices_Bulk_Download::$dir_name ) );

		if ( file_exists( $dest_path . $destination ) && ! $args['overwrite'] ) {
			return $dest_url . $destination;
		}

		if ( ! file_exists( $dest_path ) ) {
			WooCommerce_PDF_Invoices_Bulk_Download::create_target_dir();
		}

		$dest_path .= $destination;
		$dest_url  .= $destination;

		$valid_files = array();

		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				if ( file_exists( $file ) ) {
					$valid_files[] = $file;
				}
			}
		}

		if ( count( $valid_files ) ) {

			$zip = new \PHPZip\Zip\File\Zip();
			$zip->setZipFile( $dest_path );

			foreach ( $valid_files as $file ) {
				$zip->addFile( file_get_contents( $file ), basename( $file ), filectime( $file ) );

				if ( true === $args['remove_pdf_after_process'] ) {
					@unlink( $file );
				}

			}

			$zip->finalize();
		}

		if ( is_callable( $callback ) ) {
			call_user_func( $callback, $files );
		}

		if ( file_exists( $dest_path ) ) {
			return $dest_url;
		}

		return false;
	}

	public function run( $orders ) {
		$orders = apply_filters( 'woocommerce-pdf-invoices-bulk-download/run/orders', $orders, $this );

		$r = apply_filters( 'woocommerce-pdf-invoices-bulk-download/run/before', false, $orders, $this );

		if ( class_exists( 'BEWPI_Invoice' ) ) {
			$r = $this->woocommerce_pdf_invoices_plugin( $orders );

		} else if ( function_exists( 'wcpdf_get_document' ) ) {
			$r = $this->woocommerce_pdf_invoices_packing_slips( $orders );

		} else if ( defined( 'PDFPLUGINPATH' ) ) {
			require_once( PDFPLUGINPATH . 'classes/class-pdf-send-pdf-class.php' );
			$r = $this->woocommerce_pdf_invoice_plugin( $orders );
		}

		return apply_filters( 'woocommerce-pdf-invoices-bulk-download/run/after', $r, $orders, $this );
	}

	public function woocommerce_pdf_invoices_plugin( $orders ) {
		$files_to_zip = array();

		foreach ( $orders as $order ) {
			$order_id = $order->get_id();
			$invoice  = new BEWPI_Invoice( $order_id );
			$invoice_path = $invoice->get_full_path();

			if ( ! file_exists( $invoice_path ) ) {
				$files_to_zip[] = $invoice->generate();
			} else {
				$files_to_zip[] = $invoice_path;
			}
		}

		$archive_name = $this->get_archive_name();

		return $this->create_zip(
			$files_to_zip,
			$archive_name,
			array( 'overwrite' => true )
		);
	}

	public function woocommerce_pdf_invoices_packing_slips( $orders ) {
		$files_to_zip = array();

		foreach ( $orders as $order ) {
			$document     = wcpdf_get_document( 'invoice', $order, true );
			$uploaded_pdf = wp_upload_bits( $document->get_filename(), null, $document->get_pdf(), 'temp' );

			if ( ! empty( $uploaded_pdf ) && false === $uploaded_pdf['error'] ) {
				$files_to_zip[] = $uploaded_pdf['file'];
			}
		}

		$archive_name = $this->get_archive_name();

		return $this->create_zip(
			$files_to_zip,
			$archive_name,
			array( 'overwrite' => true, 'remove_pdf_after_process' => true ),
			function( $files_to_zip ) {

				if ( ! empty( $files_to_zip ) ) {
					$file_zip = $files_to_zip[0];
					@rmdir( dirname( $file_zip ) );
				}
		} );
	}

	public function woocommerce_pdf_invoice_plugin( $orders ) {
		$files_to_zip = array();

		foreach ( $orders as $order ) {
			$files_to_zip[] = WC_send_pdf::get_woocommerce_invoice( $order );
		}

		$archive_name = $this->get_archive_name();

		return $this->create_zip(
			$files_to_zip,
			$archive_name,
			array( 'overwrite' => true )
		);
	}

	public function get_archive_name() {
		return sprintf( 'Invoices_%s-%s.zip', $this->date_after, $this->date_before );
	}
}
